<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class DepartmentPunctualityChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('Department Punctuality');
    }

    protected function getData(): array
    {
        $departments = \App\Models\Department::all();
        $labels = [];
        $onTimeData = [];
        $lateData = [];

        foreach ($departments as $dept) {
            $labels[] = $dept->name;
            
            // Get attendance for this department's employees in last 30 days
            $attendance = \App\Models\Attendance::whereHas('user.employee', function ($q) use ($dept) {
                $q->where('department_id', $dept->id);
            })
            ->where('date', '>=', now()->subDays(30))
            ->get();
            
            $onTimeData[] = $attendance->where('is_late', false)->count();
            $lateData[] = $attendance->where('is_late', true)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('On Time (Last 30 Days)'),
                    'data' => $onTimeData,
                    'backgroundColor' => '#36A2EB',
                ],
                [
                    'label' => __('Late (Last 30 Days)'),
                    'data' => $lateData,
                    'backgroundColor' => '#FF6384',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
