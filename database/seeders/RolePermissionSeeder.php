<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $staff = Role::firstOrCreate(['name' => 'Staff']);

        // Create Permissions (Basic examples)
        Permission::firstOrCreate(['name' => 'view_all_attendance']);
        Permission::firstOrCreate(['name' => 'manage_payroll']);
        
        // Assign Permissions
        $admin->givePermissionTo(['view_all_attendance', 'manage_payroll']);
    }
}
