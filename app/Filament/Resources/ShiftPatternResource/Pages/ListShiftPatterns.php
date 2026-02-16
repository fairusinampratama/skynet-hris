<?php

namespace App\Filament\Resources\ShiftPatternResource\Pages;

use App\Filament\Resources\ShiftPatternResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShiftPatterns extends ListRecords
{
    protected static string $resource = ShiftPatternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
