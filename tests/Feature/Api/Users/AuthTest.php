<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;

beforeEach(function () {
    $this->routes = [
        'login' => '/api/users/auth/login',
        'me' => '/api/users/auth/me',
        'logout' => '/api/users/auth/logout',
        'refresh' => '/api/users/auth/refresh',
        'register' => '/api/users/auth/register',
    ];

    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);

    // Create a personal access client for testing
    $this->artisan('passport:client', [
        '--personal' => true,
        '--name' => 'Test Personal Access Client',
        '--provider' => 'users',
        '--no-interaction' => true,
    ]);
});

test('user can register with valid data', function () {
    $response = $this->postJson($this->routes['register'], [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'access_token',
            'refresh_token',
            'token_type',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('user cannot register with invalid email', function () {
    $response = $this->postJson($this->routes['register'], [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user cannot register with duplicate email', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson($this->routes['register'], [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'email'],
            'access_token',
            'refresh_token',
            'token_type',
        ]);
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('authenticated user can get their profile', function () {
    $user = User::factory()->create();

    Passport::actingAs($user, [], 'api');

    $response = $this->getJson($this->routes['me']);

    $response->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $response = $this->getJson($this->routes['me']);

    $response->assertStatus(401);
});

test('user can logout', function () {
    $user = User::factory()->create();

    Passport::actingAs($user, [], 'api');

    $response = $this->postJson($this->routes['logout']);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out',
        ]);
});

test('user can refresh token', function () {
    $user = User::factory()->create();

    Passport::actingAs($user, [], 'api');

    $response = $this->postJson($this->routes['refresh'], [
        'refresh_token' => 'dummy-refresh-token',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'refresh_token',
            'token_type',
            'message',
        ])
        ->assertJson([
            'token_type' => 'Bearer',
            'message' => 'Token refreshed successfully',
        ]);
});

test('refresh token requires authentication', function () {
    $response = $this->postJson($this->routes['refresh'], [
        'refresh_token' => 'dummy-refresh-token',
    ]);

    $response->assertStatus(401);
});

test('login fails when username not found among user emails', function () {
    // Act: Try to login with non-existent email
    $response = $this->postJson($this->routes['login'], [
        'username' => 'nonexistent@example.com',
        'password' => 'somepassword',
    ]);

    // Assert: Validation error for incorrect credentials
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('user can login with force password change flag', function () {
    // Arrange: Create user with force_password_change = true and no password
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => null,
        'force_password_change' => true,
    ]);

    // Act: Try to login without password when force_password_change is true
    $response = $this->postJson($this->routes['login'], [
        'username' => 'user@example.com',
        'password' => '',
    ]);

    // Assert: Login is successful even without password
    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'access_token',
            'refresh_token',
            'token_type',
            'force_password_change',
        ])
        ->assertJson([
            'token_type' => 'Bearer',
            'force_password_change' => true,
        ]);

    // Assert: User data is correct
    $responseData = $response->json();
    expect($responseData['user']['id'])->toBe($user->id);
    expect($responseData['user']['email'])->toBe($user->email);
});

test('user can update password successfully', function () {
    // Arrange: Create user with force_password_change = true
    $user = User::factory()->create([
        'password' => null,
        'force_password_change' => true,
    ]);

    // Act: Update password
    $response = $this
        ->actingAs($user, 'api')
        ->postJson('/api/users/auth/update-password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

    // Assert: Response is successful
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Contraseña actualizada correctamente.',
        ]);

    // Assert: User password is updated and force_password_change is false
    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
    expect($user->force_password_change)->toBeFalse();
});

test('update password requires authentication', function () {
    // Act: Try to update password without authentication
    $response = $this->postJson('/api/users/auth/update-password', [
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    // Assert: Unauthorized response
    $response->assertStatus(401);
});

test('update password validates password confirmation', function () {
    // Arrange: Create authenticated user
    $user = User::factory()->create();

    // Act: Try to update password with mismatched confirmation
    $response = $this
        ->actingAs($user, 'api')
        ->postJson('/api/users/auth/update-password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword',
        ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['new_password']);
});
