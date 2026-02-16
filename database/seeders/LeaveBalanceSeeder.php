<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaveBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $annualLeave = DB::table('leave_types')->where('name', 'Annual Leave')->first();
        $users = User::all();

        foreach ($users as $user) {
            // Give everyone 12 days annual leave
            DB::table('leave_balances')->insert([
                'user_id' => $user->id,
                'leave_type_id' => $annualLeave->id,
                'remaining_days' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
