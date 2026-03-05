<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Foundation
            RolePermissionSeeder::class,
            CompanySettingSeeder::class,

            // 2. Users (Super Admin only)
            UserSeeder::class,

            // 3. Configuration
            HolidaySeeder::class,

            // 4. Organizational Structure
            DepartmentSeeder::class,
            EmployeeSeeder::class,

            // 5. Shift & Scheduling
            DefaultShiftSeeder::class,
            ScheduleSeeder::class,

            // 6. Day-to-Day Operations
            LeaveRequestSeeder::class,
            AttendanceSeeder::class,

            // 7. Compensation
            PayrollSeeder::class,
        ]);
    }
}
