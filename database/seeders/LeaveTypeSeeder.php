<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('leave_types')->insert([
            [
                'name' => 'Cuti Tahunan',
                'quota' => 12,
                'requires_attachment' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuti Sakit',
                'quota' => null,
                'requires_attachment' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuti Tanpa Gaji',
                'quota' => null,
                'requires_attachment' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
