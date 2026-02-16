<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Only create overrides for shift-schedule departments (Teknisi, NOC)
        $employees = Employee::whereHas('department', fn($q) => $q->where('has_shift_schedule', true))->get();

        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::now()->endOfMonth();

        foreach ($employees as $emp) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // ~10% chance to have a shift override on any given day
                if ($faker->boolean(10)) {
                    $isSunday = $currentDate->isSunday();

                    if ($isSunday) {
                        // Working on Sunday (custom ON)
                        Schedule::create([
                            'employee_id' => $emp->id,
                            'date' => $currentDate->toDateString(),
                            'is_off' => false,
                            'start_time' => '10:00:00',
                            'end_time' => '19:00:00',
                        ]);
                    } else {
                        // Day off override on a weekday
                        Schedule::create([
                            'employee_id' => $emp->id,
                            'date' => $currentDate->toDateString(),
                            'is_off' => true,
                        ]);
                    }
                }

                $currentDate->addDay();
            }
        }
    }
}
