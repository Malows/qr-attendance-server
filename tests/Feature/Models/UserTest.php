<?php

use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
});

test('user has fillable attributes', function () {
    $fillable = [
        'name',
        'email',
        'password',
        'force_password_change',
    ];

    $user = new User();
    expect($user->getFillable())->toBe($fillable);
});

test('user has hidden attributes', function () {
    $hidden = [
        'password',
        'remember_token',
    ];

    $user = new User();
    expect($user->getHidden())->toBe($hidden);
});

test('user has correct casts', function () {
    $expectedCasts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'force_password_change' => 'boolean',
    ];

    $user = new User();
    $casts = $user->getCasts();
    
    foreach ($expectedCasts as $attribute => $cast) {
        expect($casts)->toHaveKey($attribute, $cast);
    }
});

test('user can be created with factory', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBeString();
    expect($user->email)->toBeString();
    expect($user->force_password_change)->toBeBool();
});

test('user has many locations', function () {
    $user = User::factory()->create();
    $location1 = Location::factory()->create(['user_id' => $user->id]);
    $location2 = Location::factory()->create(['user_id' => $user->id]);

    expect($user->locations)->toHaveCount(2);
    expect($user->locations->first())->toBeInstanceOf(Location::class);
});

test('user has many employees', function () {
    $user = User::factory()->create();
    $employee1 = Employee::factory()->create(['user_id' => $user->id]);
    $employee2 = Employee::factory()->create(['user_id' => $user->id]);

    expect($user->employees)->toHaveCount(2);
    expect($user->employees->first())->toBeInstanceOf(Employee::class);
});

test('user password is hashed when set', function () {
    $user = User::factory()->create([
        'password' => 'plain-password',
    ]);

    expect($user->password)->not()->toBe('plain-password');
    expect(Hash::check('plain-password', $user->password))->toBeTrue();
});

test('user force_password_change is cast to boolean', function () {
    $user = User::factory()->create(['force_password_change' => 1]);
    expect($user->force_password_change)->toBeTrue();

    $user = User::factory()->create(['force_password_change' => 0]);
    expect($user->force_password_change)->toBeFalse();
});

test('user email_verified_at is cast to datetime', function () {
    $user = User::factory()->create(['email_verified_at' => '2023-01-15 10:30:00']);
    
    expect($user->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('user soft deletes work correctly', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    // Soft delete
    $user->delete();

    // Should not be found in normal queries
    expect(User::find($userId))->toBeNull();
    
    // Should be found in trashed queries
    expect(User::withTrashed()->find($userId))->not()->toBeNull();
    expect(User::onlyTrashed()->find($userId))->not()->toBeNull();
});

test('user can be assigned roles', function () {
    $user = User::factory()->create();
    $adminRole = Role::findByName('administrator', 'api');
    
    $user->assignRole($adminRole);
    
    expect($user->hasRole('administrator', 'api'))->toBeTrue();
    expect($user->getRoleNames())->toContain('administrator');
});

test('user can have permissions through roles', function () {
    $user = User::factory()->create();
    $adminRole = Role::findByName('administrator', 'api');
    
    $user->assignRole($adminRole);
    
    expect($user->hasPermissionTo('view-users', 'api'))->toBeTrue();
    expect($user->hasPermissionTo('create-users', 'api'))->toBeTrue();
    expect($user->hasPermissionTo('edit-users', 'api'))->toBeTrue();
    expect($user->hasPermissionTo('delete-users', 'api'))->toBeTrue();
});

test('user can have multiple roles', function () {
    $user = User::factory()->create();
    $adminRole = Role::findByName('administrator', 'api');
    $supervisorRole = Role::findByName('supervisor', 'api');
    
    $user->assignRole([$adminRole, $supervisorRole]);
    
    expect($user->hasRole(['administrator', 'supervisor'], 'api'))->toBeTrue();
    expect($user->getRoleNames())->toContain('administrator');
    expect($user->getRoleNames())->toContain('supervisor');
});

test('user can check specific permissions', function () {
    $user = User::factory()->create();
    $supervisorRole = Role::findByName('supervisor', 'api');
    
    $user->assignRole($supervisorRole);
    
    // Supervisor has these permissions
    expect($user->hasPermissionTo('view-attendances', 'api'))->toBeTrue();
    expect($user->hasPermissionTo('create-attendances', 'api'))->toBeTrue();
    
    // Supervisor doesn't have these permissions
    expect($user->hasPermissionTo('delete-attendances', 'api'))->toBeFalse();
    expect($user->hasPermissionTo('create-users', 'api'))->toBeFalse();
});

test('user can be created with force_password_change true', function () {
    $user = User::factory()->create([
        'password' => null,
        'force_password_change' => true,
    ]);
    
    expect($user->password)->toBeNull();
    expect($user->force_password_change)->toBeTrue();
});

test('user email must be unique', function () {
    $user1 = User::factory()->create(['email' => 'test@example.com']);
    
    expect(function () {
        User::factory()->create(['email' => 'test@example.com']);
    })->toThrow(\Illuminate\Database\QueryException::class);
});

test('user uses api tokens trait', function () {
    $user = User::factory()->create();
    
    expect(in_array(\Laravel\Passport\HasApiTokens::class, class_uses($user)))->toBeTrue();
});

test('user uses roles trait', function () {
    $user = User::factory()->create();
    
    expect(in_array(\Spatie\Permission\Traits\HasRoles::class, class_uses($user)))->toBeTrue();
});

test('user uses soft deletes trait', function () {
    $user = User::factory()->create();
    
    expect(in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($user)))->toBeTrue();
});