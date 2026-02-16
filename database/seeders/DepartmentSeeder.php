<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Admin',       'has_shift_schedule' => false],
            ['name' => 'Teknisi',     'has_shift_schedule' => true],
            ['name' => 'NOC',         'has_shift_schedule' => true],
            ['name' => 'Programmer',  'has_shift_schedule' => false],
            ['name' => 'Planning',    'has_shift_schedule' => false],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->updateOrInsert(
                ['name' => $dept['name']],
                [
                    'office_lat' => -7.863503,
                    'office_long' => 112.681320,
                    'radius_meters' => 100,
                    'has_shift_schedule' => $dept['has_shift_schedule'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
