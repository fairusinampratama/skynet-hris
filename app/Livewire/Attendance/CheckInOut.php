<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\Department;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class CheckInOut extends Component
{
    public $hasCheckedIn = false;
    public $hasCheckedOut = false;
    public $checkInTime;
    public $checkOutTime;
    
    // Form Inputs
    public $photo; // Base64
    public $latitude;
    public $longitude;
    public $accuracy;
    public $faceDescriptor; // Added for biometric verification
    public $workSummary; // For technicians
    public $hasEnrolledFace = false;
    
    // Errors
    public $error_message;

    public function mount()
    {
        $this->checkStatus();
    }

    public function checkStatus()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
            
        if ($attendance) {
            $this->hasCheckedIn = true;
            $this->checkInTime = $attendance->check_in_time;
            
            if ($attendance->check_out_time) {
                $this->hasCheckedOut = true;
                $this->checkOutTime = $attendance->check_out_time;
            }
        }
        
        $this->hasEnrolledFace = $user->employee && $user->employee->face_descriptor;
    }

    public function checkIn(AttendanceService $service)
    {
        $this->validate([
            // 'photo' => 'required', // Photo no longer required
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $employee = $user->employee;

        // 0. Face Recognition Verification
        if (!$employee->face_descriptor) {
             $this->dispatch('notify', message: 'Face not enrolled. Please complete Face Registration in your Profile first.', type: 'error');
             return;
        }

        if (!$this->faceDescriptor) {
            \Illuminate\Support\Facades\Log::warning('CheckIn Failed: No face descriptor received.', ['user_id' => $user->id]);
            $this->dispatch('notify', message: 'No face data received. Please ensure your face is clearly visible to the camera.', type: 'error');
            return;
        }

        $storedDescriptor = json_decode($employee->face_descriptor, true);
        $liveDescriptor = $this->faceDescriptor;

        // Euclidean Distance Calculation
        $distance = 0;
        for ($i = 0; $i < count($storedDescriptor); $i++) {
            $diff = $storedDescriptor[$i] - $liveDescriptor[$i];
            $distance += $diff * $diff;
        }
        $distance = sqrt($distance);

        \Illuminate\Support\Facades\Log::info('Face Verification', ['user_id' => $user->id, 'distance' => $distance]);

        // Threshold: 0.55 (Standard biometric matching, relaxed from 0.4)
        if ($distance > 0.55) {
            \Illuminate\Support\Facades\Log::warning('CheckIn Failed: Face mismatch.', ['user_id' => $user->id, 'distance' => $distance]);
            $this->dispatch('notify', message: 'Face verification failed. Data mismatch. Please try again or move closer.', type: 'error');
            return;
        }
        
        // 1. Geofence Validation (Unified Strategy)
        $isFlagged = false;
        $flagReason = null;
        
        // Check Global Office Settings
        $settings = \App\Models\CompanySetting::first();
        
        if ($settings && $settings->office_lat && $settings->office_long) {
            $inRange = $service->validateGeofence(
                $this->latitude, 
                $this->longitude, 
                $settings->office_lat, 
                $settings->office_long, 
                $settings->radius_meters
            );
            
            if (!$inRange) {
                $isFlagged = true;
                $flagReason = "Out of office bounds";
            }
        }

        // 2. Late Detection
        $checkInTime = now();
        $formattedTime = $checkInTime->format('H:i:s');
        try {
             $isLate = $service->isLate($employee->id, $checkInTime);
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Late detection error: ' . $e->getMessage());
             $isLate = false;
        }
        
        // 3. Save Photo - REMOVED per user request
        // $photoPath = $this->storePhoto($this->photo);
        
        // 4. Device Fingerprint
        $fingerprint = $service->generateDeviceFingerprint(request());

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_time' => $formattedTime,
            'check_in_lat' => $this->latitude,
            'check_in_long' => $this->longitude,
            'check_in_accuracy' => $this->accuracy,
            'check_in_photo_path' => null, // No photo saved
            'device_fingerprint' => $fingerprint,
            'is_late' => $isLate,
            'is_flagged' => $isFlagged,
            'flag_reason' => $flagReason,
        ]);

        $this->dispatch('notify', message: 'Checked in successfully!', type: 'success');
        $this->checkStatus();
    }

    public function checkOut()
    {
        try {
            $user = Auth::user();
            
            // Unified: Summary is optional for everyone, or enforce if needed.
            // For now, we make it optional to be flexible.
            // if ($this->workSummary) { ... }
    
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->first();
    
            if ($attendance) {
                $attendance->update([
                    'check_out_time' => now()->format('H:i:s'),
                    'work_summary' => $this->workSummary,
                ]);
                
                $this->dispatch('notify', message: 'Checked out successfully!', type: 'success');
                $this->checkStatus();
            } else {
                $this->dispatch('notify', message: "Could not find today's attendance record to check out.", type: 'error');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', message: "Check out failed: " . $e->getMessage(), type: 'error');
            \Illuminate\Support\Facades\Log::error('CheckOut Error: ' . $e->getMessage());
        }
    }
    
    // private function storePhoto($base64Image) ... REMOVED

    public function render()
    {
        return view('livewire.attendance.check-in-out');
    }
}
