<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Present Today', \App\Models\Attendance::whereDate('date', now())->count())
                ->description('Employees checked in')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            
            Stat::make('Late Today', \App\Models\Attendance::whereDate('date', now())->where('is_late', true)->count())
                ->description('Checked in after shift start')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('On Leave', \App\Models\LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->count())
                ->description('Approved leaves active today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Pending Requests', 
                \App\Models\LeaveRequest::where('status', 'pending')->count() + 
                \App\Models\OvertimeRequest::where('status', 'pending')->count()
            )
                ->description('Leave & Overtime needing approval')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('warning'),
        ];
    }
}
