<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RealTimeChart extends ChartWidget
{
    protected static ?string $heading = 'Real-Time Attendance Status';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $today = now()->toDateString();
        
        // Count statuses for today
        $attendance = \App\Models\Attendance::whereDate('date', $today)->get();
        
        $present = $attendance->count();
        $late = $attendance->where('is_late', true)->count();
        $onTime = $attendance->where('is_late', false)->count();
        
        // "Absent" is tricky without scheduling logic (assuming total employees - present)
        // For now, let's just show Present Breakdown: On Time vs Late
        // Or if we have Total Employees, we can calculate Absent.
        $totalEmployees = \App\Models\Employee::count();
        $absent = $totalEmployees - $present;

        return [
            'datasets' => [
                [
                    'label' => 'Status',
                    'data' => [$onTime, $late, $absent], 
                    'backgroundColor' => ['#4ade80', '#f87171', '#9ca3af'], // Green, Red, Gray
                ],
            ],
            'labels' => ['On Time', 'Late', 'Absent'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
