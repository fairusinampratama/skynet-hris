<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PayrollService
{
    public function generatePayroll(PayrollPeriod $period)
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            // Check if employee is active during this period
            if (!$employee->isActiveDuring($period->month, $period->year)) {
                continue;
            }

            // Check if payroll already exists for this employee in this period
            $exists = Payroll::where('period_id', $period->id)
                ->where('employee_id', $employee->id)
                ->exists();

            if ($exists) continue;

            $this->calculateSalary($employee, $period);
        }
    }

    public function calculateSalary(Employee $employee, PayrollPeriod $period): Payroll
    {
        // Validate inputs
        if (!$employee->basic_salary || $employee->basic_salary <= 0) {
            throw new \InvalidArgumentException("Employee {$employee->user->name} has no valid basic salary");
        }

        if ($period->status === 'locked') {
            throw new \RuntimeException("Cannot calculate payroll for locked period {$period->month}/{$period->year}");
        }

        $basicSalary = (float) $employee->basic_salary;

        // Load company settings
        $settings = \App\Models\CompanySetting::first();

        // 1. Get working days in the period month
        $daysInMonth = \Carbon\Carbon::create($period->year, $period->month)->daysInMonth;

        // 2. Get attendance records for the month
        $attendances = Attendance::where('user_id', $employee->user_id)
            ->whereMonth('date', $period->month)
            ->whereYear('date', $period->year)
            ->get();

        // 3. New Deductions logic (Lateness and Absence)
        $absentDays = 0;
        $lateDays = 0;
        $lateFineAmount = 0;
        $lateMoreThanHourDays = 0;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = \Carbon\Carbon::create($period->year, $period->month, $i);

            // Holiday check
            $isHoliday = \App\Models\Holiday::whereDate('date', $date)->exists();
            if ($isHoliday) continue;

            $schedule = \App\Models\Schedule::where('employee_id', $employee->id)->where('date', $date->format('Y-m-d'))->first();
            if ($schedule && $schedule->is_off) continue;
            if (!$schedule && $date->isSunday()) continue;

            $attendance = $attendances->first(function ($att) use ($i) {
                return $att->date->day === $i;
            });

            if ($attendance) {
                if ($attendance->is_late && $attendance->check_in_time) {
                    $expectedStartTime = null;
                    if ($schedule) {
                        $expectedStartTime = $schedule->start_time;
                    } else {
                        $shift = \App\Models\EmployeeShift::where('employee_id', $employee->id)->where('day_of_week', $date->dayOfWeekIso)->first();
                        if ($shift) {
                            $expectedStartTime = $shift->start_time;
                        }
                    }

                    $hasIzinTelat = \App\Models\LeaveRequest::where('user_id', $employee->user_id)
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->where('type', 'Izin Telat')
                        ->exists();

                    if (!$hasIzinTelat) {
                        if ($expectedStartTime) {
                            $expected = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $expectedStartTime);
                            $actual = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $attendance->check_in_time);
                            $diffInMinutes = $expected->diffInMinutes($actual, false);

                            if ($diffInMinutes > 60) {
                                $lateMoreThanHourDays++;
                            } elseif ($diffInMinutes >= 15) {
                                $lateDays++;
                                $lateFineAmount += 25000;
                            }
                        } else {
                            $lateDays++;
                            $lateFineAmount += 25000;
                        }
                    }
                }
            } else {
                // Anyone absent is counted as absent, regardless of other Izins (Sakit/Pribadi)
                // If they had Izin Telat they shouldn't be counted as absent, but if they are completely absent, they didn't clock in anyway.
                // Thus we just count them absent.
                $absentDays++;
            }
        }

        $extremeLateDeductionAmount = 0;
        if ($lateMoreThanHourDays > 0) {
            $rawExtremeLate = ($lateMoreThanHourDays / $daysInMonth) * $basicSalary;
            $extremeLateDeductionAmount = floor($rawExtremeLate / 1000) * 1000;
        }

        $absentDeductionAmount = 0;
        if ($absentDays > 0) {
            // Strictly round DOWN to the nearest 1000. E.g. 107,851 becomes 107,000.
            $rawAmount = ($absentDays / $daysInMonth) * $basicSalary;
            $absentDeductionAmount = floor($rawAmount / 1000) * 1000;
        }

        // 6. Totals
        $totalAllowances = 0;
        $totalEarnings   = $basicSalary;
        $totalDeductions = $lateFineAmount + $absentDeductionAmount + $extremeLateDeductionAmount;
        $netSalary       = $totalEarnings - $totalDeductions;

        if ($netSalary < 0) {
            \Log::warning("Negative net salary for employee {$employee->id} in period {$period->id}: {$netSalary}");
            $netSalary = 0; // ensure no negative
        }

        // 8. Create Payroll Record
        $payroll = Payroll::create([
            'period_id'        => $period->id,
            'employee_id'      => $employee->id,
            'basic_salary'     => $basicSalary,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'net_salary'       => $netSalary,
            'bonus'            => 0, // defaults to 0
        ]);

        // 8. Payroll items (detailed slip)
        $this->createItem($payroll, 'Gaji Pokok', $basicSalary, 'earning');

        if ($lateFineAmount > 0) {
            $this->createItem($payroll, "Potongan Keterlambatan ({$lateDays} hari x Rp 25.000)", $lateFineAmount, 'deduction');
        }

        if ($extremeLateDeductionAmount > 0) {
            $basicRupiah = number_format($basicSalary, 0, ',', '.');
            $dailyRateRaw = $basicSalary / $daysInMonth;
            $dailyRateRounded = floor($dailyRateRaw / 1000) * 1000;
            $dailyRupiah = number_format($dailyRateRounded, 0, ',', '.');
            
            $this->createItem($payroll, "Potongan Keterlambatan > 1 Jam ({$lateMoreThanHourDays} hari / {$daysInMonth} hari kerja x Rp {$basicRupiah} = Rp {$dailyRupiah} / hari)", $extremeLateDeductionAmount, 'deduction');
        }

        if ($absentDeductionAmount > 0) {
            $basicRupiah = number_format($basicSalary, 0, ',', '.');
            $dailyRateRaw = $basicSalary / $daysInMonth;
            $dailyRateRounded = floor($dailyRateRaw / 1000) * 1000;
            $dailyRupiah = number_format($dailyRateRounded, 0, ',', '.');
            
            $this->createItem($payroll, "Potongan Tidak Masuk ({$absentDays} hari / {$daysInMonth} hari kerja x Rp {$basicRupiah} = Rp {$dailyRupiah} / hari)", $absentDeductionAmount, 'deduction');
        }

        return $payroll;
    }

    private function createItem($payroll, $name, $amount, $type)
    {
        PayrollItem::create([
            'payroll_id' => $payroll->id,
            'name' => $name,
            'amount' => $amount,
            'type' => $type,
        ]);
    }

    public function getPdfContent(Payroll $payroll): string
    {
        $payroll->loadMissing(['items', 'employee.user', 'period']);
        $company = \App\Models\CompanySetting::first();
        
        $pdf = Pdf::loadView('pdf.payslip', [
            'payroll' => $payroll,
            'company' => $company
        ]);
        return $pdf->output();
    }

    /**
     * @deprecated Use on-demand generation via getPdfContent instead
     */
    public function generatePdf(Payroll $payroll): string
    {
        $content = $this->getPdfContent($payroll);
        
        $filename = "payslips/{$payroll->period->year}/{$payroll->period->month}/slip_{$payroll->id}.pdf";
        Storage::disk('public')->put($filename, $content);
        
        $payroll->update(['pdf_path' => $filename]);
        
        return $filename;
    }
}
