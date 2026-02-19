<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Admin'      => ['min_salary' => 5000000,  'max_salary' => 8000000],
            'Teknisi'    => ['min_salary' => 5000000,  'max_salary' => 10000000],
            'NOC'        => ['min_salary' => 5000000,  'max_salary' => 10000000],
            'Programmer' => ['min_salary' => 8000000,  'max_salary' => 15000000],
            'Planning'   => ['min_salary' => 5000000,  'max_salary' => 8000000],
        ];

        $deptIds = DB::table('departments')->whereIn('name', array_keys($departments))->pluck('id', 'name');
        $faker = \Faker\Factory::create('id_ID');

        // Static Users to link as Employees
        $staticEmployees = [
            'sarah@skynet.com' => ['dept' => 'Admin', 'salary' => 7500000],
            'john@skynet.com'  => ['dept' => 'Admin', 'salary' => 5500000],
            'mike@skynet.com'  => ['dept' => 'Teknisi', 'salary' => 6000000],
        ];

        foreach ($staticEmployees as $email => $details) {
            $user = User::where('email', $email)->first();
            if ($user) {
                DB::table('employees')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'department_id' => $deptIds[$details['dept']] ?? null,
                        'join_date' => '2023-01-01', // Fixed join date for static users
                        'basic_salary' => $details['salary'],
                        'face_descriptor' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        foreach ($departments as $deptName => $salaryRange) {
            for ($i = 1; $i <= 10; $i++) {
                $uniqueId = strtolower($deptName) . $i;
                $email = "{$uniqueId}@skynet.com";
                $name = $faker->name();

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Hash::make('password'),
                        'device_fingerprint' => "device-{$uniqueId}",
                        'phone_number' => $faker->phoneNumber(),
                    ]
                );

                if (!$user->hasRole('Staff')) {
                    $user->assignRole('Staff');
                }

                DB::table('employees')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'department_id' => $deptIds[$deptName],
                        'join_date' => $faker->dateTimeBetween('-3 years', '-1 month')->format('Y-m-d'),
                        'basic_salary' => $faker->numberBetween($salaryRange['min_salary'], $salaryRange['max_salary']),
                        'face_descriptor' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
