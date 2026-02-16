<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FaceEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_page_loads_for_authorized_user()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'face_descriptor' => null
        ]);

        $this->actingAs($user)
            ->get(route('profile')) // Assuming enrollment is part of profile or has its own route
            ->assertStatus(200)
            ->assertSeeLivewire('employee.face-enrollment');
    }

    public function test_can_save_face_enrollment()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $descriptor = array_fill(0, 128, 0.1); // Mock descriptor
        
        Livewire::actingAs($user)
            ->test('employee.face-enrollment')
            ->set('photo', 'data:image/jpeg;base64,fakeimagedata')
            ->set('descriptor', $descriptor)
            ->call('saveEnrollment')
            ->assertHasNoErrors();

        $this->assertNotNull($employee->fresh()->face_descriptor);
        $this->assertNotNull($employee->fresh()->profile_photo_path);
    }
}
