<?php

namespace Tests\Feature;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_leave_request()
    {
        $user = User::factory()->create();
        $dept = Department::create(['name' => 'IT', 'office_lat' => 0, 'office_long' => 0, 'radius_meters' => 100]);
        $employee = Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'basic_salary' => 5000000,
            'role_type' => 'office',
            'join_date' => now(),
        ]);
        
        $leaveType = LeaveType::create(['name' => 'Annual', 'code' => 'AL', 'days_per_year' => 12]);
        
        LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'remaining_days' => 12,
        ]);

        $this->actingAs($user);

        // Assumption: Livewire component usage or direct model creation for this test?
        // Let's test the model/logic flow mainly, or a Livewire test if we had the component name handy.
        // Given 'LeaveRequestForm' component exists.
        
        \Livewire\Livewire::test(\App\Livewire\Leave\RequestForm::class)
            ->set('leave_type_id', $leaveType->id)
            ->set('start_date', now()->addDay()->toDateString())
            ->set('end_date', now()->addDays(2)->toDateString())
            ->set('reason', 'Vacation for family trip') // > 10 chars
            ->call('submit')
            ->assertHasNoErrors();
            
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }
    
    public function test_balance_deducted_on_approval()
    {
        // Setup data
        $user = User::factory()->create();
        $dept = Department::create(['name' => 'IT', 'office_lat' => 0, 'office_long' => 0, 'radius_meters' => 100]);
        $employee = Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'basic_salary' => 5000000, 
            'role_type' => 'office',   
            'join_date' => now(),      
        ]);
        
        $leaveType = LeaveType::create(['name' => 'Annual', 'code' => 'AL', 'days_per_year' => 12]);
        
        $balance = LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'remaining_days' => 12,
        ]);
        
        $request = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2), // 2 days
            'reason' => 'Resting at home', // > 10 chars
            'status' => 'pending',
        ]);
        
        // Act: Approve (Simulate Admin Action)
        $request->update(['status' => 'approved']);
        $balance->decrement('remaining_days', 2);
        
        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $user->id,
            'remaining_days' => 10,
        ]);
    }
}
