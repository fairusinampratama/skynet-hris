<?php

namespace App\Livewire\Employee;

use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Requests extends Component
{
    #[On('request-submitted')]
    public function refreshRequests()
    {
        // Automatically re-renders the component
    }

    public function render()
    {
        $user = Auth::user();
        
        $leaves = LeaveRequest::where('user_id', $user->id) // Assuming LeaveRequest uses user_id, need to verify
            ->get()
            ->map(function ($item) {
                $item->type_label = 'Leave'; // Label for UI
                $item->sort_date = $item->start_date;
                return $item;
            });
            
        $overtime = OvertimeRequest::where('employee_id', $user->employee->id ?? 0) // Overtime usually flagged to employee
            ->get()
            ->map(function ($item) {
                $item->type_label = 'Overtime';
                $item->sort_date = $item->date;
                return $item;
            });
            
        // Merge and Sort
        $requests = $leaves->concat($overtime)->sortByDesc('sort_date');

        return view('livewire.employee.requests', [
            'requests' => $requests
        ]);
    }
}
