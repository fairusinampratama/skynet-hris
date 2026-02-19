<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Resources\Pages\Page;

class AttendanceAnalytics extends Page
{
    protected static string $resource = AttendanceResource::class;

    protected static string $view = 'filament.resources.attendance-resource.pages.attendance-analytics';

    public function getBreadcrumbs(): array
    {
        return [
            \App\Filament\Resources\AttendanceResource::getUrl() => 'Attendances',
            $this->getUrl() => $this->getTitle(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\RealTimeChart::class,
            \App\Filament\Widgets\AttendanceTrendChart::class,
            \App\Filament\Widgets\DepartmentPunctualityChart::class,
            \App\Filament\Widgets\LateArrivalsWidget::class,
        ];
    }
}
