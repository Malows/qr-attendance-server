<?php

use App\Models\Employee;
use App\Models\User;
use App\Policies\EmployeePolicy;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
    
    $this->policy = new EmployeePolicy();
    
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
    
    // Create employees
    $this->ownEmployee = Employee::factory()->create(['user_id' => $this->regularUser->id]);
    $this->otherEmployee = Employee::factory()->create(['user_id' => $this->supervisorUser->id]);
});

test('viewAny allows users with view-employees permission', function () {
    expect($this->policy->viewAny($this->adminUser))->toBeTrue();
    expect($this->policy->viewAny($this->supervisorUser))->toBeTrue();
});

test('viewAny denies users without view-employees permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->viewAny($userWithoutPermission))->toBeFalse();
});

test('view allows user to see their own employee profile', function () {
    expect($this->policy->view($this->regularUser, $this->ownEmployee))->toBeTrue();
});

test('view denies user from seeing other employee profiles', function () {
    expect($this->policy->view($this->regularUser, $this->otherEmployee))->toBeFalse();
});

test('view denies user without view-employees permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->view($userWithoutPermission, $this->ownEmployee))->toBeFalse();
});

test('create allows users with create-employees permission', function () {
    expect($this->policy->create($this->adminUser))->toBeTrue();
    expect($this->policy->create($this->supervisorUser))->toBeFalse(); // supervisor doesn't have create-employees
});

test('create denies users without create-employees permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->create($userWithoutPermission))->toBeFalse();
});

test('update denies supervisor even for their own employee profile', function () {
    expect($this->policy->update($this->regularUser, $this->ownEmployee))->toBeFalse(); // supervisor doesn't have edit-employees
});

test('update denies user from editing other employee profiles', function () {
    expect($this->policy->update($this->regularUser, $this->otherEmployee))->toBeFalse();
});

test('update denies user without edit-employees permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->update($userWithoutPermission, $this->ownEmployee))->toBeFalse();
});

test('delete denies supervisor even for their own employee profile', function () {
    expect($this->policy->delete($this->regularUser, $this->ownEmployee))->toBeFalse(); // supervisor doesn't have delete-employees
});

test('delete denies user from deleting other employee profiles', function () {
    expect($this->policy->delete($this->regularUser, $this->otherEmployee))->toBeFalse();
});

test('delete denies user without delete-employees permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->delete($userWithoutPermission, $this->ownEmployee))->toBeFalse();
});

test('restore denies supervisor even for their own employee profile', function () {
    expect($this->policy->restore($this->regularUser, $this->ownEmployee))->toBeFalse(); // supervisor doesn't have delete-employees
});

test('restore denies user from restoring other employee profiles', function () {
    expect($this->policy->restore($this->regularUser, $this->otherEmployee))->toBeFalse();
});

test('restore denies user without delete-employees permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->restore($userWithoutPermission, $this->ownEmployee))->toBeFalse();
});

test('forceDelete denies supervisor even for their own employee profile', function () {
    expect($this->policy->forceDelete($this->regularUser, $this->ownEmployee))->toBeFalse(); // supervisor doesn't have delete-employees
});

test('forceDelete denies user from force deleting other employee profiles', function () {
    expect($this->policy->forceDelete($this->regularUser, $this->otherEmployee))->toBeFalse();
});

test('forceDelete denies user without delete-employees permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->forceDelete($userWithoutPermission, $this->ownEmployee))->toBeFalse();
});