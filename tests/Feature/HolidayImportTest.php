<?php

namespace Tests\Feature;

use App\Services\HolidayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HolidayImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_and_store_holidays()
    {
        // Mock the API response (guangrei/APIHariLibur_V2 format)
        Http::fake([
            'raw.githubusercontent.com/*' => Http::response([
                '2026-01-01' => [
                    'holiday' => true,
                    'summary' => ['Tahun Baru 2026 Masehi']
                ],
                '2026-02-16' => [
                    'holiday' => true,
                    'summary' => ['Cuti Bersama Tahun Baru Imlek']
                ]
            ], 200),
        ]);

        $service = new HolidayService();
        $count = $service->fetchAndStoreHolidays(2026);

        // Assertions
        $this->assertEquals(2, $count);
        
        $this->assertDatabaseHas('holidays', [
            'date' => '2026-01-01 00:00:00',
            'name' => 'Tahun Baru 2026 Masehi',
            'year' => 2026,
            'type' => 'national_holiday',
        ]);

        $this->assertDatabaseHas('holidays', [
            'date' => '2026-02-16 00:00:00',
            'name' => 'Cuti Bersama Tahun Baru Imlek',
            'year' => 2026,
            'type' => 'cuti_bersama',
        ]);
    }

    public function test_handles_api_failure()
    {
        Http::fake([
            'raw.githubusercontent.com/*' => Http::response(null, 500),
        ]);

        $this->expectException(\Exception::class);

        $service = new HolidayService();
        $service->fetchAndStoreHolidays(2026);
    }
}
