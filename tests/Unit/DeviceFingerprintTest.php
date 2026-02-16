<?php

namespace Tests\Unit;

use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Tests\TestCase;

class DeviceFingerprintTest extends TestCase
{
    public function test_fingerprint_generation_is_consistent()
    {
        $service = new AttendanceService();
        
        $request = Request::create('/check-in', 'POST');
        $request->headers->set('User-Agent', 'TestAgent/1.0');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        
        $fingerprint1 = $service->generateDeviceFingerprint($request);
        $fingerprint2 = $service->generateDeviceFingerprint($request);
        
        $this->assertEquals($fingerprint1, $fingerprint2);
        $this->assertEquals(64, strlen($fingerprint1)); // SHA-256 is 64 chars hex
    }

    public function test_fingerprint_changes_with_user_agent()
    {
        $service = new AttendanceService();
        
        $request1 = Request::create('/check-in', 'POST');
        $request1->headers->set('User-Agent', 'Agent A');
        $request1->server->set('REMOTE_ADDR', '127.0.0.1');
        
        $request2 = Request::create('/check-in', 'POST');
        $request2->headers->set('User-Agent', 'Agent B');
        $request2->server->set('REMOTE_ADDR', '127.0.0.1');
        
        $this->assertNotEquals(
            $service->generateDeviceFingerprint($request1),
            $service->generateDeviceFingerprint($request2)
        );
    }
}
