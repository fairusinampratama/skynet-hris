<?php

namespace App\Services;

use App\Models\Holiday;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    public function fetchAndStoreHolidays(int $year): int
    {
        // Use guangrei/APIHariLibur_V2 (GitHub Raw) - explicitly labels "Cuti Bersama"
        $url = "https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/calendar.min.json";
        $response = Http::get($url);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch from API: ' . $response->status());
        }

        $allHolidays = $response->json();
        $count = 0;

        foreach ($allHolidays as $date => $details) {
            // Filter by year
            if (substr($date, 0, 4) != $year) {
                continue;
            }

            // Skip if not a holiday
            if (!($details['holiday'] ?? false)) {
                continue;
            }

            $name = $details['summary'][0] ?? 'Unknown Holiday';
            
            // Determine Type
            $type = 'national_holiday';
            if (str_contains(strtolower($name), 'cuti bersama')) {
                $type = 'cuti_bersama';
            }

            Holiday::updateOrCreate(
                ['date' => $date],
                [
                    'name' => $name, 
                    'year' => $year,
                    'type' => $type
                ]
            );
            $count++;
        }

        return $count;
    }
}
