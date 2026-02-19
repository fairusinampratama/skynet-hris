<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class AttendanceTrendChart extends ChartWidget
{
    protected static ?int $sort = 1;

    public function getHeading(): string
    {
        return __('Monthly Attendance Trends');
    }

    protected function getData(): array
    {
        $data = [];
        $months = [];
        
        // Loop through 12 months of current year
        for ($m = 1; $m <= 12; $m++) {
            $monthName = \Carbon\Carbon::create(null, $m)->translatedFormat('M');
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
                    'label' => __('Total Attendance'),
                    'data' => $data['total'],
                    'borderColor' => '#4ade80',
                ],
                [
                    'label' => __('Late Arrivals'),
                    'data' => $data['late'],
                    'borderColor' => '#f87171',
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
