<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AttendanceAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Analytics');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Attendance Management');
    }

    public function getTitle(): string
    {
        return __('Attendance Analytics');
    }

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

    public function getBreadcrumbs(): array
    {
        return [
            \App\Filament\Resources\AttendanceResource::getUrl() => __('Attendances'),
            $this->getUrl() => $this->getTitle(),
        ];
    }
}
