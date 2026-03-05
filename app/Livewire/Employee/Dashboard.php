<?php

namespace App\Livewire\Employee;

use App\Models\Attendance;
use App\Models\LeaveRequest;
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

        // 2. Leave Balance (derived from approved leave requests this year)
        $leaveDaysTaken = \App\Models\LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->get()
            ->sum(fn ($req) => $req->start_date->diffInDays($req->end_date) + 1);

        // 3. Recent Activity (Last 5 attendances)
        $recentActivity = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return view('livewire.employee.dashboard', [
            'user' => $user,
            'status' => $status,
            'todayAttendance' => $todayAttendance,
            'leaveBalance' => $leaveDaysTaken,
            'recentActivity' => $recentActivity,
        ]);
    }
}
