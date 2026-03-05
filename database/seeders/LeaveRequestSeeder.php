<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        // Static Types to match Filament Options
        $pribadiId = 'Izin Keperluan Pribadi';
        $sickLeaveId = 'Izin Sakit';
        $telatId = 'Izin Telat'; 

        // Get staff users (excluding admins)
        $users = User::role('Staff')->get();

        $reasons = [
            'pribadi' => [
                'Acara keluarga', 'Urusan pribadi mendesak',
                'Menghadiri pernikahan', 'Renovasi rumah', 'Pulang kampung',
                'Perpanjang SIM', 'Urusan administrasi',
            ],
            'sick' => [
                'Demam dan flu', 'Sakit gigi', 'Kontrol ke dokter',
                'Sakit perut', 'Migrain berat', 'Cedera ringan',
                'Alergi parah', 'Cek laboratorium',
            ],
            'telat' => [
                'Ban bocor di jalan', 'Macet parah karena kecelakaan',
                'Motor mogok', 'Hujan badai',
            ],
        ];

        foreach ($users as $user) {
            // 2-4 leave requests per employee
            $numRequests = $faker->numberBetween(2, 4);

            for ($i = 0; $i < $numRequests; $i++) {
                // Random leave type weighted: 40% pribadi, 40% sick, 20% telat
                $roll = $faker->numberBetween(1, 100);
                if ($roll <= 40) {
                    $typeId = $pribadiId;
                    $typeKey = 'pribadi';
                } elseif ($roll <= 80) {
                    $typeId = $sickLeaveId;
                    $typeKey = 'sick';
                } else {
                    $typeId = $telatId;
                    $typeKey = 'telat';
                }

                if (!$typeId) continue;

                $startDate = $faker->dateTimeBetween('2026-01-05', '2026-06-30');
                $duration = $faker->numberBetween(1, 3); // 1-3 days
                $start = Carbon::instance($startDate);
                $end = $start->copy()->addDays($duration - 1);

                // Status: 60% approved, 25% pending, 15% rejected
                $statusRoll = $faker->numberBetween(1, 100);
                if ($statusRoll <= 60) {
                    $status = 'approved';
                } elseif ($statusRoll <= 85) {
                    $status = 'pending';
                } else {
                    $status = 'rejected';
                }

                LeaveRequest::create([
                    'user_id' => $user->id,
                    'type' => $typeId,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'reason' => $faker->randomElement($reasons[$typeKey]),
                    'attachment_path' => $typeKey === 'sick' ? 'attachments/surat-dokter-' . $faker->numberBetween(1, 100) . '.pdf' : null,
                    'status' => $status,
                    'rejection_reason' => $status === 'rejected' ? $faker->randomElement(['Banyak pekerjaan', 'Alasan kurang kuat', 'Periode sibuk']) : null,
                    'created_at' => $start->copy()->subDays($faker->numberBetween(1, 7)),
                    'updated_at' => $start->copy()->subDays($faker->numberBetween(0, 3)),
                ]);
            }
        }
    }
}
