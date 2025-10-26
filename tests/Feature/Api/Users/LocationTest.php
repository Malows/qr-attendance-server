<?php

use App\Models\Location;
use App\Models\User;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::findByName('administrator', 'api'));

    $this->user = User::factory()->create();
    $this->user->assignRole(Role::findByName('supervisor', 'api'));
});

test('admin can list all locations', function () {
    Location::factory()->count(3)->create(['user_id' => $this->admin->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson('/api/users/locations');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => ['id', 'name', 'address', 'city', 'latitude', 'longitude', 'is_active'],
        ])
        ->assertJsonCount(3);
});

test('admin can create a location', function () {
    $locationData = [
        'name' => 'Main Office',
        'address' => '123 Main St',
        'city' => 'New York',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'description' => 'Main office location',
        'is_active' => true,
    ];

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->postJson('/api/users/locations', $locationData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'Main Office',
            'city' => 'New York',
        ]);

    $this->assertDatabaseHas('locations', [
        'name' => 'Main Office',
        'user_id' => $this->admin->id,
    ]);
});

test('location requires name', function () {
    Passport::actingAs($this->admin, [], 'api');

    $response = $this->postJson('/api/users/locations', [
        'city' => 'New York',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('admin can view a specific location', function () {
    $location = Location::factory()->create(['user_id' => $this->admin->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson("/api/users/locations/{$location->id}");

    $response->assertStatus(200)
        ->assertJson([
            'id' => $location->id,
            'name' => $location->name,
        ]);
});

test('admin can update a location', function () {
    $location = Location::factory()->create(['user_id' => $this->admin->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->putJson("/api/users/locations/{$location->id}", [
        'name' => 'Updated Office',
        'address' => $location->address,
        'city' => $location->city,
        'latitude' => $location->latitude,
        'longitude' => $location->longitude,
        'is_active' => false,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Updated Office',
            'is_active' => false,
        ]);

    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'name' => 'Updated Office',
    ]);
});

test('admin can soft delete a location', function () {
    $location = Location::factory()->create(['user_id' => $this->admin->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->deleteJson("/api/users/locations/{$location->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('locations', [
        'id' => $location->id,
    ]);
});

test('unauthorized user cannot create location', function () {
    Passport::actingAs($this->user, [], 'api');

    $response = $this->postJson('/api/users/locations', [
        'name' => 'Test Location',
        'address' => '123 Test St',
        'city' => 'Test City',
        'latitude' => 0,
        'longitude' => 0,
    ]);

    $response->assertStatus(403);
});
