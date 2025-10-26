<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Location permissions
            'view-locations',
            'create-locations',
            'edit-locations',
            'delete-locations',

            // Employee permissions
            'view-employees',
            'create-employees',
            'edit-employees',
            'delete-employees',

            // Attendance permissions
            'view-attendances',
            'create-attendances',
            'edit-attendances',
            'delete-attendances',
            'check-in-attendance',
            'check-out-attendance',

            // User management permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Role & Permission management
            'view-roles',
            'assign-roles',
            'revoke-roles',
            'assign-permissions',
            'revoke-permissions',

            // Reports permissions
            'view-reports',
            'export-reports',
        ];

        $existentPermissions = Permission::whereIn('name', $permissions)->pluck('name');
        foreach ($permissions as $permission) {
            if ($existentPermissions->contains($permission)) {
                continue; // Skip if permission already exists
            }
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        }

        // Create roles and assign permissions

        // Administrator - Full access
        if (Role::where('name', 'administrator')->exists()) {
            $admin = Role::where('name', 'administrator')->first();
            $admin->syncPermissions(Permission::all());

            return;
        } else {
            $admin = Role::create(['name' => 'administrator', 'guard_name' => 'api']);
            $admin->givePermissionTo(Permission::all());
        }

        // Manager - Can manage locations, employees, and attendances
        if (Role::where('name', 'manager')->exists()) {
            $manager = Role::where('name', 'manager')->first();
        } else {
            $manager = Role::create(['name' => 'manager', 'guard_name' => 'api']);
        }
        $manager->givePermissionTo([
            'view-locations',
            'create-locations',
            'edit-locations',
            'delete-locations',
            'view-employees',
            'create-employees',
            'edit-employees',
            'delete-employees',
            'view-attendances',
            'create-attendances',
            'edit-attendances',
            'delete-attendances',
            'check-in-attendance',
            'check-out-attendance',
            'view-reports',
            'export-reports',
        ]);

        // Supervisor - Can view and manage attendances, limited employee access
        if (Role::where('name', 'supervisor')->exists()) {
            $supervisor = Role::where('name', 'supervisor')->first();
        } else {
            $supervisor = Role::create(['name' => 'supervisor', 'guard_name' => 'api']);
        }
        $supervisor->givePermissionTo([
            'view-locations',
            'view-employees',
            'view-attendances',
            'create-attendances',
            'edit-attendances',
            'check-in-attendance',
            'check-out-attendance',
            'view-reports',
        ]);
    }
}
