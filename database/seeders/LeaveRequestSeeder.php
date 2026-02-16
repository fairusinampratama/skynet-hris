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
        $leaveTypes = DB::table('leave_types')->get();
        $annualLeaveId = $leaveTypes->firstWhere('name', 'Annual Leave')?->id;
        $sickLeaveId = $leaveTypes->firstWhere('name', 'Sick Leave')?->id;
        $unpaidLeaveId = $leaveTypes->firstWhere('name', 'Unpaid Leave')?->id;

        // Get staff users (excluding admins)
        $users = User::role('Staff')->get();

        $reasons = [
            'annual' => [
                'Acara keluarga', 'Liburan keluarga', 'Urusan pribadi',
                'Menghadiri pernikahan', 'Renovasi rumah', 'Pulang kampung',
                'Perpanjang SIM', 'Urusan administrasi', 'Rekreasi',
            ],
            'sick' => [
                'Demam dan flu', 'Sakit gigi', 'Kontrol ke dokter',
                'Sakit perut', 'Migrain berat', 'Cedera ringan',
                'Alergi parah', 'Cek laboratorium',
            ],
            'unpaid' => [
                'Urusan mendesak keluarga', 'Keperluan darurat',
                'Masalah pribadi yang harus diselesaikan',
            ],
        ];

        foreach ($users as $user) {
            // 2-4 leave requests per employee
            $numRequests = $faker->numberBetween(2, 4);

            for ($i = 0; $i < $numRequests; $i++) {
                // Random leave type weighted: 60% annual, 30% sick, 10% unpaid
                $roll = $faker->numberBetween(1, 100);
                if ($roll <= 60) {
                    $typeId = $annualLeaveId;
                    $typeKey = 'annual';
                } elseif ($roll <= 90) {
                    $typeId = $sickLeaveId;
                    $typeKey = 'sick';
                } else {
                    $typeId = $unpaidLeaveId;
                    $typeKey = 'unpaid';
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
                    'leave_type_id' => $typeId,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'reason' => $faker->randomElement($reasons[$typeKey]),
                    'attachment_path' => $typeKey === 'sick' ? 'attachments/surat-dokter-' . $faker->numberBetween(1, 100) . '.pdf' : null,
                    'status' => $status,
                    'rejection_reason' => $status === 'rejected' ? $faker->randomElement(['Jadwal bentrok dengan proyek', 'Kuota cuti habis', 'Periode sibuk']) : null,
                    'created_at' => $start->copy()->subDays($faker->numberBetween(1, 7)),
                    'updated_at' => $start->copy()->subDays($faker->numberBetween(0, 3)),
                ]);

                // Deduct leave balance for approved annual leave
                if ($status === 'approved' && $typeKey === 'annual') {
                    $balance = LeaveBalance::where('user_id', $user->id)
                        ->where('leave_type_id', $annualLeaveId)
                        ->first();
                    if ($balance && $balance->remaining_days > 0) {
                        $balance->decrement('remaining_days', min($duration, $balance->remaining_days));
                    }
                }
            }
        }
    }
}
