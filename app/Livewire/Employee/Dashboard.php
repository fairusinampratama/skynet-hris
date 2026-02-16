<?php

namespace App\Livewire\Employee;

use App\Models\Attendance;
use App\Models\LeaveBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 1. Today's Attendance Status
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $status = 'not_checked_in';
        if ($todayAttendance) {
            $status = $todayAttendance->check_out_time ? 'checked_out' : 'checked_in';
        }

        // 2. Leave Balance
        $leaveBalance = LeaveBalance::where('user_id', $user->id)
            ->sum('remaining_days'); // Summing just in case multiple types, though usually 1 record per type

        // 3. Recent Activity (Last 5 attendances)
        $recentActivity = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return view('livewire.employee.dashboard', [
            'user' => $user,
            'status' => $status,
            'todayAttendance' => $todayAttendance,
            'leaveBalance' => $leaveBalance,
            'recentActivity' => $recentActivity,
        ]);
    }
}
