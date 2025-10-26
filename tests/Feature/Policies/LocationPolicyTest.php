<?php

use App\Models\Location;
use App\Models\User;
use App\Policies\LocationPolicy;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
    
    $this->policy = new LocationPolicy();
    
    // Create test users with different roles
    $this->adminUser = User::factory()->create();
    $adminRole = Role::findByName('administrator', 'api');
    $this->adminUser->assignRole($adminRole);
    
    $this->supervisorUser = User::factory()->create();
    $supervisorRole = Role::findByName('supervisor', 'api');
    $this->supervisorUser->assignRole($supervisorRole);
    
    $this->regularUser = User::factory()->create();
    $regularRole = Role::findByName('supervisor', 'api');
    $this->regularUser->assignRole($regularRole);
    
    // Create locations
    $this->ownLocation = Location::factory()->create(['user_id' => $this->regularUser->id]);
    $this->otherLocation = Location::factory()->create(['user_id' => $this->supervisorUser->id]);
});

test('viewAny allows users with view-locations permission', function () {
    expect($this->policy->viewAny($this->adminUser))->toBeTrue();
    expect($this->policy->viewAny($this->supervisorUser))->toBeTrue();
});

test('viewAny denies users without view-locations permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->viewAny($userWithoutPermission))->toBeFalse();
});

test('view allows user to see their own location', function () {
    expect($this->policy->view($this->regularUser, $this->ownLocation))->toBeTrue();
});

test('view denies user from seeing other users locations', function () {
    expect($this->policy->view($this->regularUser, $this->otherLocation))->toBeFalse();
});

test('view denies user without view-locations permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->view($userWithoutPermission, $this->ownLocation))->toBeFalse();
});

test('create allows users with create-locations permission', function () {
    expect($this->policy->create($this->adminUser))->toBeTrue();
    expect($this->policy->create($this->supervisorUser))->toBeFalse(); // supervisor doesn't have create-locations
});

test('create denies users without create-locations permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->create($userWithoutPermission))->toBeFalse();
});

test('update denies supervisor even for their own location', function () {
    expect($this->policy->update($this->regularUser, $this->ownLocation))->toBeFalse(); // supervisor doesn't have edit-locations
});

test('update denies user from editing other users locations', function () {
    expect($this->policy->update($this->regularUser, $this->otherLocation))->toBeFalse();
});

test('update denies user without edit-locations permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->update($userWithoutPermission, $this->ownLocation))->toBeFalse();
});

test('delete denies supervisor even for their own location', function () {
    expect($this->policy->delete($this->regularUser, $this->ownLocation))->toBeFalse(); // supervisor doesn't have delete-locations
});

test('delete denies user from deleting other users locations', function () {
    expect($this->policy->delete($this->regularUser, $this->otherLocation))->toBeFalse();
});

test('delete denies user without delete-locations permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->delete($userWithoutPermission, $this->ownLocation))->toBeFalse();
});

test('restore denies supervisor even for their own location', function () {
    expect($this->policy->restore($this->regularUser, $this->ownLocation))->toBeFalse(); // supervisor doesn't have delete-locations
});

test('restore denies user from restoring other users locations', function () {
    expect($this->policy->restore($this->regularUser, $this->otherLocation))->toBeFalse();
});

test('restore denies user without delete-locations permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->restore($userWithoutPermission, $this->ownLocation))->toBeFalse();
});

test('forceDelete denies supervisor even for their own location', function () {
    expect($this->policy->forceDelete($this->regularUser, $this->ownLocation))->toBeFalse(); // supervisor doesn't have delete-locations
});

test('forceDelete denies user from force deleting other users locations', function () {
    expect($this->policy->forceDelete($this->regularUser, $this->otherLocation))->toBeFalse();
});

test('forceDelete denies user without delete-locations permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->forceDelete($userWithoutPermission, $this->ownLocation))->toBeFalse();
});