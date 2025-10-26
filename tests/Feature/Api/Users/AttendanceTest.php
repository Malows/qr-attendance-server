<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::findByName('administrator', 'api'));

    $this->location = Location::factory()->create(['user_id' => $this->admin->id]);
    $this->employee = Employee::factory()->create(['user_id' => $this->admin->id]);
});

test('admin can list all attendances', function () {
    Attendance::factory()->count(3)->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
    ]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson('/api/users/attendances');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => ['id', 'employee_id', 'location_id', 'check_in', 'check_out'],
        ])
        ->assertJsonCount(3);
});

test('admin can create an attendance record', function () {
    $attendanceData = [
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->toDateTimeString(),
        'check_in_latitude' => 40.7128,
        'check_in_longitude' => -74.0060,
        'notes' => 'Regular check-in',
    ];

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->postJson('/api/users/attendances', $attendanceData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'employee_id' => $this->employee->id,
            'location_id' => $this->location->id,
        ]);

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
    ]);
});

test('attendance requires employee and location', function () {
    Passport::actingAs($this->admin, [], 'api');

    $response = $this->postJson('/api/users/attendances', [
        'check_in' => now()->toDateTimeString(),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['employee_id', 'location_id']);
});

test('admin can view a specific attendance', function () {
    $attendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
    ]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson("/api/users/attendances/{$attendance->id}");

    $response->assertStatus(200)
        ->assertJson([
            'id' => $attendance->id,
            'employee_id' => $this->employee->id,
        ]);
});

test('admin can update an attendance', function () {
    $attendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subHours(2),
    ]);

    $checkOut = now();

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->putJson("/api/users/attendances/{$attendance->id}", [
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => $attendance->check_in->toDateTimeString(),
        'check_out' => $checkOut->toDateTimeString(),
        'check_out_latitude' => 40.7128,
        'check_out_longitude' => -74.0060,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('attendances', [
        'id' => $attendance->id,
    ]);

    expect($attendance->fresh()->check_out)->not->toBeNull();
});

test('admin can delete an attendance', function () {
    $attendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
    ]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->deleteJson("/api/users/attendances/{$attendance->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('attendances', [
        'id' => $attendance->id,
        'deleted_at' => null,
    ]);
});

test('admin can filter attendances by employee', function () {
    $employee2 = Employee::factory()->create(['user_id' => $this->admin->id]);

    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
    ]);
    Attendance::factory()->create([
        'employee_id' => $employee2->id,
        'location_id' => $this->location->id,
    ]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson("/api/users/attendances?employee_id={$this->employee->id}");

    $response->assertStatus(200);

    $data = $response->json();
    expect($data)->each(fn ($att) => $att->employee_id->toBe($this->employee->id));
});

test('admin can filter attendances by location', function () {
    $location2 = Location::factory()->create(['user_id' => $this->admin->id]);

    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
    ]);
    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $location2->id,
    ]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson("/api/users/attendances?location_id={$this->location->id}");

    $response->assertStatus(200);

    $data = $response->json();
    expect($data)->each(fn ($att) => $att->location_id->toBe($this->location->id));
});

test('admin can filter attendances by date range', function () {
    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(5),
    ]);
    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDay(),
    ]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->getJson('/api/users/attendances?start_date='.now()->subDays(2)->toDateString());

    $response->assertStatus(200)
        ->assertJsonCount(1);
});

test('user cannot create attendance with employee from another user', function () {
    $otherUser = User::factory()->create();
    $otherUser->assignRole(Role::findByName('administrator', 'api'));

    $otherEmployee = Employee::factory()->create(['user_id' => $otherUser->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->postJson('/api/users/attendances', [
        'employee_id' => $otherEmployee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->toDateTimeString(),
    ]);

    $response->assertStatus(403);
});

test('user cannot create attendance with location from another user', function () {
    $otherUser = User::factory()->create();
    $otherUser->assignRole(Role::findByName('administrator', 'api'));

    $otherLocation = Location::factory()->create(['user_id' => $otherUser->id]);

    Passport::actingAs($this->admin, [], 'api');

    $response = $this->postJson('/api/users/attendances', [
        'employee_id' => $this->employee->id,
        'location_id' => $otherLocation->id,
        'check_in' => now()->toDateTimeString(),
    ]);

    $response->assertStatus(403);
});

test('admin can filter attendances by end_date', function () {
    // Create attendance from 5 days ago (should be excluded)
    $oldAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(5),
    ]);

    // Create attendance from 2 days ago (should be included)
    $recentAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(2),
    ]);

    // Create attendance from yesterday (should be included)
    $yesterdayAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDay(),
    ]);

    Passport::actingAs($this->admin, [], 'api');

    // Filter to include only attendances up to 3 days ago
    $endDate = now()->subDays(3)->toDateString();
    $response = $this->getJson("/api/users/attendances?end_date={$endDate}");

    $response->assertStatus(200)
        ->assertJsonCount(1); // Only the old attendance should be included

    $data = $response->json();
    expect($data[0]['id'])->toBe($oldAttendance->id);
});

test('admin can filter attendances by both start_date and end_date', function () {
    // Create attendances with different dates
    $veryOldAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(20),
    ]);

    $targetAttendance1 = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(7),
    ]);

    $targetAttendance2 = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(3),
    ]);

    $recentAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDay(),
    ]);

    Passport::actingAs($this->admin, [], 'api');

    // Filter to get attendances between 10 days ago and 2 days ago
    $startDate = now()->subDays(10)->toDateString();
    $endDate = now()->subDays(2)->toDateString();
    $response = $this->getJson("/api/users/attendances?start_date={$startDate}&end_date={$endDate}");

    $response->assertStatus(200)
        ->assertJsonCount(2); // Only the two target attendances should be included

    $data = $response->json();
    $ids = collect($data)->pluck('id')->toArray();
    expect($ids)->toContain($targetAttendance1->id, $targetAttendance2->id)
        ->not->toContain($veryOldAttendance->id, $recentAttendance->id);
});

test('admin gets empty result when end_date filter matches nothing', function () {
    // Create some attendances
    Attendance::factory()->count(3)->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(2), // 2 days ago
    ]);

    Passport::actingAs($this->admin, [], 'api');

    // Filter with end_date of 5 days ago (should return nothing)
    $endDate = now()->subDays(5)->toDateString();
    $response = $this->getJson("/api/users/attendances?end_date={$endDate}");

    $response->assertStatus(200)
        ->assertJsonCount(0);
});
