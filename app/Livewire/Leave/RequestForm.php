<?php

namespace App\Livewire\Leave;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class RequestForm extends Component
{
    use WithFileUploads;

    public $leave_type_id;
    public $start_date;
    public $end_date;
    public $reason;
    public $attachment;

    public $balances;

    public function mount()
    {
        $this->loadBalances();
    }

    public function loadBalances()
    {
        $this->balances = LeaveBalance::where('user_id', Auth::id())
            ->with('leaveType')
            ->get();
    }

    public function submit()
    {
        $this->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'attachment' => 'nullable|file|max:2048', // 2MB max
        ]);

        $leaveType = LeaveType::find($this->leave_type_id);
        
        // Check attachment requirement
        if ($leaveType->requires_attachment && !$this->attachment) {
            $this->addError('attachment', 'Attachment is required for this leave type.');
            return;
        }

        // Check Balance
        if ($leaveType->quota !== null) {
            $daysRequested = $this->calculateDays($this->start_date, $this->end_date);
            $balance = LeaveBalance::where('user_id', Auth::id())
                ->where('leave_type_id', $this->leave_type_id)
                ->first();

            if (!$balance || $balance->remaining_days < $daysRequested) {
                $this->addError('leave_type_id', 'Insufficient leave balance.');
                return;
            }
        }

        $path = null;
        if ($this->attachment) {
            $path = $this->attachment->store('leave-attachments', 'public');
        }

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reason' => $this->reason,
            'attachment_path' => $path,
            'status' => 'pending',
        ]);

        $this->dispatch('notify', message: 'Leave request submitted successfully!', type: 'success');
        $this->reset(['leave_type_id', 'start_date', 'end_date', 'reason', 'attachment']);
        $this->loadBalances();
        $this->dispatch('request-submitted');
    }

    private function calculateDays($start, $end)
    {
        $startDate = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($end);
        
        // Simple day calculation (inclusive)
        // In a real app, you'd exclude weekends/holidays here
        return $startDate->diffInDays($endDate) + 1;
    }

    public function render()
    {
        return view('livewire.leave.request-form', [
            'leaveTypes' => LeaveType::all(),
        ]);
    }
}
