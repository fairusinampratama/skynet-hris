<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSchedules;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageSchedulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ManageSchedules::class)
            ->assertSuccessful();
    }

    public function test_can_toggle_first_day_of_every_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $department = Department::create(['name' => 'IT', 'has_shift_schedule' => true]);
        $employee = Employee::factory()->create(['user_id' => $user->id, 'department_id' => $department->id]);

        $years = [2026];
        $months = range(1, 12);

        foreach ($years as $year) {
            foreach ($months as $month) {
                // Testing the 1st day of the month
                $day = 1;
                $dateString = Carbon::create($year, $month, $day)->format('Y-m-d');

                // Set component to this month/year first (simulate user navigation)
                $component = Livewire::test(ManageSchedules::class)
                    ->set('year', $year)
                    ->set('month', $month);

                // 1. Toggle OFF
                $component->call('toggleDay', $employee->id, $dateString);

                $schedule = Schedule::where('employee_id', $employee->id)
                    ->where('date', $dateString)
                    ->first();
                
                $this->assertNotNull($schedule, "Failed to create schedule for $dateString");
                
                // Verify View Data (Map)
                $viewData = $component->get('employeesAndSchedules');
                $map = $viewData['map'];
                $this->assertArrayHasKey($employee->id, $map, "Employee ID missing from map");
                $this->assertArrayHasKey($day, $map[$employee->id], "Day $day missing from schedule map");
                $this->assertTrue((bool)$map[$employee->id][$day]['is_off'], "View data should reflect OFF status");

                // 2. Toggle ON (Custom)
                $component->call('toggleDay', $employee->id, $dateString);
                $schedule->refresh();
                $this->assertFalse((bool)$schedule->is_off, "Schedule should be ON (Custom) for $dateString");
                
                // Verify View Data again
                $viewData = $component->get('employeesAndSchedules');
                $map = $viewData['map'];
                $this->assertFalse((bool)$map[$employee->id][$day]['is_off'], "View data should reflect ON status");

                // 3. Delete (Reset)
                $component->call('toggleDay', $employee->id, $dateString);
                
                // Verify View Data (should be empty for this day)
                $viewData = $component->get('employeesAndSchedules');
                $map = $viewData['map'];
                $this->assertArrayNotHasKey($day, $map[$employee->id] ?? [], "Day $day should be removed from map");
            }
        }
    }
}
