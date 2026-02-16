<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $employee = \App\Models\Employee::find($data['id']);
        if ($employee && $employee->user) {
            $data['name'] = $employee->user->name;
            $data['email'] = $employee->user->email;
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $employee = \App\Models\Employee::find($data['id']);
        
        if ($employee && $employee->user) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];
            
            if (!empty($data['password'])) {
                $userData['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
            }
            
            $employee->user->update($userData);
        }

        // Remove user fields
        unset($data['name']);
        unset($data['email']);
        unset($data['password']);
        unset($data['password_confirmation']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
