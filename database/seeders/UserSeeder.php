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
        // 1. Admin
        $admin = User::firstOrCreate([
            'email' => 'admin@skynet.com',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'device_fingerprint' => 'admin-device',
            'phone_number' => '+6281234567890',
        ]);
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        // 2. Staff Users
        $staff1 = User::firstOrCreate([
            'email' => 'sarah@skynet.com',
        ], [
            'name' => 'Sarah',
            'password' => Hash::make('password'),
            'device_fingerprint' => 'staff1-device',
            'phone_number' => '+6281234567892',
        ]);
        if (!$staff1->hasRole('Staff')) {
            $staff1->assignRole('Staff');
        }

        $staff2 = User::firstOrCreate([
            'email' => 'john@skynet.com',
        ], [
            'name' => 'John',
            'password' => Hash::make('password'),
            'device_fingerprint' => 'staff2-device',
            'phone_number' => '+6281234567893',
        ]);
        if (!$staff2->hasRole('Staff')) {
            $staff2->assignRole('Staff');
        }
    }
}
