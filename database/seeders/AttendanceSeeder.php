<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\CompanySetting;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $settings = CompanySetting::first();
        $faker = \Faker\Factory::create('id_ID');

        $baseLat = $settings ? $settings->office_lat : -7.863503;
        $baseLong = $settings ? $settings->office_long : 112.681320;

        // Date range: Jan 1 to today
        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::now()->startOfDay();

        // Pre-load holidays as a set of date strings
        $holidayDates = Holiday::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        // Assign personality profiles to employees
        $profiles = [];
        foreach ($employees as $emp) {
            $roll = $faker->numberBetween(1, 100);
            if ($roll <= 70) {
                $profiles[$emp->id] = 'punctual';     // 70%
            } elseif ($roll <= 90) {
                $profiles[$emp->id] = 'average';       // 20%
            } else {
                $profiles[$emp->id] = 'problematic';   // 10%
            }
        }

        $records = [];
        $batchSize = 500;

        foreach ($employees as $employee) {
            $profile = $profiles[$employee->id];
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $dateString = $currentDate->toDateString();

                // Skip Sundays
                if ($currentDate->isSunday()) {
                    $currentDate->addDay();
                    continue;
                }

                // Skip holidays
                if (in_array($dateString, $holidayDates)) {
                    $currentDate->addDay();
                    continue;
                }

                // Attendance probability based on profile
                $attendChance = match ($profile) {
                    'punctual' => 95,
                    'average' => 88,
                    'problematic' => 78,
                };

                if ($faker->boolean($attendChance)) {
                    // Generate check-in time based on profile
                    switch ($profile) {
                        case 'punctual':
                            // 07:30 - 08:45 (rarely late)
                            $hour = $faker->boolean(90) ? 7 : 8;
                            $minute = $hour === 7 ? $faker->numberBetween(30, 59) : $faker->numberBetween(0, 45);
                            break;
                        case 'average':
                            // 07:45 - 09:15 (sometimes late)
                            $hour = $faker->randomElement([7, 8, 8, 8, 9]);
                            $minute = $faker->numberBetween(0, 59);
                            if ($hour === 7) $minute = $faker->numberBetween(45, 59);
                            if ($hour === 9) $minute = $faker->numberBetween(0, 15);
                            break;
                        case 'problematic':
                            // 08:30 - 09:45 (frequently late)
                            $hour = $faker->randomElement([8, 9, 9, 9]);
                            $minute = $faker->numberBetween(0, 59);
                            if ($hour === 8) $minute = $faker->numberBetween(30, 59);
                            if ($hour === 9) $minute = $faker->numberBetween(0, 45);
                            break;
                    }

                    $checkInTime = sprintf('%02d:%02d:00', $hour, $minute);
                    $isLate = $checkInTime > '09:00:00';

                    // Check out: 17:00 - 19:30
                    $coHour = $faker->numberBetween(17, 19);
                    $coMinute = $faker->numberBetween(0, 59);
                    if ($coHour === 19) $coMinute = $faker->numberBetween(0, 30);
                    $checkOutTime = sprintf('%02d:%02d:00', $coHour, $coMinute);

                    // GPS jitter
                    $latOffset = $faker->randomFloat(6, -0.0004, 0.0004);
                    $longOffset = $faker->randomFloat(6, -0.0004, 0.0004);

                    $records[] = [
                        'user_id' => $employee->user_id,
                        'date' => $dateString,
                        'check_in_time' => $checkInTime,
                        'check_out_time' => $checkOutTime,
                        'check_in_lat' => $baseLat + $latOffset,
                        'check_in_long' => $baseLong + $longOffset,
                        'check_in_accuracy' => $faker->numberBetween(5, 50),
                        'check_in_photo_path' => null,
                        'device_fingerprint' => "device-sim-{$employee->id}",
                        'is_late' => $isLate,
                        'is_flagged' => false,
                        'work_summary' => $faker->sentence(4),
                        'created_at' => $dateString . ' ' . $checkInTime,
                        'updated_at' => $dateString . ' ' . $checkOutTime,
                    ];

                    // Batch insert for performance
                    if (count($records) >= $batchSize) {
                        Attendance::insert($records);
                        $records = [];
                    }
                }

                $currentDate->addDay();
            }
        }

        // Insert remaining records
        if (count($records) > 0) {
            Attendance::insert($records);
        }
    }
}
