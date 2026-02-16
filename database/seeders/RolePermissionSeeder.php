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
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $hrManager = Role::create(['name' => 'HR Manager']);
        $staff = Role::create(['name' => 'Staff']);
        $technician = Role::create(['name' => 'Technician']);

        // Create Permissions (Basic examples)
        Permission::create(['name' => 'view_all_attendance']);
        Permission::create(['name' => 'manage_payroll']);
        
        // Assign Permissions
        $hrManager->givePermissionTo(['view_all_attendance', 'manage_payroll']);
    }
}
