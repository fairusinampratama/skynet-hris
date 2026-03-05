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
                'status' => 'draft', // Create as draft initially to allow calculation
            ]);
        }

        // Generate accurate payroll using the service
        $payrollService = app(\App\Services\PayrollService::class);
        foreach ($periods as $month => $period) {
            $payrollService->generatePayroll($period);
            
            // Sprinkle some random bonuses ON THE DRAFT PAYROLL BEFORE LOCKING
            $payrolls = Payroll::where('period_id', $period->id)->get();
            foreach ($payrolls as $payroll) {
                if ($faker->boolean(20)) { // 20% chance
                    $bonus = $faker->randomElement([100000, 250000, 500000, 1000000]);
                    
                    // We must update using DB facade to easily bypass model events or just do it normally since it is draft
                    $payroll->update([
                        'bonus' => $bonus,
                        'net_salary' => $payroll->net_salary + $bonus
                    ]);
                }
            }

            // Update to final intended status
            $period->update(['status' => $statuses[$month]]);
        }
    }
}
