<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_admin_panel()
    {
        $role = Role::create(['name' => 'Staff']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);
        
        $response = $this->get('/admin'); // Filament default path
        
        // Filament usually redirects to login or shows 403
        // If configured correctly with FilamentShield or Panel access
        $response->assertStatus(403); 
    }

    public function test_admin_can_access_admin_panel()
    {
        $role = Role::create(['name' => 'Super Admin']);
        $user = User::factory()->create();
        $user->assignRole($role);
        
        // Filament user check typically looks for canAccessPanel
        // We need to ensure User model implements FilamentUser interface if strict
        
        $this->actingAs($user);
        
        $response = $this->get('/admin');
        
        $response->assertStatus(200);
    }
}
