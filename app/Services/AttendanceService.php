<?php

namespace App\Services;

use App\Models\EmployeeShift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceService
{
    /**
     * Validate if the user is within the allowed radius of the office.
     */
    public function validateGeofence($userLat, $userLong, $officeLat, $officeLong, $radiusMeters): bool
    {
        if (!$userLat || !$userLong) {
            return false;
        }

        $distance = $this->calculateDistance($userLat, $userLong, $officeLat, $officeLong);
        return $distance <= $radiusMeters;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in meters.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if the employee is late based on their shift.
     */
    public function isLate($employeeId, $checkInTime): bool
    {
        $now = Carbon::parse($checkInTime);
        $dayOfWeek = $now->dayOfWeek; // 0=Sunday, 6=Saturday

        // 0. Check for Daily Schedule Override (Roster)
        $schedule = \App\Models\Schedule::where('employee_id', $employeeId)
            ->where('date', $now->toDateString())
            ->first();

        if ($schedule) {
            if ($schedule->is_off) {
                return false; // Not late if they are OFF (technically shouldn't check in, but let's say safe)
            }
            
            // If shift_id is present, we could link to a shift template, 
            // but for "Simplicity", let's assume we might add start_time/end_time columns to Schedule later if needed.
            // For now, if "is_off" is false, they are working. 
            // If the user wants specific hours per day in the grid, we need those columns.
            
            if ($schedule->start_time) {
                 $startTime = Carbon::parse($schedule->start_time);
                 return $now->greaterThan($startTime->addMinutes(15));
            }
            
            // If schedule exists but no specific time, fall back to default or shift?
            // Let's assume if they are scheduled ON, they follow the default shift for that day or a global default.
        }

        // 1. Check for custom shift
        $shift = EmployeeShift::where('employee_id', $employeeId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if ($shift) {
            $startTime = Carbon::parse($shift->start_time);
            // Allow 15 mins grace period
            return $now->greaterThan($startTime->addMinutes(15));
        }

        // 2. Fallback to default office hours (Mon-Sat, 08:00)
        // If it's Sunday and no shift, they technically shouldn't be working, but let's assume not late for now or handle elsewhere.
        if ($dayOfWeek === 0) {
            return false; // Or throw exception: No work scheduled
        }

        $defaultStart = Carbon::parse('08:00:00');
        return $now->greaterThan($defaultStart->addMinutes(15));
    }

    /**
     * Generate a device fingerprint hash.
     */
    public function generateDeviceFingerprint(Request $request): string
    {
        // Simple fingerprint: UserAgent + IP. 
        // In a real app, you might use a client-side library to send a more robust fingerprint.
        return hash('sha256', $request->userAgent() . $request->ip());
    }
}
