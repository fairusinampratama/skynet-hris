<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AttendanceAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?string $navigationGroup = 'Attendance Management';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Attendance Analytics';

    protected static string $view = 'filament.pages.attendance-analytics';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\RealTimeChart::class,
            \App\Filament\Widgets\AttendanceTrendChart::class,
            \App\Filament\Widgets\DepartmentPunctualityChart::class,
            \App\Filament\Widgets\LateArrivalsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
