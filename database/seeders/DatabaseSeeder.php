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
            DepartmentSeeder::class,
            CompanySettingSeeder::class,

            // 2. Users & Employees
            UserSeeder::class,
            EmployeeSeeder::class,
            DefaultShiftSeeder::class,

            // 3. Leave Setup
            LeaveTypeSeeder::class,
            LeaveBalanceSeeder::class,

            // 4. Operational Data
            HolidaySeeder::class,
            AttendanceSeeder::class,
            ScheduleSeeder::class,

            // 5. Requests
            LeaveRequestSeeder::class,
            OvertimeRequestSeeder::class,

            // 6. Payroll
            PayrollSeeder::class,
        ]);
    }
}
