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
        // 1. Get attendance records for the month
        $attendances = Attendance::where('user_id', $employee->user_id)
            ->whereMonth('date', $period->month)
            ->whereYear('date', $period->year)
            ->get();

        // 2. Calculate late fines
        $lateDays = $attendances->where('is_late', true)->count();
        $lateFineAmount = $lateDays * 50000; // Configurable ideally

        // 3. Get approved overtime
        $overtimeHours = OvertimeRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereMonth('date', $period->month)
            ->whereYear('date', $period->year)
            ->sum('hours');
        
        $overtimeRate = 20000; // per hour
        $overtimePay = $overtimeHours * $overtimeRate;

        // 4. Base components
        $basicSalary = $employee->basic_salary;
        $allowances = 500000; // Fixed transport/meal allowance for now
        $bpjsDeduction = 200000; // Fixed deduction

        $totalEarnings = $basicSalary + $allowances + $overtimePay;
        $totalDeductions = $lateFineAmount + $bpjsDeduction;
        $netSalary = $totalEarnings - $totalDeductions;

        // 5. Create Payroll Record
        $payroll = Payroll::create([
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'basic_salary' => $basicSalary,
            'total_allowances' => $allowances + $overtimePay, // Grouping OT into allowances for simplicity in DB, or separate if column exists
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ]);

        // 6. Create Items (for detailed slip)
        $this->createItem($payroll, 'Basic Salary', $basicSalary, 'earning');
        $this->createItem($payroll, 'Fixed Allowance', $allowances, 'earning');
        if ($overtimePay > 0) {
            $this->createItem($payroll, "Overtime ({$overtimeHours} hrs)", $overtimePay, 'earning');
        }
        if ($lateFineAmount > 0) {
            $this->createItem($payroll, "Late Fine ({$lateDays} days)", $lateFineAmount, 'deduction');
        }
        $this->createItem($payroll, 'BPJS Kesehatan', $bpjsDeduction, 'deduction');

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

    public function generatePdf(Payroll $payroll)
    {
        $payroll->load(['items', 'employee.user', 'period']);
        $pdf = Pdf::loadView('pdf.payslip', ['payroll' => $payroll]);
        
        $filename = "payslips/{$payroll->period->year}/{$payroll->period->month}/slip_{$payroll->id}.pdf";
        Storage::put("public/$filename", $pdf->output());
        
        $payroll->update(['pdf_path' => $filename]);
        
        return $filename;
    }
}
