<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RobustFaceRecognitionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rejects_enrollment_with_missing_descriptor()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('employee.face-enrollment')
            ->set('photo', 'data:image/jpeg;base64,fake')
            ->set('descriptor', null) // Missing descriptor
            ->call('saveEnrollment')
            ->assertHasErrors(['descriptor' => 'required']);
    }

    /** @test */
    public function it_rejects_enrollment_with_empty_descriptor_array()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('employee.face-enrollment')
            ->set('photo', 'data:image/jpeg;base64,fake')
            ->set('descriptor', []) // Empty array
            ->call('saveEnrollment')
            ->assertHasErrors(['descriptor']); // validation might need 'required' or 'min' check
    }

    /** @test */
    public function it_rejects_check_in_if_descriptor_is_missing_from_payload()
    {
        $user = User::factory()->create();
        $user->employee()->save(Employee::factory()->make([
            'role_type' => 'office',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
        ]));

        Livewire::actingAs($user)
            ->test('attendance.check-in-out')
            ->set('latitude', -6.200000)
            ->set('longitude', 106.816666)
            ->set('photo', 'data:image/jpeg;base64,fake')
            ->set('faceDescriptor', null) // Missing descriptor
            ->call('checkIn')
            ->assertHasErrors('photo'); // "Face recognition failed" error is attached to photo field
    }

    /** @test */
    public function it_rejects_check_in_if_face_does_not_match()
    {
        $user = User::factory()->create();
        $user->employee()->save(Employee::factory()->make([
            'role_type' => 'office',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
        ]));

        // Check in with a completely different face
        Livewire::actingAs($user)
            ->test('attendance.check-in-out')
            ->set('latitude', -6.200000)
            ->set('longitude', 106.816666)
            ->set('photo', 'data:image/jpeg;base64,fake')
            ->set('faceDescriptor', array_fill(0, 128, 0.9)) // Different vector
            ->call('checkIn')
            ->assertHasErrors('photo'); // should be "Face does not match"
    }

    /** @test */
    public function it_accepts_check_in_with_valid_and_matching_descriptor()
    {
        $user = User::factory()->create();
        $user->employee()->save(Employee::factory()->make([
            'role_type' => 'office',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
        ]));

        $today = now()->format('Y-m-d 00:00:00'); // Match DB format exactly

        Livewire::actingAs($user)
            ->test('attendance.check-in-out')
            ->set('latitude', -6.200000)
            ->set('longitude', 106.816666)
            ->set('photo', 'data:image/jpeg;base64,fake')
            ->set('faceDescriptor', array_fill(0, 128, 0.1)) // Exact match
            ->call('checkIn')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => $today,
        ]);
    }
}
