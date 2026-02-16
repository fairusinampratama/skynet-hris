<?php

namespace Tests\Feature;

use App\Livewire\Attendance\CheckInOut;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceCheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_check_in_successfully()
    {
        $user = User::factory()->create();
        $dept = Department::create([
            'name' => 'Office',
            'office_lat' => -7.250445, 
            'office_long' => 112.768845,
            'radius_meters' => 100
        ]);
        
        Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'basic_salary' => 5000000,
            'role_type' => 'office',
            'join_date' => now(),
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
        ]);

        $this->actingAs($user);

        // Simulate being at the office
        Livewire::test(CheckInOut::class)
            ->set('latitude', -7.250445)
            ->set('longitude', 112.768845)
            ->set('photo', 'data:image/png;base64,fake_photo_base64_string')
            ->set('faceDescriptor', array_fill(0, 128, 0.1))
            ->call('checkIn')
            ->assertHasNoErrors()
            ->assertSee('Checked in successfully');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => now()->startOfDay(),
            'is_flagged' => 0,
        ]);
    }

    public function test_check_in_flagged_when_far_away()
    {
        $user = User::factory()->create();
        $dept = Department::create([
            'name' => 'Office',
            'office_lat' => -7.250445, 
            'office_long' => 112.768845,
            'radius_meters' => 100
        ]);
        
        Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'basic_salary' => 5000000,
            'role_type' => 'office',
            'join_date' => now(),
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
        ]);

        $this->actingAs($user);

        // 10km away
        Livewire::test(CheckInOut::class)
            ->set('latitude', -7.350445)
            ->set('longitude', 112.768845)
            ->set('photo', 'data:image/png;base64,fake_photo')
            ->set('faceDescriptor', array_fill(0, 128, 0.1))
            ->call('checkIn')
            ->assertHasNoErrors(); // Should NOT error, but flag

        $this->assertDatabaseHas('attendances', [
             'user_id' => $user->id,
             'is_flagged' => 1,
             'flag_reason' => 'Out of office bounds'
        ]);
    }

    public function test_check_in_fails_if_face_does_not_match()
    {
        $user = User::factory()->create();
        $dept = Department::create(['name' => 'Test']);
        
        // Setup employee with a stored descriptor [0.1, 0.2, 0.3...]
        $storedDescriptor = array_fill(0, 128, 0.1);
        Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'basic_salary' => 5000,
            'role_type' => 'technician',
            'join_date' => now(),
            'face_descriptor' => json_encode($storedDescriptor)
        ]);

        $this->actingAs($user);

        // Try to check in with a very different descriptor [0.9, 0.9, 0.9...]
        $liveDescriptor = array_fill(0, 128, 0.9);

        Livewire::test(CheckInOut::class)
            ->set('latitude', 0)
            ->set('longitude', 0)
            ->set('photo', 'fake_photo')
            ->set('faceDescriptor', $liveDescriptor)
            ->set('faceDescriptor', $liveDescriptor)
            ->call('checkIn')
            ->assertSet('error_message', 'Face verification failed. Data mismatch. Please try again or move closer.');
    }

    public function test_check_in_succeeds_with_matching_face()
    {
        $user = User::factory()->create();
        $dept = Department::create(['name' => 'Test']);
        
        // Setup employee with a stored descriptor
        $storedDescriptor = array_fill(0, 128, 0.1);
        Employee::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'basic_salary' => 5000,
            'role_type' => 'technician',
            'join_date' => now(),
            'face_descriptor' => json_encode($storedDescriptor)
        ]);

        $this->actingAs($user);

        // Try to check in with a matching descriptor
        $liveDescriptor = array_fill(0, 128, 0.11); // Very close

        Livewire::test(CheckInOut::class)
            ->set('latitude', 0)
            ->set('longitude', 0)
            ->set('photo', 'fake_photo')
            ->set('faceDescriptor', $liveDescriptor)
            ->call('checkIn')
            ->assertHasNoErrors()
            ->assertSee('Checked in successfully');
    }
}
