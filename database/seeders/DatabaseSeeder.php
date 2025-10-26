<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // if environment is production, do not create test users
        if (app()->environment('production')) {
            return;
        }
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole(Role::findByName('administrator', 'api'));

        // Create manager user
        $manager = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
        ]);
        $manager->assignRole(Role::findByName('manager', 'api'));

        // Create supervisor user
        $supervisor = User::factory()->create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
        ]);
        $supervisor->assignRole(Role::findByName('supervisor', 'api'));

        // Create a regular test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
