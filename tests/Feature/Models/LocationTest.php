<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
});

test('location has fillable attributes', function () {
    $fillable = [
        'user_id',
        'name',
        'address',
        'city',
        'latitude',
        'longitude',
        'description',
        'is_active',
    ];

    $location = new Location();
    expect($location->getFillable())->toBe($fillable);
});

test('location has correct casts', function () {
    $expectedCasts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    $location = new Location();
    $casts = $location->getCasts();
    
    foreach ($expectedCasts as $attribute => $cast) {
        expect($casts)->toHaveKey($attribute, $cast);
    }
});

test('location can be created with factory', function () {
    $location = Location::factory()->create();

    expect($location)->toBeInstanceOf(Location::class);
    expect($location->name)->toBeString();
    expect($location->address)->toBeString();
    expect($location->city)->toBeString();
    expect($location->is_active)->toBeBool();
    expect($location->latitude)->toBeString();
    expect($location->longitude)->toBeString();
});

test('location belongs to user', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create(['user_id' => $user->id]);

    expect($location->user)->toBeInstanceOf(User::class);
    expect($location->user->id)->toBe($user->id);
});

test('location has many attendances', function () {
    $location = Location::factory()->create();
    $attendance1 = Attendance::factory()->create(['location_id' => $location->id]);
    $attendance2 = Attendance::factory()->create(['location_id' => $location->id]);

    expect($location->attendances)->toHaveCount(2);
    expect($location->attendances->first())->toBeInstanceOf(Attendance::class);
});

test('location is_active is cast to boolean', function () {
    $location = Location::factory()->create(['is_active' => 1]);
    expect($location->is_active)->toBeTrue();

    $location = Location::factory()->create(['is_active' => 0]);
    expect($location->is_active)->toBeFalse();
});

test('location latitude is cast to decimal', function () {
    $location = Location::factory()->create(['latitude' => 40.7128]);
    
    expect($location->latitude)->toBeString();
    expect((string) $location->latitude)->toBe('40.7128000');
});

test('location longitude is cast to decimal', function () {
    $location = Location::factory()->create(['longitude' => -74.0060]);
    
    expect($location->longitude)->toBeString();
    expect((string) $location->longitude)->toBe('-74.0060000');
});

test('location soft deletes work correctly', function () {
    $location = Location::factory()->create();
    $locationId = $location->id;

    // Soft delete
    $location->delete();

    // Should not be found in normal queries
    expect(Location::find($locationId))->toBeNull();
    
    // Should be found in trashed queries
    expect(Location::withTrashed()->find($locationId))->not()->toBeNull();
    expect(Location::onlyTrashed()->find($locationId))->not()->toBeNull();
});

test('location scope visible returns all for administrator', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('administrator', 'api'));
    
    $location1 = Location::factory()->create();
    $location2 = Location::factory()->create();

    $visibleLocations = Location::visible($admin)->get();
    
    expect($visibleLocations)->toHaveCount(2);
});

test('location scope visible returns user locations for manager', function () {
    $manager = User::factory()->create();
    $manager->assignRole(Role::findByName('manager', 'api'));
    
    $ownLocation = Location::factory()->create(['user_id' => $manager->id]);
    $otherLocation = Location::factory()->create();

    $visibleLocations = Location::visible($manager)->get();
    
    expect($visibleLocations)->toHaveCount(1);
    expect($visibleLocations->first()->id)->toBe($ownLocation->id);
});

test('location scope visible returns no locations for other roles', function () {
    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::findByName('supervisor', 'api'));
    
    Location::factory()->create();
    Location::factory()->create();

    $visibleLocations = Location::visible($supervisor)->get();
    
    expect($visibleLocations)->toHaveCount(0);
});

test('location can have coordinates within valid range', function () {
    $location = Location::factory()->create([
        'latitude' => 90.0000000,
        'longitude' => 180.0000000,
    ]);
    
    expect($location->latitude)->toBe('90.0000000');
    expect($location->longitude)->toBe('180.0000000');
});

test('location can have negative coordinates', function () {
    $location = Location::factory()->create([
        'latitude' => -45.5231,
        'longitude' => -122.6765,
    ]);
    
    expect($location->latitude)->toBe('-45.5231000');
    expect($location->longitude)->toBe('-122.6765000');
});

test('location can have optional description', function () {
    $location = Location::factory()->create(['description' => null]);
    expect($location->description)->toBeNull();

    $location = Location::factory()->create(['description' => 'Main office building']);
    expect($location->description)->toBe('Main office building');
});

test('location uses soft deletes trait', function () {
    $location = Location::factory()->create();
    
    expect(in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($location)))->toBeTrue();
});

test('location can be created with all fillable attributes', function () {
    $user = User::factory()->create();
    
    $locationData = [
        'user_id' => $user->id,
        'name' => 'Test Location',
        'address' => '123 Main St',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'description' => 'Test description',
        'is_active' => true,
    ];
    
    $location = Location::create($locationData);
    
    expect($location->user_id)->toBe($user->id);
    expect($location->name)->toBe('Test Location');
    expect($location->address)->toBe('123 Main St');
    expect($location->city)->toBe('Test City');
    expect($location->latitude)->toBe('40.7128000');
    expect($location->longitude)->toBe('-74.0060000');
    expect($location->description)->toBe('Test description');
    expect($location->is_active)->toBeTrue();
});

test('location can have employees assigned', function () {
    $location = Location::factory()->create();
    $employee1 = Employee::factory()->create();
    $employee2 = Employee::factory()->create();
    
    $location->employees()->attach([$employee1->id, $employee2->id]);
    
    expect($location->employees)->toHaveCount(2);
    expect($location->employees->pluck('id'))->toContain($employee1->id, $employee2->id);
});

test('location can detach employees', function () {
    $location = Location::factory()->create();
    $employee = Employee::factory()->create();
    
    $location->employees()->attach($employee->id);
    expect($location->employees)->toHaveCount(1);
    
    $location->employees()->detach($employee->id);
    expect($location->fresh()->employees)->toHaveCount(0);
});

test('location employee relationship has timestamps', function () {
    $location = Location::factory()->create();
    $employee = Employee::factory()->create();
    
    $location->employees()->attach($employee->id);
    
    $pivot = $location->employees()->first()->pivot;
    expect($pivot->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($pivot->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('location employee relationship is bidirectional', function () {
    $location = Location::factory()->create();
    $employee = Employee::factory()->create();
    
    // Attach from employee side
    $employee->locations()->attach($location->id);
    
    // Should be visible from location side
    expect($location->fresh()->employees)->toHaveCount(1);
    expect($location->employees->first()->id)->toBe($employee->id);
    
    // And vice versa
    expect($employee->fresh()->locations)->toHaveCount(1);
    expect($employee->locations->first()->id)->toBe($location->id);
});

test('location has employee method works', function () {
    $location = Location::factory()->create();
    $employee1 = Employee::factory()->create();
    $employee2 = Employee::factory()->create();
    
    $location->employees()->attach($employee1->id);
    
    expect($location->hasEmployee($employee1))->toBeTrue();
    expect($location->hasEmployee($employee2))->toBeFalse();
});

test('location active employees attribute works', function () {
    $location = Location::factory()->create();
    $activeEmployee = Employee::factory()->create(['is_active' => true]);
    $inactiveEmployee = Employee::factory()->create(['is_active' => false]);
    
    $location->employees()->attach([$activeEmployee->id, $inactiveEmployee->id]);
    
    $activeEmployees = $location->active_employees;
    expect($activeEmployees)->toHaveCount(1);
    expect($activeEmployees->first()->id)->toBe($activeEmployee->id);
});

test('location can assign and remove multiple employees', function () {
    $location = Location::factory()->create();
    $employee1 = Employee::factory()->create();
    $employee2 = Employee::factory()->create();
    $employee3 = Employee::factory()->create();
    
    // Assign multiple employees
    $location->assignEmployees([$employee1->id, $employee2->id]);
    expect($location->fresh()->employees)->toHaveCount(2);
    
    // Add another employee without removing existing ones
    $location->assignEmployees([$employee3->id]);
    expect($location->fresh()->employees)->toHaveCount(3);
    
    // Remove some employees
    $location->removeEmployees([$employee1->id, $employee3->id]);
    expect($location->fresh()->employees)->toHaveCount(1);
    expect($location->fresh()->employees->first()->id)->toBe($employee2->id);
});