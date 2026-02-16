<?php

namespace App\Livewire\Employee;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Profile extends Component
{
    #[On('profile-updated')]
    #[On('password-updated')]
    public function refreshProfile()
    {
        // Re-renders the component to show updated data
    }

    public function render()
    {
        return view('livewire.employee.profile', [
            'user' => Auth::user(),
        ]);
    }
}
