<?php

namespace App\Filament\Resources\EmployeeShiftResource\Pages;

use App\Filament\Resources\EmployeeShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeShift extends EditRecord
{
    protected static string $resource = EmployeeShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
