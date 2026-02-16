<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\CompanySetting::firstOrCreate([
            'id' => 1
        ], [
            'office_name' => 'PT. Skynet Lintas Nusantara',
            'office_address' => 'Gg VIII No.01, RT.03/RW.07, Gondang, Randuangung, Kec. Singosari, Kabupaten Malang, Jawa Timur 65153, Indonesia',
            'office_lat' => -7.863503,
            'office_long' => 112.681320,
            'radius_meters' => 100,
        ]);
    }
}
