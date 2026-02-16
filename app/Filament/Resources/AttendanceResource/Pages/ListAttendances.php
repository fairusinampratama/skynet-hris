<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Filament\Pages\AttendanceAnalytics;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('analytics')
                ->label('Analytics')
                ->icon('heroicon-o-chart-bar')
                ->url(AttendanceAnalytics::getUrl()),
            Actions\CreateAction::make(),
        ];
    }
}
