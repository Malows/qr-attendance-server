<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
});

test('employee has fillable attributes', function () {
    $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'employee_code',
        'hire_date',
        'position',
        'notes',
        'is_active',
        'password',
        'force_password_change',
    ];

    $employee = new Employee();
    expect($employee->getFillable())->toBe($fillable);
});

test('employee has hidden attributes', function () {
    $hidden = [
        'password',
        'remember_token',
    ];

    $employee = new Employee();
    expect($employee->getHidden())->toBe($hidden);
});

test('employee has correct casts', function () {
    $expectedCasts = [
        'is_active' => 'boolean',
        'hire_date' => 'date',
        'password' => 'hashed',
        'force_password_change' => 'boolean',
    ];

    $employee = new Employee();
    $casts = $employee->getCasts();
    
    foreach ($expectedCasts as $attribute => $cast) {
        expect($casts)->toHaveKey($attribute, $cast);
    }
});

test('employee can be created with factory', function () {
    $employee = Employee::factory()->create();

    expect($employee)->toBeInstanceOf(Employee::class);
    expect($employee->first_name)->toBeString();
    expect($employee->last_name)->toBeString();
    expect($employee->email)->toBeString();
    expect($employee->employee_code)->toBeString();
    expect($employee->is_active)->toBeBool();
    expect($employee->force_password_change)->toBeBool();
});

test('employee belongs to user', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id]);

    expect($employee->user)->toBeInstanceOf(User::class);
    expect($employee->user->id)->toBe($user->id);
});

test('employee has many attendances', function () {
    $employee = Employee::factory()->create();
    $attendance1 = Attendance::factory()->create(['employee_id' => $employee->id]);
    $attendance2 = Attendance::factory()->create(['employee_id' => $employee->id]);

    expect($employee->attendances)->toHaveCount(2);
    expect($employee->attendances->first())->toBeInstanceOf(Attendance::class);
});

test('employee full name attribute combines first and last name', function () {
    $employee = Employee::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($employee->full_name)->toBe('John Doe');
    expect($employee->getFullNameAttribute())->toBe('John Doe');
});

test('employee password is hashed when set', function () {
    $employee = Employee::factory()->create([
        'password' => 'plain-password',
    ]);

    expect($employee->password)->not()->toBe('plain-password');
    expect(Hash::check('plain-password', $employee->password))->toBeTrue();
});

test('employee is_active is cast to boolean', function () {
    $employee = Employee::factory()->create(['is_active' => 1]);
    expect($employee->is_active)->toBeTrue();

    $employee = Employee::factory()->create(['is_active' => 0]);
    expect($employee->is_active)->toBeFalse();
});

test('employee force_password_change is cast to boolean', function () {
    $employee = Employee::factory()->create(['force_password_change' => 1]);
    expect($employee->force_password_change)->toBeTrue();

    $employee = Employee::factory()->create(['force_password_change' => 0]);
    expect($employee->force_password_change)->toBeFalse();
});

test('employee hire_date is cast to date', function () {
    $employee = Employee::factory()->create(['hire_date' => '2023-01-15']);
    
    expect($employee->hire_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($employee->hire_date->format('Y-m-d'))->toBe('2023-01-15');
});

test('employee soft deletes work correctly', function () {
    $employee = Employee::factory()->create();
    $employeeId = $employee->id;

    // Soft delete
    $employee->delete();

    // Should not be found in normal queries
    expect(Employee::find($employeeId))->toBeNull();
    
    // Should be found in trashed queries
    expect(Employee::withTrashed()->find($employeeId))->not()->toBeNull();
    expect(Employee::onlyTrashed()->find($employeeId))->not()->toBeNull();
});

test('employee scope visible returns all for administrator', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('administrator', 'api'));
    
    $employee1 = Employee::factory()->create();
    $employee2 = Employee::factory()->create();

    $visibleEmployees = Employee::visible($admin)->get();
    
    expect($visibleEmployees)->toHaveCount(2);
});

test('employee scope visible returns user employees for manager', function () {
    $manager = User::factory()->create();
    $manager->assignRole(Role::findByName('manager', 'api'));
    
    $ownEmployee = Employee::factory()->create(['user_id' => $manager->id]);
    $otherEmployee = Employee::factory()->create();

    $visibleEmployees = Employee::visible($manager)->get();
    
    expect($visibleEmployees)->toHaveCount(1);
    expect($visibleEmployees->first()->id)->toBe($ownEmployee->id);
});

test('employee scope visible returns no employees for other roles', function () {
    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::findByName('supervisor', 'api'));
    
    Employee::factory()->create();
    Employee::factory()->create();

    $visibleEmployees = Employee::visible($supervisor)->get();
    
    expect($visibleEmployees)->toHaveCount(0);
});

test('employee can have null password', function () {
    $employee = Employee::factory()->create(['password' => null]);
    
    expect($employee->password)->toBeNull();
});

test('employee can be created with force_password_change true', function () {
    $employee = Employee::factory()->create([
        'password' => null,
        'force_password_change' => true,
    ]);
    
    expect($employee->password)->toBeNull();
    expect($employee->force_password_change)->toBeTrue();
});

test('employee can be assigned to locations', function () {
    $employee = Employee::factory()->create();
    $location1 = Location::factory()->create();
    $location2 = Location::factory()->create();
    
    $employee->locations()->attach([$location1->id, $location2->id]);
    
    expect($employee->locations)->toHaveCount(2);
    expect($employee->locations->pluck('id'))->toContain($location1->id, $location2->id);
});

test('employee can be detached from locations', function () {
    $employee = Employee::factory()->create();
    $location = Location::factory()->create();
    
    $employee->locations()->attach($location->id);
    expect($employee->locations)->toHaveCount(1);
    
    $employee->locations()->detach($location->id);
    expect($employee->fresh()->locations)->toHaveCount(0);
});

test('employee location relationship has timestamps', function () {
    $employee = Employee::factory()->create();
    $location = Location::factory()->create();
    
    $employee->locations()->attach($location->id);
    
    $pivot = $employee->locations()->first()->pivot;
    expect($pivot->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($pivot->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('employee location relationship prevents duplicates', function () {
    $employee = Employee::factory()->create();
    $location = Location::factory()->create();
    
    // First attach should work
    $employee->locations()->attach($location->id);
    expect($employee->fresh()->locations)->toHaveCount(1);
    
    // Second attach should throw exception due to unique constraint
    expect(fn() => $employee->locations()->attach($location->id))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('employee has location access method works', function () {
    $employee = Employee::factory()->create();
    $location1 = Location::factory()->create();
    $location2 = Location::factory()->create();
    
    $employee->locations()->attach($location1->id);
    
    expect($employee->hasLocationAccess($location1))->toBeTrue();
    expect($employee->hasLocationAccess($location2))->toBeFalse();
});

test('employee active locations attribute works', function () {
    $employee = Employee::factory()->create();
    $activeLocation = Location::factory()->create(['is_active' => true]);
    $inactiveLocation = Location::factory()->create(['is_active' => false]);
    
    $employee->locations()->attach([$activeLocation->id, $inactiveLocation->id]);
    
    $activeLocations = $employee->active_locations;
    expect($activeLocations)->toHaveCount(1);
    expect($activeLocations->first()->id)->toBe($activeLocation->id);
});

test('employee can assign and remove from multiple locations', function () {
    $employee = Employee::factory()->create();
    $location1 = Location::factory()->create();
    $location2 = Location::factory()->create();
    $location3 = Location::factory()->create();
    
    // Assign to multiple locations
    $employee->assignToLocations([$location1->id, $location2->id]);
    expect($employee->fresh()->locations)->toHaveCount(2);
    
    // Add another location without removing existing ones
    $employee->assignToLocations([$location3->id]);
    expect($employee->fresh()->locations)->toHaveCount(3);
    
    // Remove some locations
    $employee->removeFromLocations([$location1->id, $location3->id]);
    expect($employee->fresh()->locations)->toHaveCount(1);
    expect($employee->fresh()->locations->first()->id)->toBe($location2->id);
});