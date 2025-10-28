<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Laravel\Passport\Passport;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'password' => bcrypt('password123'),
    ]);
    $this->location = Location::factory()->create(['user_id' => $this->user->id]);
});

test('employee can view their own attendances', function () {
    Attendance::factory()->count(3)->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
    ]);

    // Create attendance for another employee (should not be visible)
    $otherEmployee = Employee::factory()->create(['user_id' => $this->user->id]);
    Attendance::factory()->create([
        'employee_id' => $otherEmployee->id,
        'location_id' => $this->location->id,
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    $response = $this->getJson('/api/employees/attendances');

    $response->assertStatus(200)
        ->assertJsonCount(3);

    $data = $response->json();
    expect($data)->each(fn ($att) => $att->employee_id->toBe($this->employee->id));
});

test('employee can check in at a location', function () {
    Passport::actingAs($this->employee, [], 'employee');

    $response = $this->postJson('/api/employees/attendances/check-in', [
        'location_id' => $this->location->id,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'employee_id' => $this->employee->id,
            'location_id' => $this->location->id,
        ])
        ->assertJsonMissing(['check_out']);

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_out' => null,
    ]);
});

test('employee cannot check in if already checked in', function () {
    // Create an open attendance (no check_out)
    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now(),
        'check_out' => null,
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->postJson('/api/employees/attendances/check-in', [
            'location_id' => $this->location->id,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'error' => __('messages.attendance.already_checked_in'),
        ]);
});

test('employee can check out from current attendance', function () {
    $attendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subHours(4),
        'check_out' => null,
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->postJson('/api/employees/attendances/check-out', [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'employee_id',
            'check_in',
            'check_out',
        ]);

    expect($attendance->fresh()->check_out)->not->toBeNull();
});

test('employee cannot check out without an open attendance', function () {
    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->postJson('/api/employees/attendances/check-out', [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'error' => __('messages.attendance.no_open_attendance'),
        ]);
});

test('employee can view their current open attendance', function () {
    $attendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subHours(2),
        'check_out' => null,
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->getJson('/api/employees/attendances/current');

    $response->assertStatus(200)
        ->assertJson([
            'id' => $attendance->id,
            'employee_id' => $this->employee->id,
        ])
        ->assertJsonMissing(['check_out']);
});

test('employee gets null when no current attendance', function () {
    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->getJson('/api/employees/attendances/current');

    $response->assertStatus(200)
        ->assertJson(['attendance' => null]);
});

test('employee can list available locations', function () {
    Location::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'is_active' => true,
    ]);
    Location::factory()->create([
        'user_id' => $this->user->id,
        'is_active' => false,
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->getJson('/api/employees/locations');

    $response->assertStatus(200)
        ->assertJsonCount(4); // 3 active + 1 from beforeEach

    $data = $response->json();
    expect($data)->each(fn ($loc) => $loc->is_active->toBeTrue());
});

test('check in requires location_id', function () {
    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->postJson('/api/employees/attendances/check-in', [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['location_id']);
});

test('check in requires latitude and longitude', function () {
    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->postJson('/api/employees/attendances/check-in', [
            'location_id' => $this->location->id,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['latitude', 'longitude']);
});

test('check out requires latitude and longitude', function () {
    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subHours(4),
        'check_out' => null,
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->postJson('/api/employees/attendances/check-out', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['latitude', 'longitude']);
});

test('employee can add notes when checking in', function () {
    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->postJson('/api/employees/attendances/check-in', [
            'location_id' => $this->location->id,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'notes' => 'Starting my shift',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $this->employee->id,
        'notes' => 'Starting my shift',
    ]);
});

test('hours worked is calculated correctly on check out', function () {
    $checkInTime = now()->subHours(5);
    $attendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => $checkInTime,
        'check_out' => null,
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    $response = $this
        ->postJson('/api/employees/attendances/check-out', [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

    $response->assertStatus(200);

    $fresh = $attendance->fresh();
    $hoursWorked = $fresh->check_in->diffInHours($fresh->check_out);

    expect($hoursWorked)->toBeGreaterThanOrEqual(4)
        ->toBeLessThanOrEqual(6); // Some tolerance for test execution time
});

// Filter Tests
test('employee can filter attendances by start_date', function () {
    // Create attendances with different dates
    $oldAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(10),
        'check_out' => now()->subDays(10)->addHours(8),
    ]);

    $recentAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(2),
        'check_out' => now()->subDays(2)->addHours(8),
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    // Filter to only get recent attendance
    $response = $this->getJson('/api/employees/attendances?start_date=' . now()->subDays(5)->toDateString());

    $response->assertStatus(200)
        ->assertJsonCount(1);

    $data = $response->json();
    expect($data[0]['id'])->toBe($recentAttendance->id);
});

test('employee can filter attendances by end_date', function () {
    // Create attendances with different dates
    $oldAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(10),
        'check_out' => now()->subDays(10)->addHours(8),
    ]);

    $recentAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(2),
        'check_out' => now()->subDays(2)->addHours(8),
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    // Filter to only get old attendance
    $response = $this->getJson('/api/employees/attendances?end_date=' . now()->subDays(5)->toDateString());

    $response->assertStatus(200)
        ->assertJsonCount(1);

    $data = $response->json();
    expect($data[0]['id'])->toBe($oldAttendance->id);
});

test('employee can filter attendances by date range', function () {
    // Create attendances with different dates
    $veryOldAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(20),
        'check_out' => now()->subDays(20)->addHours(8),
    ]);

    $targetAttendance1 = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(7),
        'check_out' => now()->subDays(7)->addHours(8),
    ]);

    $targetAttendance2 = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(3),
        'check_out' => now()->subDays(3)->addHours(8),
    ]);

    $recentAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(1),
        'check_out' => now()->subDays(1)->addHours(8),
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    // Filter to get attendances between 10 days ago and 2 days ago
    $startDate = now()->subDays(10)->toDateString();
    $endDate = now()->subDays(2)->toDateString();
    $response = $this->getJson("/api/employees/attendances?start_date={$startDate}&end_date={$endDate}");

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $data = $response->json();
    $ids = collect($data)->pluck('id')->toArray();
    expect($ids)->toContain($targetAttendance1->id, $targetAttendance2->id)
        ->not->toContain($veryOldAttendance->id, $recentAttendance->id);
});

test('employee can filter attendances by location_id', function () {
    // Create a second location
    $location2 = Location::factory()->create(['user_id' => $this->user->id]);

    // Create attendances at different locations
    $attendance1 = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(3),
        'check_out' => now()->subDays(3)->addHours(8),
    ]);

    $attendance2 = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $location2->id,
        'check_in' => now()->subDays(2),
        'check_out' => now()->subDays(2)->addHours(8),
    ]);

    $attendance3 = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(1),
        'check_out' => now()->subDays(1)->addHours(8),
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    // Filter by first location
    $response = $this->getJson("/api/employees/attendances?location_id={$this->location->id}");

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $data = $response->json();
    $ids = collect($data)->pluck('id')->toArray();
    expect($ids)->toContain($attendance1->id, $attendance3->id)
        ->not->toContain($attendance2->id);

    // Verify all returned attendances have the correct location_id
    expect($data)->each(fn ($att) => $att->location_id->toBe($this->location->id));
});

test('employee can combine multiple filters', function () {
    // Create a second location
    $location2 = Location::factory()->create(['user_id' => $this->user->id]);

    // Create various attendances
    $targetAttendance = Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(5),
        'check_out' => now()->subDays(5)->addHours(8),
    ]);

    // Wrong location, right date range
    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $location2->id,
        'check_in' => now()->subDays(4),
        'check_out' => now()->subDays(4)->addHours(8),
    ]);

    // Right location, wrong date range
    Attendance::factory()->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(15),
        'check_out' => now()->subDays(15)->addHours(8),
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    // Apply multiple filters
    $startDate = now()->subDays(10)->toDateString();
    $endDate = now()->subDays(2)->toDateString();
    $response = $this->getJson("/api/employees/attendances?start_date={$startDate}&end_date={$endDate}&location_id={$this->location->id}");

    $response->assertStatus(200)
        ->assertJsonCount(1);

    $data = $response->json();
    expect($data[0]['id'])->toBe($targetAttendance->id);
    expect($data[0]['location_id'])->toBe($this->location->id);
});

test('employee gets empty result when filters match nothing', function () {
    // Create some attendances
    Attendance::factory()->count(3)->create([
        'employee_id' => $this->employee->id,
        'location_id' => $this->location->id,
        'check_in' => now()->subDays(5),
    ]);

    Passport::actingAs($this->employee, [], 'employee');

    // Filter with future date range (should return nothing)
    $startDate = now()->addDays(1)->toDateString();
    $endDate = now()->addDays(5)->toDateString();
    $response = $this->getJson("/api/employees/attendances?start_date={$startDate}&end_date={$endDate}");

    $response->assertStatus(200)
        ->assertJsonCount(0);
});

test('employee attendance filters validate date format', function () {
    Passport::actingAs($this->employee, [], 'employee');

    // Invalid date format
    $response = $this->getJson('/api/employees/attendances?start_date=invalid-date');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['start_date']);
});

test('employee attendance filters validate end_date after start_date', function () {
    Passport::actingAs($this->employee, [], 'employee');

    // end_date before start_date
    $startDate = now()->toDateString();
    $endDate = now()->subDays(1)->toDateString();
    $response = $this->getJson("/api/employees/attendances?start_date={$startDate}&end_date={$endDate}");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end_date']);
});

test('employee attendance filters validate location_id exists', function () {
    Passport::actingAs($this->employee, [], 'employee');

    // Non-existent location_id
    $response = $this->getJson('/api/employees/attendances?location_id=99999');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['location_id']);
});
