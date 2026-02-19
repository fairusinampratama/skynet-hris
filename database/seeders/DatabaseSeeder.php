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
            LeaveTypeSeeder::class,
            HolidaySeeder::class,
            
            // Note: Department, Employee, Attendance, Schedule, Payroll seeders are for demo only and have been disabled.
        ]);
    }
}
