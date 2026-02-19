<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => \Filament\Resources\Components\Tab::make('Active')
                ->modifyQueryUsing(fn ($query) => $query->whereNull('resignation_date'))
                ->badge(\App\Models\Employee::whereNull('resignation_date')->count())
                ->icon('heroicon-m-user'),
            'resigned' => \Filament\Resources\Components\Tab::make('Resigned')
                ->modifyQueryUsing(fn ($query) => $query->whereNotNull('resignation_date'))
                ->badge(\App\Models\Employee::whereNotNull('resignation_date')->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-user-minus'),
            'all' => \Filament\Resources\Components\Tab::make('All'),
        ];
    }
}
