<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PayrollLockingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_update_attendance_when_period_is_locked()
    {
        $user = User::factory()->create();
        $date = now()->subMonth(); // Last month
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'check_in_time' => '08:00:00',
        ]);

        // Create locked period
        PayrollPeriod::create([
            'month' => $date->month,
            'year' => $date->year,
            'status' => 'locked',
        ]);

        $this->expectException(ValidationException::class);

        // Try to update
        $attendance->update(['check_out_time' => '17:00:00']);
    }

    public function test_can_update_attendance_when_period_is_draft()
    {
        $user = User::factory()->create();
        $date = now()->subMonth(); 
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'check_in_time' => '08:00:00',
        ]);

        PayrollPeriod::create([
            'month' => $date->month,
            'year' => $date->year,
            'status' => 'draft',
        ]);

        $attendance->update(['check_out_time' => '17:00:00']);
        
        $this->assertEquals('17:00:00', $attendance->fresh()->check_out_time);
    }
}
