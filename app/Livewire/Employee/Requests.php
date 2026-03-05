<?php

namespace App\Livewire\Employee;

use App\Models\LeaveRequest;
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

        $requests = LeaveRequest::where('user_id', $user->id)
            ->orderByDesc('start_date')
            ->get()
            ->map(function ($item) {
                $item->days = \Carbon\Carbon::parse($item->start_date)
                    ->diffInDays(\Carbon\Carbon::parse($item->end_date)) + 1;
                $item->sort_date = $item->start_date;
                return $item;
            });

        return view('livewire.employee.requests', [
            'requests' => $requests,
        ]);
    }
}
