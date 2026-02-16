<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
        ];

        $user = \App\Models\User::create($userData);

        $data['user_id'] = $user->id;

        // Remove user fields from data so they don't try to save to employees table
        unset($data['name']);
        unset($data['email']);
        unset($data['password']);
        unset($data['password_confirmation']);

        return $data;
    }
}
