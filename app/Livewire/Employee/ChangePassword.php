<?php

namespace App\Livewire\Employee;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePassword extends Component
{
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        session()->flash('message', 'Password changed successfully.');
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('password-updated');
    }

    public function render()
    {
        return view('livewire.employee.change-password');
    }
}
