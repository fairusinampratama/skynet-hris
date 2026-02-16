<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Employee;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Notifications\Notification;

use Livewire\Attributes\Computed;

class ManageSchedules extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Shift Schedule';
    protected static ?string $navigationGroup = 'Attendance Management'; // Group with Attendances
    protected static ?int $navigationSort = 2;

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

        return [
            'employees' => $employees,
            'map' => $scheduleMap,
            'holidays' => $holidayMap
        ];
    }

    public function toggleDay($employeeId, $dateString)
    {
        // $dateString is already Y-m-d
        \Illuminate\Support\Facades\Log::info("Toggling day for Emp: $employeeId, Date: $dateString");
        
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
}
