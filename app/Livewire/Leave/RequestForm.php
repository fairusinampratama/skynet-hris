<?php

namespace App\Livewire\Leave;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class RequestForm extends Component
{
    use WithFileUploads;

    const LEAVE_TYPES = [
        'Izin Sakit'             => 'Izin Sakit',
        'Izin Telat'             => 'Izin Telat',
        'Izin Keperluan Pribadi' => 'Izin Keperluan Pribadi',
    ];

    public $type;
    public $start_date;
    public $end_date;
    public $reason;
    public $attachment;

    public function submit()
    {
        $this->validate([
            'type'       => 'required|in:' . implode(',', array_keys(self::LEAVE_TYPES)),
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|min:10',
            'attachment' => 'nullable|file|max:2048',
        ]);

        $path = null;
        if ($this->attachment) {
            $path = $this->attachment->store('leave-attachments', 'public');
        }

        LeaveRequest::create([
            'user_id'         => Auth::id(),
            'type'            => $this->type,
            'start_date'      => $this->start_date,
            'end_date'        => $this->end_date,
            'reason'          => $this->reason,
            'attachment_path' => $path,
            'status'          => 'pending',
        ]);

        $this->dispatch('notify', message: 'Permohonan izin berhasil dikirim!', type: 'success');
        $this->reset(['type', 'start_date', 'end_date', 'reason', 'attachment']);
        $this->dispatch('request-submitted');
    }

    public function render()
    {
        return view('livewire.leave.request-form', [
            'leaveTypes' => self::LEAVE_TYPES,
        ]);
    }
}
