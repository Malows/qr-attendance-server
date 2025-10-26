<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
});

test('attendance can be created with factory', function () {
    $attendance = Attendance::factory()->create();

    expect($attendance)->toBeInstanceOf(Attendance::class);
    expect($attendance->check_in)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($attendance->employee_id)->toBeInt();
    expect($attendance->location_id)->toBeInt();
    expect($attendance->latitude)->toBeString(); // Decimal cast returns string
    expect($attendance->longitude)->toBeString(); // Decimal cast returns string
});

test('attendance belongs to employee', function () {
    $employee = Employee::factory()->create();
    $attendance = Attendance::factory()->create(['employee_id' => $employee->id]);

    expect($attendance->employee)->toBeInstanceOf(Employee::class);
    expect($attendance->employee->id)->toBe($employee->id);
});

test('attendance belongs to location', function () {
    $location = Location::factory()->create();
    $attendance = Attendance::factory()->create(['location_id' => $location->id]);

    expect($attendance->location)->toBeInstanceOf(Location::class);
    expect($attendance->location->id)->toBe($location->id);
});

test('attendance has correct fillable attributes', function () {
    $fillable = [
        'employee_id',
        'location_id', 
        'check_in',
        'check_out',
        'latitude',
        'longitude',
        'notes',
    ];

    $attendance = new Attendance();
    
    expect($attendance->getFillable())->toBe($fillable);
});

test('attendance has correct casts', function () {
    $expectedCasts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    $attendance = new Attendance();
    $casts = $attendance->getCasts();
    
    foreach ($expectedCasts as $attribute => $cast) {
        expect($casts)->toHaveKey($attribute, $cast);
    }
});

test('attendance latitude is cast to decimal', function () {
    $attendance = Attendance::factory()->create(['latitude' => 40.7128]);
    
    expect($attendance->latitude)->toBeString(); // Decimal cast returns string
    expect((string) $attendance->latitude)->toBe('40.7128000');
});

test('attendance longitude is cast to decimal', function () {
    $attendance = Attendance::factory()->create(['longitude' => -74.0060]);
    
    expect($attendance->longitude)->toBeString(); // Decimal cast returns string
    expect((string) $attendance->longitude)->toBe('-74.0060000');
});

test('attendance total_hours attribute calculates hours correctly', function () {
    $checkIn = now()->setTime(9, 0, 0);
    $checkOut = now()->setTime(17, 30, 0);
    
    $attendance = Attendance::factory()->create([
        'check_in' => $checkIn,
        'check_out' => $checkOut,
    ]);
    
    expect($attendance->total_hours)->toBe(8.5);
});

test('attendance total_hours returns null when no check_out', function () {
    $attendance = Attendance::factory()->create([
        'check_in' => now(),
        'check_out' => null,
    ]);
    
    expect($attendance->total_hours)->toBeNull();
});

test('attendance total_hours calculates partial hours correctly', function () {
    $checkIn = now()->setTime(14, 30, 0);
    $checkOut = now()->setTime(18, 0, 0);
    
    $attendance = Attendance::factory()->create([
        'check_in' => $checkIn,
        'check_out' => $checkOut,
    ]);
    
    expect($attendance->total_hours)->toBe(3.5);
});

test('attendance scope visible returns all for administrator', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('administrator', 'api'));
    
    $attendance1 = Attendance::factory()->create();
    $attendance2 = Attendance::factory()->create();

    $visibleAttendances = Attendance::visible($admin)->get();
    
    expect($visibleAttendances)->toHaveCount(2);
});

test('attendance scope visible returns user employee attendances for manager', function () {
    $manager = User::factory()->create();
    $manager->assignRole(Role::findByName('manager', 'api'));
    
    $ownEmployee = Employee::factory()->create(['user_id' => $manager->id]);
    $otherEmployee = Employee::factory()->create();
    
    $ownAttendance = Attendance::factory()->create(['employee_id' => $ownEmployee->id]);
    $otherAttendance = Attendance::factory()->create(['employee_id' => $otherEmployee->id]);

    $visibleAttendances = Attendance::visible($manager)->get();
    
    expect($visibleAttendances)->toHaveCount(1);
    expect($visibleAttendances->first()->id)->toBe($ownAttendance->id);
});

test('attendance scope visible returns no attendances for other roles', function () {
    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::findByName('supervisor', 'api'));
    
    Attendance::factory()->create();
    Attendance::factory()->create();

    $visibleAttendances = Attendance::visible($supervisor)->get();
    
    expect($visibleAttendances)->toHaveCount(0);
});

test('attendance can have null check_out for open attendance', function () {
    $attendance = Attendance::factory()->create([
        'check_in' => now(),
        'check_out' => null,
    ]);

    expect($attendance->check_in)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($attendance->check_out)->toBeNull();
});

test('attendance can have notes', function () {
    $notes = 'Late arrival due to traffic';
    $attendance = Attendance::factory()->create(['notes' => $notes]);

    expect($attendance->notes)->toBe($notes);
});

test('attendance timestamps are automatically managed', function () {
    $attendance = Attendance::factory()->create();

    expect($attendance->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($attendance->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('attendance can be created with all fillable attributes', function () {
    $employee = Employee::factory()->create();
    $location = Location::factory()->create();
    
    $attendanceData = [
        'employee_id' => $employee->id,
        'location_id' => $location->id,
        'check_in' => '2023-01-15 09:00:00',
        'check_out' => '2023-01-15 17:00:00',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'notes' => 'Regular workday',
    ];
    
    $attendance = Attendance::create($attendanceData);
    
    expect($attendance->employee_id)->toBe($employee->id);
    expect($attendance->location_id)->toBe($location->id);
    expect($attendance->check_in->format('Y-m-d H:i:s'))->toBe('2023-01-15 09:00:00');
    expect($attendance->check_out->format('Y-m-d H:i:s'))->toBe('2023-01-15 17:00:00');
    expect($attendance->latitude)->toBe('40.7128000'); // Decimal cast returns string
    expect($attendance->longitude)->toBe('-74.0060000'); // Decimal cast returns string
    expect($attendance->notes)->toBe('Regular workday');
});

test('attendance can have coordinates for location tracking', function () {
    $attendance = Attendance::factory()->create([
        'latitude' => 37.7749,
        'longitude' => -122.4194, // San Francisco coordinates
    ]);
    
    expect($attendance->latitude)->toBe('37.7749000'); // Decimal cast returns string
    expect($attendance->longitude)->toBe('-122.4194000'); // Decimal cast returns string
});