<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use Carbon\Carbon;

use Livewire\Attributes\Computed;

class AttendanceCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('Attendance Calendar');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Attendance Management');
    }

    public function getTitle(): string
    {
        return __('Attendance Calendar');
    }

    protected static string $view = 'filament.pages.attendance-calendar';

    public $month;
    public $year;
    public $departmentFilter = '';

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    #[Computed]
    public function days()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay();
        $daysInMonth = $date->daysInMonth;

        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = $date->copy()->day($i);
        }
        return $days;
    }

    #[Computed]
    public function calendarData()
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        // Get employees, optionally filtered by department
        $query = Employee::with(['department', 'user'])->orderBy('department_id');
        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }
        $employees = $query->get();

        // Get all attendance records for the month
        $attendances = Attendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        // Map: user_id -> day (int) -> attendance record
        $attendanceMap = [];
        foreach ($attendances as $a) {
            $day = Carbon::parse($a->date)->day;
            $attendanceMap[$a->user_id][$day] = $a;
        }

        // Fetch Holidays
        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();
        $holidayMap = [];
        foreach ($holidays as $h) {
            $day = Carbon::parse($h->date)->day;
            $holidayMap[$day] = $h;
        }

        // Fetch Approved Leave Requests
        $leaveRequests = LeaveRequest::where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start->toDateString())
                         ->where('end_date', '>=', $end->toDateString());
                  });
            })
            ->get();

        // Map: user_id -> day (int) -> leave request record
        $leaveMap = [];
        foreach ($leaveRequests as $lr) {
            $lrStart = Carbon::parse($lr->start_date);
            $lrEnd = Carbon::parse($lr->end_date);
            
            // Loop through each day of the leave request
            for ($d = $lrStart->copy(); $d->lte($lrEnd); $d->addDay()) {
                // Only map if the day falls within the current viewed month
                if ($d->month === (int)$this->month && $d->year === (int)$this->year) {
                    $leaveMap[$lr->user_id][$d->day] = $lr;
                }
            }
        }

        return [
            'employees' => $employees,
            'attendanceMap' => $attendanceMap,
            'holidays' => $holidayMap,
            'leaveMap' => $leaveMap,
        ];
    }

    #[Computed]
    public function departments()
    {
        return Department::orderBy('name')->get();
    }

    public function getBreadcrumbs(): array
    {
        return [
            \App\Filament\Resources\AttendanceResource::getUrl() => __('Attendances'),
            $this->getUrl() => $this->getTitle(),
        ];
    }
}
