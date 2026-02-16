<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_salary_correctness()
    {
        // Setup
        $user = User::factory()->create();
        $dept = \App\Models\Department::create([
             'name' => 'Test Dept',
             'office_lat' => 0, 
             'office_long' => 0, 
             'radius_meters' => 100
        ]);
        
        $employee = Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
             'basic_salary' => 5000000,
             'role_type' => 'office',
             'join_date' => now(),
        ]);

        $period = PayrollPeriod::create([
            'month' => 1,
            'year' => 2026,
            'status' => 'draft',
        ]);

        // Mock Attendances: 2 Late days
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-01-02',
            'check_in_time' => '08:00:00',
            'is_late' => true,
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-01-03',
            'check_in_time' => '08:00:00',
            'is_late' => true,
        ]);

        $service = new PayrollService();
        $payroll = $service->calculateSalary($employee, $period);

        // Basic: 5,000,000
        // Allowances: 500,000
        // Late Fine: 2 * 50,000 = 100,000
        // BPJS: 200,000
        // Total Deductions: 300,000
        // Net: 5,500,000 - 300,000 = 5,200,000

        $this->assertEquals(5000000, $payroll->basic_salary);
        $this->assertEquals(500000, $payroll->total_allowances);
        $this->assertEquals(300000, $payroll->total_deductions);
        $this->assertEquals(5200000, $payroll->net_salary);
    }
}
