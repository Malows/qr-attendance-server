<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;

beforeEach(function () {
    $this->routes = [
        'login' => '/api/employees/auth/login',
        'me' => '/api/employees/auth/me',
        'logout' => '/api/employees/auth/logout',
        'refresh' => '/api/employees/auth/refresh',
    ];

    $this->user = User::factory()->create();

    // Create a personal access client for employees
    $this->artisan('passport:client', [
        '--personal' => true,
        '--name' => 'Employees Personal Access Client',
        '--provider' => 'employees',
        '--no-interaction' => true,
    ]);
});

test('employee can login with email and password', function () {
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'email' => 'employee@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'employee@example.com', // username puede ser email o employee_code
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'employee' => ['id', 'first_name', 'last_name', 'email', 'employee_code'],
            'access_token',
            'refresh_token',
            'token_type',
        ]);
});

test('employee cannot login with wrong password', function () {
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'email' => 'employee@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'employee@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('inactive employee cannot login', function () {
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'email' => 'employee@example.com',
        'password' => Hash::make('password123'),
        'is_active' => false,
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'employee@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('employee can login with employee code', function () {
    Employee::factory()->create([
        'user_id' => $this->user->id,
        'employee_code' => 'EMP001',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'EMP001',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'employee' => ['id', 'first_name', 'last_name', 'email', 'employee_code'],
            'access_token',
            'refresh_token',
            'token_type',
        ]);
});

test('employee cannot login with invalid code', function () {
    Employee::factory()->create([
        'user_id' => $this->user->id,
        'employee_code' => 'EMP001',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'INVALID',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('authenticated employee can get their profile', function () {
    /** @var \App\Models\Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => Hash::make('password123'),
    ]);

    Passport::actingAs($employee, [], 'employee');

    $response = $this->getJson($this->routes['me']);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $employee->id,
                'email' => $employee->email,
            ],
        ]);
});
test('employee can logout', function () {
    /** @var \App\Models\Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => Hash::make('password123'),
    ]);

    Passport::actingAs($employee, [], 'employee');

    $response = $this->postJson($this->routes['logout']);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('messages.logout.success'),
        ]);
});

test('unauthenticated employee cannot access protected routes', function () {
    $response = $this->getJson($this->routes['me']);

    $response->assertStatus(401);
});

test('employee can refresh token', function () {
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => Hash::make('password123'),
    ]);

    /** @var \App\Models\Employee $employee */
    Passport::actingAs($employee, [], 'employee');

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

test('employee refresh token requires authentication', function () {
    $response = $this->postJson($this->routes['refresh'], [
        'refresh_token' => 'dummy-refresh-token',
    ]);

    $response->assertStatus(401);
});

// Password Update Tests
test('employee can update password with current password', function () {
    /** @var \App\Models\Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => Hash::make('oldpassword123'),
        'force_password_change' => false,
    ]);

    Passport::actingAs($employee, [], 'employee');

    $response = $this->postJson('/api/employees/auth/update-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'newpassword456',
        'new_password_confirmation' => 'newpassword456',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Contraseña actualizada correctamente.',
        ]);

    // Verify password was changed and force_password_change is false
    $fresh = $employee->fresh();
    expect(Hash::check('newpassword456', $fresh->password))->toBeTrue();
    expect($fresh->force_password_change)->toBeFalse();
});

test('employee can update password when force_password_change is true without current password', function () {
    /** @var \App\Models\Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => null,
        'force_password_change' => true,
    ]);

    Passport::actingAs($employee, [], 'employee');

    $response = $this->postJson('/api/employees/auth/update-password', [
        'new_password' => 'newpassword456',
        'new_password_confirmation' => 'newpassword456',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Contraseña actualizada correctamente.',
        ]);

    // Verify password was set and force_password_change is now false
    $fresh = $employee->fresh();
    expect(Hash::check('newpassword456', $fresh->password))->toBeTrue();
    expect($fresh->force_password_change)->toBeFalse();
});

test('employee cannot update password with incorrect current password', function () {
    /** @var \App\Models\Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => Hash::make('oldpassword123'),
        'force_password_change' => false,
    ]);

    Passport::actingAs($employee, [], 'employee');

    $response = $this->postJson('/api/employees/auth/update-password', [
        'current_password' => 'wrongpassword',
        'new_password' => 'newpassword456',
        'new_password_confirmation' => 'newpassword456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

test('employee must provide current password when force_password_change is false', function () {
    /** @var \App\Models\Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => Hash::make('oldpassword123'),
        'force_password_change' => false,
    ]);

    Passport::actingAs($employee, [], 'employee');

    $response = $this->postJson('/api/employees/auth/update-password', [
        'new_password' => 'newpassword456',
        'new_password_confirmation' => 'newpassword456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

test('employee new password must be confirmed', function () {
    /** @var \App\Models\Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => Hash::make('oldpassword123'),
        'force_password_change' => false,
    ]);

    Passport::actingAs($employee, [], 'employee');

    $response = $this->postJson('/api/employees/auth/update-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'newpassword456',
        'new_password_confirmation' => 'differentpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['new_password']);
});

test('employee new password must meet minimum length requirement', function () {
    /** @var \App\Models\Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => Hash::make('oldpassword123'),
        'force_password_change' => false,
    ]);

    Passport::actingAs($employee, [], 'employee');

    $response = $this->postJson('/api/employees/auth/update-password', [
        'current_password' => 'oldpassword123',
        'new_password' => '123',
        'new_password_confirmation' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['new_password']);
});

test('unauthenticated employee cannot update password', function () {
    $response = $this->postJson('/api/employees/auth/update-password', [
        'current_password' => 'oldpassword123',
        'new_password' => 'newpassword456',
        'new_password_confirmation' => 'newpassword456',
    ]);

    $response->assertStatus(401);
});

test('employee can login without password when force_password_change is true', function () {
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'email' => 'employee@example.com',
        'password' => null,
        'force_password_change' => true,
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'employee@example.com',
        // No password provided
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'employee' => ['id', 'first_name', 'last_name', 'email', 'employee_code'],
            'access_token',
            'refresh_token',
            'token_type',
            'force_password_change',
        ])
        ->assertJson([
            'force_password_change' => true,
        ]);
});

test('employee cannot login with password when force_password_change is true and password is null', function () {
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'email' => 'employee@example.com',
        'password' => null,
        'force_password_change' => true,
    ]);

    $response = $this->postJson($this->routes['login'], [
        'username' => 'employee@example.com',
        'password' => 'anypassword', // Password provided but employee has force_password_change
    ]);

    $response->assertStatus(200) // Should still allow login
        ->assertJson([
            'force_password_change' => true,
        ]);
});

test('employee login returns force_password_change flag in response', function () {
    $employee1 = Employee::factory()->create([
        'user_id' => $this->user->id,
        'email' => 'employee1@example.com',
        'password' => Hash::make('password123'),
        'force_password_change' => false,
    ]);

    $employee2 = Employee::factory()->create([
        'user_id' => $this->user->id,
        'email' => 'employee2@example.com',
        'password' => null,
        'force_password_change' => true,
    ]);

    // Test normal employee (force_password_change: false)
    $response1 = $this->postJson($this->routes['login'], [
        'username' => 'employee1@example.com',
        'password' => 'password123',
    ]);

    $response1->assertStatus(200)
        ->assertJson([
            'force_password_change' => false,
        ]);

    // Test employee with force_password_change: true
    $response2 = $this->postJson($this->routes['login'], [
        'username' => 'employee2@example.com',
    ]);

    $response2->assertStatus(200)
        ->assertJson([
            'force_password_change' => true,
        ]);
});
