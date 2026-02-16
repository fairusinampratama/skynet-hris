<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class AttendanceTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Attendance Trends';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = [];
        $months = [];
        
        // Loop through 12 months of current year
        for ($m = 1; $m <= 12; $m++) {
            $monthName = date('M', mktime(0, 0, 0, $m, 1));
            $months[] = $monthName;
            
            // Count for this month
            $count = \App\Models\Attendance::whereYear('date', now()->year)
                ->whereMonth('date', $m)
                ->count();
            
            $lateCount = \App\Models\Attendance::whereYear('date', now()->year)
                ->whereMonth('date', $m)
                ->where('is_late', true)
                ->count();
                
            $data['total'][] = $count;
            $data['late'][] = $lateCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Attendance',
                    'data' => $data['total'],
                    'borderColor' => '#4ade80', // Green
                ],
                [
                    'label' => 'Late Arrivals',
                    'data' => $data['late'],
                    'borderColor' => '#f87171', // Red
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
