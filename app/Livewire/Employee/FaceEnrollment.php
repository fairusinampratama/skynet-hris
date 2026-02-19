<?php

namespace App\Livewire\Employee;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class FaceEnrollment extends Component
{
    public $photo;
    public $descriptor;
    public $hasEnrolledFace = false;

    public function mount()
    {
        $user = Auth::user();
        if ($user->employee && $user->employee->face_descriptor) {
            $this->hasEnrolledFace = true;
        }
    }

    public function saveEnrollment()
    {
        $this->validate([
            'photo' => 'required',
            'descriptor' => ['required', 'array', function ($attribute, $value, $fail) {
                if (count($value) !== 128) {
                    $fail('The '.$attribute.' must be a valid face descriptor with 128 features.');
                }
            }],
        ]);

        $user = Auth::user();
        
        if (!$user->employee) {
            $this->addError('photo', 'No associated employee record found. Please contact administrator.');
            return;
        }

        if ($user->employee->face_descriptor) {
            $this->addError('photo', 'Face already registered. Please contact HR to reset.');
            return;
        }

        \Illuminate\Support\Facades\Log::info('Face Enrollment Attempt', ['user_id' => $user->id]);
        
        // 1. Decode and save photo
        $image = str_replace('data:image/jpeg;base64,', '', $this->photo);
        $image = str_replace(' ', '+', $image);
        $imageName = 'face-enrollment/' . $user->id . '-' . time() . '.jpg';
        
        Storage::put($imageName, base64_decode($image));

        // 2. Update Employee record
        $user->employee->update([
            'profile_photo_path' => $imageName,
            'face_descriptor' => json_encode($this->descriptor)
        ]);

        \Illuminate\Support\Facades\Log::info('Face Enrollment Success', ['user_id' => $user->id]);

        session()->flash('message', 'Face registered successfully! You can now use Face Recognition check-in.');
        $this->dispatch('enrollment-saved');
    }

    public function render()
    {
        return view('livewire.employee.face-enrollment');
    }
}
