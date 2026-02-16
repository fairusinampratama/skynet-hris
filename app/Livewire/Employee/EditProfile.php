<?php

namespace App\Livewire\Employee;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditProfile extends Component
{
    public $name;
    public $phone_number;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->phone_number = $user->phone_number;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|min:3|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $this->name,
            'phone_number' => $this->phone_number,
        ]);

        session()->flash('message', 'Profile updated successfully.');
        $this->dispatch('profile-updated');
    }

    public function render()
    {
        return view('livewire.employee.edit-profile');
    }
}
