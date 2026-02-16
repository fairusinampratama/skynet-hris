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
                'name' => 'Annual Leave',
                'quota' => 12,
                'requires_attachment' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sick Leave',
                'quota' => null, // Unlimited/As needed
                'requires_attachment' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unpaid Leave',
                'quota' => null,
                'requires_attachment' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
