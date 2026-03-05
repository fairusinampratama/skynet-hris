<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Filament\Notifications\Notification;

use Livewire\Attributes\Computed;

class ManageSchedules extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $slug = 'shift-schedule';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Shift Schedule');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Attendance Management');
    }

    public function getTitle(): string
    {
        return __('Shift Schedule');
    }

    protected static string $view = 'filament.pages.manage-schedules';
    
    // use \Livewire\Attributes\Computed; // Removed from here

    public $month;
    public $year;
    // public $daysInMonth; // Not needed as property anymore
    // public $days = []; // Removed

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
        // $this->updateDays();
    }

    public function updatedMonth() { } // $this->updateDays(); 
    public function updatedYear() { } // $this->updateDays();

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

    public function getEmployeesAndSchedulesProperty()
    {
        // Fetch employees ONLY from departments that have shift schedule enabled
        $employees = Employee::with('department')
            ->whereHas('department', function ($query) {
                $query->where('has_shift_schedule', true);
            })
            ->orderBy('department_id')
            ->get();
        
        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        
        $schedules = Schedule::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();
        
        // Map: employee_id -> day (int) -> verified status
        $scheduleMap = [];
        foreach ($schedules as $s) {
            $day = Carbon::parse($s->date)->day;
            $scheduleMap[$s->employee_id][$day] = $s;
        }

        // Fetch Holidays
        $holidays = \App\Models\Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();
        $holidayMap = [];
        foreach ($holidays as $h) {
            $day = Carbon::parse($h->date)->day;
            $holidayMap[$day] = $h; // Store the whole Holiday object
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
            'map' => $scheduleMap,
            'holidays' => $holidayMap,
            'leaveMap' => $leaveMap
        ];
    }

    public function toggleDay($employeeId, $dateString)
    {
        // $dateString is already Y-m-d
        \Illuminate\Support\Facades\Log::info("Toggling day for Emp: $employeeId, Date: $dateString");
        
        $employee = Employee::find($employeeId);
        
        // Safety Check: Do not allow toggling if there is an approved Leave Request
        if ($employee) {
            $hasLeave = LeaveRequest::where('user_id', $employee->user_id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $dateString)
                ->whereDate('end_date', '>=', $dateString)
                ->exists();
                
            if ($hasLeave) {
                Notification::make()
                    ->title(__('Cannot modify schedule'))
                    ->body(__('Employee is on approved leave (Izin) this day.'))
                    ->warning()
                    ->send();
                return;
            }
        }
        
        $schedule = Schedule::where('employee_id', $employeeId)
            ->where('date', $dateString)
            ->first();
            
        // Toggle Logic: Default -> Off -> Custom On -> Default
        
        if (!$schedule) {
            // No override exists. Create "OFF".
            \Illuminate\Support\Facades\Log::info("Creating OFF schedule");
            $newSchedule = Schedule::create([
                'employee_id' => $employeeId,
                'date' => $dateString,
                'is_off' => true,
            ]);
            \Illuminate\Support\Facades\Log::info("Created Result: " . json_encode($newSchedule));
        } elseif ($schedule->is_off) {
            // Was OFF. Switch to "Custom ON" (e.g. Work on Sunday).
            // For simplicity, is_off = false means "Working".
            // We could add start_time here later.
            \Illuminate\Support\Facades\Log::info("Updating to ON");
            $schedule->update(['is_off' => false]);
        } else {
            // Was Custom ON. Delete it (Return to Default).
            \Illuminate\Support\Facades\Log::info("Deleting schedule");
            $schedule->delete();
        }
        
        // Refresh? Livewire should handle re-render.
    }

    public function getBreadcrumbs(): array
    {
        return [
            \App\Filament\Resources\AttendanceResource::getUrl() => __('Attendances'),
            $this->getUrl() => $this->getTitle(),
        ];
    }
}
