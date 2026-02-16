<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['date' => '2026-01-01', 'name' => 'Tahun Baru Masehi',           'type' => 'national_holiday'],
            ['date' => '2026-01-27', 'name' => 'Isra Mi\'raj Nabi Muhammad',   'type' => 'national_holiday'],
            ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek',             'type' => 'national_holiday'],
            ['date' => '2026-03-20', 'name' => 'Hari Raya Nyepi',              'type' => 'national_holiday'],
            ['date' => '2026-03-21', 'name' => 'Cuti Bersama Nyepi',           'type' => 'cuti_bersama'],
            ['date' => '2026-03-31', 'name' => 'Hari Raya Idul Fitri',         'type' => 'national_holiday'],
            ['date' => '2026-04-01', 'name' => 'Hari Raya Idul Fitri',         'type' => 'national_holiday'],
            ['date' => '2026-04-02', 'name' => 'Cuti Bersama Idul Fitri',      'type' => 'cuti_bersama'],
            ['date' => '2026-04-03', 'name' => 'Wafat Isa Almasih',            'type' => 'national_holiday'],
            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional',     'type' => 'national_holiday'],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Isa Almasih',         'type' => 'national_holiday'],
            ['date' => '2026-05-16', 'name' => 'Hari Raya Waisak',             'type' => 'national_holiday'],
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila',         'type' => 'national_holiday'],
            ['date' => '2026-06-07', 'name' => 'Hari Raya Idul Adha',          'type' => 'national_holiday'],
            ['date' => '2026-06-27', 'name' => 'Tahun Baru Islam',             'type' => 'national_holiday'],
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI',          'type' => 'national_holiday'],
            ['date' => '2026-09-05', 'name' => 'Maulid Nabi Muhammad',         'type' => 'national_holiday'],
            ['date' => '2026-12-25', 'name' => 'Hari Natal',                   'type' => 'national_holiday'],
        ];

        foreach ($holidays as $h) {
            Holiday::updateOrCreate(
                ['date' => $h['date']],
                [
                    'year' => (int) substr($h['date'], 0, 4),
                    'name' => $h['name'],
                    'type' => $h['type'],
                ]
            );
        }
    }
}
