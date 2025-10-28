<?php

use App\Models\User;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::findByName('administrator', 'api'));

    $this->user = User::factory()->create();
    $this->user->assignRole(Role::findByName('supervisor', 'api'));
});

test('admin can list all roles', function () {
    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson('/api/users/roles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => ['id', 'name', 'guard_name', 'permissions'],
        ])
        ->assertJsonCount(3); // administrator, manager, supervisor
});

test('admin can list all permissions', function () {
    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson('/api/users/permissions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => ['id', 'name', 'guard_name'],
        ]);
});

test('admin can assign role to user', function () {
    Passport::actingAs($this->admin, [], 'api');

    $newUser = User::factory()->create();
    $role = Role::findByName('manager', 'api');

    $response = $this->postJson("/api/users/manage/{$newUser->id}/assign-role", [
        'role' => 'manager',
    ]);

    $response->assertStatus(200);

    expect($newUser->fresh()->hasRole('manager', 'api'))->toBeTrue();
});

test('admin can remove role from user', function () {
    Passport::actingAs($this->admin, [], 'api');

    $testUser = User::factory()->create();
    $testUser->assignRole(Role::findByName('manager', 'api'));

    $response = $this->postJson("/api/users/manage/{$testUser->id}/remove-role", [
        'role' => 'manager',
    ]);

    $response->assertStatus(200);

    expect($testUser->fresh()->hasRole('manager', 'api'))->toBeFalse();
});

test('admin can sync user roles', function () {
    Passport::actingAs($this->admin, [], 'api');

    $testUser = User::factory()->create();
    $testUser->assignRole(Role::findByName('supervisor', 'api'));

    $response = $this->postJson("/api/users/manage/{$testUser->id}/sync-roles", [
        'roles' => ['manager'],
    ]);

    $response->assertStatus(200);

    $fresh = $testUser->fresh();
    expect($fresh->hasRole('manager', 'api'))->toBeTrue();
    expect($fresh->hasRole('supervisor', 'api'))->toBeFalse();
});

test('admin can give permission to user', function () {
    Passport::actingAs($this->admin, [], 'api');

    $testUser = User::factory()->create();
    $permission = Permission::findByName('view-locations', 'api');

    $response = $this->postJson("/api/users/manage/{$testUser->id}/give-permission", [
        'permission' => 'view-locations',
    ]);

    $response->assertStatus(200);

    expect($testUser->fresh()->hasPermissionTo('view-locations', 'api'))->toBeTrue();
});

test('admin can revoke permission from user', function () {
    Passport::actingAs($this->admin, [], 'api');

    $testUser = User::factory()->create();
    $testUser->givePermissionTo(Permission::findByName('view-locations', 'api'));

    $response = $this->postJson("/api/users/manage/{$testUser->id}/revoke-permission", [
        'permission' => 'view-locations',
    ]);

    $response->assertStatus(200);

    expect($testUser->fresh()->hasPermissionTo('view-locations', 'api'))->toBeFalse();
});

// Note: Sync permissions endpoint doesn't exist in current implementation
// Removing this test for now
// test('admin can sync user permissions', function () { ... });

test('non-admin cannot manage roles', function () {
    Passport::actingAs($this->user, [], 'api');

    $testUser = User::factory()->create();

    $response = $this->postJson("/api/users/manage/{$testUser->id}/assign-role", [
        'role' => 'manager',
    ]);

    $response->assertStatus(403);
});

test('non-admin cannot manage permissions', function () {
    Passport::actingAs($this->user, [], 'api');

    $testUser = User::factory()->create();

    $response = $this->postJson("/api/users/manage/{$testUser->id}/give-permission", [
        'permission' => 'view-locations',
    ]);

    $response->assertStatus(403);
});
