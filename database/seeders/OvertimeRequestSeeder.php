<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Carbon\Carbon;

class OvertimeRequestSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $employees = Employee::all();

        $reasons = [
            'Maintenance server', 'Deadline proyek', 'Issue pelanggan mendesak',
            'Instalasi perangkat baru', 'Backup data bulanan', 'Monitoring jaringan malam',
            'Troubleshoot jaringan pelanggan', 'Migrasi sistem', 'Perbaikan link fiber',
            'Laporan akhir bulan', 'Persiapan audit', 'Update sistem keamanan',
        ];

        foreach ($employees as $emp) {
            // 1-3 overtime requests per employee
            $numRequests = $faker->numberBetween(1, 3);

            for ($i = 0; $i < $numRequests; $i++) {
                $date = $faker->dateTimeBetween('2026-01-05', '2026-06-30');

                // Status: 70% approved, 20% pending, 10% rejected
                $statusRoll = $faker->numberBetween(1, 100);
                $status = $statusRoll <= 70 ? 'approved' : ($statusRoll <= 90 ? 'pending' : 'rejected');

                OvertimeRequest::create([
                    'employee_id' => $emp->id,
                    'date' => Carbon::instance($date)->toDateString(),
                    'hours' => $faker->randomFloat(1, 1, 4), // 1.0 - 4.0 hours
                    'reason' => $faker->randomElement($reasons),
                    'status' => $status,
                    'created_at' => Carbon::instance($date)->subDays($faker->numberBetween(0, 3)),
                    'updated_at' => Carbon::instance($date),
                ]);
            }
        }
    }
}
