<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\PayrollItem;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $employees = Employee::all();

        // Create 6 payroll periods (Jan-Jun 2026)
        $periods = [];
        $statuses = [
            1 => 'locked',     // Jan
            2 => 'locked',     // Feb
            3 => 'locked',     // Mar
            4 => 'locked',     // Apr
            5 => 'finalized',  // May
            6 => 'draft',      // Jun
        ];

        for ($month = 1; $month <= 6; $month++) {
            $periods[$month] = PayrollPeriod::create([
                'month' => $month,
                'year' => 2026,
                'status' => $statuses[$month],
            ]);
        }

        // Allowance & deduction templates
        $earningTemplates = [
            ['name' => 'Tunjangan Transport', 'min' => 300000, 'max' => 500000],
            ['name' => 'Tunjangan Makan',     'min' => 400000, 'max' => 600000],
        ];

        $deductionTemplates = [
            ['name' => 'BPJS Kesehatan',   'rate' => 0.01],  // 1% of salary
            ['name' => 'BPJS Ketenagakerjaan', 'rate' => 0.02], // 2% of salary
            ['name' => 'PPh 21',            'rate' => 0.05],  // 5% simplified
        ];

        foreach ($employees as $emp) {
            foreach ($periods as $month => $period) {
                $basicSalary = (float) $emp->basic_salary;

                // Calculate earnings
                $totalEarnings = 0;
                $items = [];

                foreach ($earningTemplates as $template) {
                    $amount = $faker->numberBetween($template['min'], $template['max']);
                    $totalEarnings += $amount;
                    $items[] = [
                        'name' => $template['name'],
                        'amount' => $amount,
                        'type' => 'earning',
                    ];
                }

                // Add overtime pay if applicable (random, more for Teknisi/NOC)
                $deptName = $emp->department->name ?? '';
                $overtimeChance = in_array($deptName, ['Teknisi', 'NOC']) ? 60 : 30;
                if ($faker->boolean($overtimeChance)) {
                    $overtimeHours = $faker->randomFloat(1, 1, 8);
                    $overtimeRate = round($basicSalary / 173, 0); // per-hour rate
                    $overtimeAmount = round($overtimeHours * $overtimeRate * 1.5, 0);
                    $totalEarnings += $overtimeAmount;
                    $items[] = [
                        'name' => 'Lembur (' . $overtimeHours . ' jam)',
                        'amount' => $overtimeAmount,
                        'type' => 'earning',
                    ];
                }

                // Calculate deductions
                $totalDeductions = 0;
                foreach ($deductionTemplates as $template) {
                    $amount = round($basicSalary * $template['rate'], 0);
                    $totalDeductions += $amount;
                    $items[] = [
                        'name' => $template['name'],
                        'amount' => $amount,
                        'type' => 'deduction',
                    ];
                }

                // Late penalty (based on month — could be calculated from attendance, but simulated here)
                $latePenalty = $faker->boolean(25) ? $faker->numberBetween(50000, 300000) : 0;
                if ($latePenalty > 0) {
                    $totalDeductions += $latePenalty;
                    $items[] = [
                        'name' => 'Potongan Keterlambatan',
                        'amount' => $latePenalty,
                        'type' => 'deduction',
                    ];
                }

                $netSalary = $basicSalary + $totalEarnings - $totalDeductions;

                $payroll = Payroll::create([
                    'period_id' => $period->id,
                    'employee_id' => $emp->id,
                    'basic_salary' => $basicSalary,
                    'total_allowances' => $totalEarnings,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'pdf_path' => null,
                ]);

                // Create payroll items
                foreach ($items as $item) {
                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'name' => $item['name'],
                        'amount' => $item['amount'],
                        'type' => $item['type'],
                    ]);
                }
            }
        }
    }
}
