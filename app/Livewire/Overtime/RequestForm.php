<?php

namespace App\Livewire\Overtime;

use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class RequestForm extends Component
{
    public $date;
    public $hours;
    public $reason;

    public $myRequests;

    public function mount()
    {
        $this->loadRequests();
    }

    public function loadRequests()
    {
        $user = Auth::user();
        if ($user->employee) {
            $this->myRequests = OvertimeRequest::where('employee_id', $user->employee->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        } else {
            $this->myRequests = [];
        }
    }

    public function submit()
    {
        $this->validate([
            'date' => 'required|date|before_or_equal:today', 
            'hours' => 'required|numeric|min:0.5|max:12',
            'reason' => 'required|string|min:5',
        ]);

        $user = Auth::user();
        if (!$user->employee) {
            $this->dispatch('notify', message: 'You are not linked to an employee record.', type: 'error');
            return;
        }

        OvertimeRequest::create([
            'employee_id' => $user->employee->id,
            'date' => $this->date,
            'hours' => $this->hours,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->dispatch('notify', message: 'Overtime request submitted successfully!', type: 'success');
        $this->reset(['date', 'hours', 'reason']);
        $this->loadRequests();
        $this->dispatch('request-submitted');
    }

    public function render()
    {
        return view('livewire.overtime.request-form');
    }
}
