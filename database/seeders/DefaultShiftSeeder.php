<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultShiftSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Assign Office Shift (Mon-Sat, 08:00-17:00) to John Staff
        // Get user 'john@skynet.com' -> find employee -> assign shift
        
        // This requires 'Employee' model or query. Let's use query for speed.
        
        $employees = DB::table('employees')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->select('employees.id', 'departments.name as dept_name')
            ->get();

        foreach ($employees as $emp) {
            // Determine shift days based on department
            // Teknisi / NOC / Operations -> Tue-Sun (Off Mon)
            // Others -> Mon-Sat (Off Sun)
            
            if (in_array($emp->dept_name, ['Teknisi', 'NOC', 'Operations'])) {
                $days = [0, 2, 3, 4, 5, 6]; // Sun, Tue-Sat
                $startTime = '10:00:00';
                $endTime = '19:00:00';
            } else {
                $days = [1, 2, 3, 4, 5, 6]; // Mon-Sat
                $startTime = '08:00:00';
                $endTime = '17:00:00';
            }

            foreach ($days as $day) {
                DB::table('employee_shifts')->updateOrInsert(
                    [
                        'employee_id' => $emp->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
