<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::findByName('administrator', 'api'));
});

test('admin can list all employees', function () {
    Employee::factory()->count(5)->create(['user_id' => $this->admin->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson('/api/users/employees');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'first_name', 'last_name', 'email', 'employee_code', 'is_active'],
            ],
            'links',
            'meta',
        ]);
});

test('admin can create an employee', function () {
    $employeeData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '555-0123',
        'employee_code' => 'EMP001',
        'hire_date' => '2025-01-01',
        'position' => 'Developer',
        'is_active' => true,
    ];

    Passport::actingAs($this->admin, [], 'api');

    $response = $this
        ->postJson('/api/users/employees', $employeeData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_code' => 'EMP001',
        ]);

    $this->assertDatabaseHas('employees', [
        'email' => 'john.doe@example.com',
        'employee_code' => 'EMP001',
    ]);
});

test('employee requires first name, last name, and email', function () {
    Passport::actingAs($this->admin, [], 'api');

    $response = $this
        ->postJson('/api/users/employees', [
            'phone' => '555-0123',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
});

test('employee email must be unique', function () {
    Employee::factory()->create(['email' => 'john@example.com']);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this
        ->postJson('/api/users/employees', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('admin can view a specific employee', function () {
    $employee = Employee::factory()->create(['user_id' => $this->admin->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this
        ->getJson("/api/users/employees/{$employee->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $employee->id,
                'email' => $employee->email,
            ],
        ]);
});

test('admin can update an employee', function () {
    $employee = Employee::factory()->create(['user_id' => $this->admin->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this
        ->putJson("/api/users/employees/{$employee->id}", [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $employee->email,
            'position' => 'Senior Developer',
            'is_active' => true,
        ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'first_name' => 'Jane',
            'position' => 'Senior Developer',
        ]);

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'first_name' => 'Jane',
    ]);
});

test('admin can soft delete an employee', function () {
    $employee = Employee::factory()->create(['user_id' => $this->admin->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this
        ->deleteJson("/api/users/employees/{$employee->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('employees', [
        'id' => $employee->id,
    ]);
});

test('admin can search employees by name', function () {
    Employee::factory()->create([
        'user_id' => $this->admin->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
    Employee::factory()->create([
        'user_id' => $this->admin->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this
        ->getJson('/api/users/employees?search=John');

    $response->assertStatus(200)
        ->assertJsonFragment(['first_name' => 'John']);
});

test('admin can filter active employees', function () {
    Employee::factory()->create(['user_id' => $this->admin->id, 'is_active' => true]);
    Employee::factory()->create(['user_id' => $this->admin->id, 'is_active' => false]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this
        ->getJson('/api/users/employees?is_active=1');

    $response->assertStatus(200);

    $data = $response->json('data');
    expect($data)->each(fn ($employee) => $employee->is_active->toBeTrue());
});

test('reset employee password successfully', function () {
    // Arrange: Create a user with appropriate role and permissions
    $admin = User::factory()->create();
    $adminRole = Role::findByName('administrator', 'api');
    $admin->assignRole($adminRole);
    
    // Create an employee with a password that belongs to the admin
    $employee = Employee::factory()->create([
        'password' => Hash::make('original_password'),
        'force_password_change' => false,
        'user_id' => $admin->id,
    ]);

    // Act: Reset the employee password
    $response = $this
        ->actingAs($admin, 'api')
        ->postJson("/api/users/employees/{$employee->id}/reset-password");

    // Assert: Response is successful
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Contraseña del empleado reseteada correctamente. El empleado deberá establecer una nueva contraseña en su próximo ingreso.',
        ]);

    // Assert: Employee password is nullified and force_password_change is true
    $employee->refresh();
    expect($employee->password)->toBeNull();
    expect($employee->force_password_change)->toBeTrue();
});

test('reset employee password requires authentication', function () {
    // Arrange: Create an employee
    $employee = Employee::factory()->create();

    // Act: Try to reset password without authentication
    $response = $this->postJson("/api/users/employees/{$employee->id}/reset-password");

    // Assert: Unauthorized response
    $response->assertStatus(401);
});

test('reset employee password with non-existent employee', function () {
    // Arrange: Create authenticated admin with proper role
    $admin = User::factory()->create();
    $adminRole = Role::findByName('administrator', 'api');
    $admin->assignRole($adminRole);

    // Act: Try to reset password for non-existent employee
    $response = $this
        ->actingAs($admin, 'api')
        ->postJson("/api/users/employees/99999/reset-password");

    // Assert: Not found response
    $response->assertStatus(404);
});
