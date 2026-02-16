<?php

namespace App\Filament\Resources\EmployeeShiftResource\Pages;

use App\Filament\Resources\EmployeeShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeShifts extends ListRecords
{
    protected static string $resource = EmployeeShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
