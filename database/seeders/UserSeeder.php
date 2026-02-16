<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Import Role model

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@skynet.com',
            'password' => Hash::make('password'),
            'device_fingerprint' => 'admin-device',
            'phone_number' => '+6281234567890',
        ]);
        $admin->assignRole('Super Admin');

        // 2. HR Manager
        $hr = User::create([
            'name' => 'Sarah HR',
            'email' => 'sarah@skynet.com',
            'password' => Hash::make('password'),
            'device_fingerprint' => 'hr-device',
            'phone_number' => '+6281234567891',
        ]);
        $hr->assignRole('HR Manager');

        // 3. Staff (Office)
        $staff = User::create([
            'name' => 'John Staff',
            'email' => 'john@skynet.com',
            'password' => Hash::make('password'),
            'device_fingerprint' => 'staff-device',
            'phone_number' => '+6281234567892',
        ]);
        $staff->assignRole('Staff');

        // 4. Technician
        $tech = User::create([
            'name' => 'Mike Tech',
            'email' => 'mike@skynet.com',
            'password' => Hash::make('password'),
            'device_fingerprint' => 'tech-device',
            'phone_number' => '+6281234567893',
        ]);
        $tech->assignRole('Technician');
    }
}
