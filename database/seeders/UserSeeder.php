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
    }
}
