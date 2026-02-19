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

        // Load company settings (with defaults matching old hardcoded values)
        $settings = \App\Models\CompanySetting::first();
        $transportAllowance = (int) ($settings->transport_allowance ?? 400000);
        $mealAllowance      = (int) ($settings->meal_allowance ?? 500000);
        $lateFinePerDay     = (int) ($settings->late_fine_per_day ?? 50000);

        // 1. Get working days in the period month
        $daysInMonth = \Carbon\Carbon::create($period->year, $period->month)->daysInMonth;

        // 2. Get attendance records for the month
        $attendances = Attendance::where('user_id', $employee->user_id)
            ->whereMonth('date', $period->month)
            ->whereYear('date', $period->year)
            ->get();

        // 3. Calculate late fines
        $lateDays = $attendances->where('is_late', true)->count();
        $lateFineAmount = $lateDays * $lateFinePerDay;

        // 4. Unpaid leave deduction (pro-rate daily salary for approved Cuti Tanpa Gaji)
        $unpaidLeaveType = \App\Models\LeaveType::where('name', 'Cuti Tanpa Gaji')->first();
        $unpaidLeaveDays = 0;
        $unpaidLeaveAmount = 0;

        if ($unpaidLeaveType) {
            $unpaidLeaveRequests = \App\Models\LeaveRequest::where('user_id', $employee->user_id)
                ->where('leave_type_id', $unpaidLeaveType->id)
                ->where('status', 'approved')
                ->get()
                ->filter(function ($req) use ($period) {
                    // Count days that fall within this payroll period
                    $start = \Carbon\Carbon::parse($req->start_date)->max(\Carbon\Carbon::create($period->year, $period->month, 1));
                    $end   = \Carbon\Carbon::parse($req->end_date)->min(\Carbon\Carbon::create($period->year, $period->month)->endOfMonth());
                    return $start->lte($end);
                });

            foreach ($unpaidLeaveRequests as $req) {
                $start = \Carbon\Carbon::parse($req->start_date)->max(\Carbon\Carbon::create($period->year, $period->month, 1));
                $end   = \Carbon\Carbon::parse($req->end_date)->min(\Carbon\Carbon::create($period->year, $period->month)->endOfMonth());
                $unpaidLeaveDays += $start->diffInDays($end) + 1;
            }

            if ($unpaidLeaveDays > 0) {
                $dailySalary = round($basicSalary / $daysInMonth, 0);
                $unpaidLeaveAmount = $unpaidLeaveDays * $dailySalary;
            }
        }

        // 5. Get approved overtime
        $overtimeHours = OvertimeRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereMonth('date', $period->month)
            ->whereYear('date', $period->year)
            ->sum('hours');

        $workHoursPerMonth  = config('payroll.work_hours_per_month', 173);
        $overtimeMultiplier = config('payroll.overtime_multiplier', 1.5);
        $hourlyRate         = round($basicSalary / $workHoursPerMonth, 0);
        $overtimePay        = round($overtimeHours * $hourlyRate * $overtimeMultiplier, 0);

        // 6. BPJS & tax deductions (percentage-based)
        $bpjsHealthRate      = config('payroll.deductions.bpjs_health', 0.01);
        $bpjsEmploymentRate  = config('payroll.deductions.bpjs_employment', 0.02);
        $pph21Rate           = config('payroll.deductions.pph21', 0.05);

        $bpjsHealthAmount     = round($basicSalary * $bpjsHealthRate, 0);
        $bpjsEmploymentAmount = round($basicSalary * $bpjsEmploymentRate, 0);
        $pph21Amount          = round($basicSalary * $pph21Rate, 0);

        // 7. Totals
        $totalAllowances = $transportAllowance + $mealAllowance;
        $totalEarnings   = $basicSalary + $totalAllowances + $overtimePay;
        $totalDeductions = $lateFineAmount + $unpaidLeaveAmount + $bpjsHealthAmount + $bpjsEmploymentAmount + $pph21Amount;
        $netSalary       = $totalEarnings - $totalDeductions;

        if ($netSalary < 0) {
            \Log::warning("Negative net salary for employee {$employee->id} in period {$period->id}: {$netSalary}");
        }

        // 8. Create Payroll Record
        $payroll = Payroll::create([
            'period_id'        => $period->id,
            'employee_id'      => $employee->id,
            'basic_salary'     => $basicSalary,
            'total_allowances' => $totalAllowances + $overtimePay,
            'total_deductions' => $totalDeductions,
            'net_salary'       => $netSalary,
        ]);

        // 9. Payroll items (detailed slip)
        $this->createItem($payroll, 'Gaji Pokok', $basicSalary, 'earning');
        $this->createItem($payroll, 'Tunjangan Transport', $transportAllowance, 'earning');
        $this->createItem($payroll, 'Tunjangan Makan', $mealAllowance, 'earning');

        if ($overtimePay > 0) {
            $this->createItem($payroll, "Lembur ({$overtimeHours} jam @ " . number_format($hourlyRate * $overtimeMultiplier, 0) . "/jam)", $overtimePay, 'earning');
        }

        if ($lateFineAmount > 0) {
            $this->createItem($payroll, "Potongan Keterlambatan ({$lateDays} hari)", $lateFineAmount, 'deduction');
        }

        if ($unpaidLeaveAmount > 0) {
            $this->createItem($payroll, "Potongan Cuti Tanpa Gaji ({$unpaidLeaveDays} hari)", $unpaidLeaveAmount, 'deduction');
        }

        $this->createItem($payroll, 'BPJS Kesehatan (' . ($bpjsHealthRate * 100) . '%)', $bpjsHealthAmount, 'deduction');
        $this->createItem($payroll, 'BPJS Ketenagakerjaan (' . ($bpjsEmploymentRate * 100) . '%)', $bpjsEmploymentAmount, 'deduction');
        $this->createItem($payroll, 'PPh 21 (' . ($pph21Rate * 100) . '%)', $pph21Amount, 'deduction');

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
