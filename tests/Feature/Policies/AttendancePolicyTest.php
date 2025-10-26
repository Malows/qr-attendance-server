<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Policies\AttendancePolicy;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
    
    $this->policy = new AttendancePolicy();
    
    // Create test users with different roles
    $this->adminUser = User::factory()->create();
    $adminRole = Role::findByName('administrator', 'api');
    $this->adminUser->assignRole($adminRole);
    
    $this->supervisorUser = User::factory()->create();
    $supervisorRole = Role::findByName('supervisor', 'api');
    $this->supervisorUser->assignRole($supervisorRole);
    
    $this->regularUser = User::factory()->create();
    $regularRole = Role::findByName('supervisor', 'api'); // Default role for users
    $this->regularUser->assignRole($regularRole);
    
    // Create employees
    $this->ownEmployee = Employee::factory()->create(['user_id' => $this->regularUser->id]);
    $this->otherEmployee = Employee::factory()->create(['user_id' => $this->supervisorUser->id]);
    
    // Create attendances
    $this->ownAttendance = Attendance::factory()->create(['employee_id' => $this->ownEmployee->id]);
    $this->otherAttendance = Attendance::factory()->create(['employee_id' => $this->otherEmployee->id]);
});

test('viewAny allows users with view-attendances permission', function () {
    expect($this->policy->viewAny($this->adminUser))->toBeTrue();
    expect($this->policy->viewAny($this->supervisorUser))->toBeTrue();
});

test('viewAny denies users without view-attendances permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->viewAny($userWithoutPermission))->toBeFalse();
});

test('view allows admin to see any attendance', function () {
    expect($this->policy->view($this->adminUser, $this->ownAttendance))->toBeTrue();
    expect($this->policy->view($this->adminUser, $this->otherAttendance))->toBeTrue();
});

test('view allows user to see their own attendance', function () {
    expect($this->policy->view($this->regularUser, $this->ownAttendance))->toBeTrue();
});

test('view denies user from seeing other users attendance', function () {
    expect($this->policy->view($this->regularUser, $this->otherAttendance))->toBeFalse();
});

test('view denies user without view-attendances permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->view($userWithoutPermission, $this->ownAttendance))->toBeFalse();
});

test('create allows users with create-attendances permission', function () {
    expect($this->policy->create($this->adminUser))->toBeTrue();
    expect($this->policy->create($this->supervisorUser))->toBeTrue();
});

test('create denies users without create-attendances permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->create($userWithoutPermission))->toBeFalse();
});

test('update allows admin to edit any attendance', function () {
    expect($this->policy->update($this->adminUser, $this->ownAttendance))->toBeTrue();
    expect($this->policy->update($this->adminUser, $this->otherAttendance))->toBeTrue();
});

test('update allows user to edit their own attendance', function () {
    expect($this->policy->update($this->regularUser, $this->ownAttendance))->toBeTrue();
});

test('update denies user from editing other users attendance', function () {
    expect($this->policy->update($this->regularUser, $this->otherAttendance))->toBeFalse();
});

test('update denies user without edit-attendances permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->update($userWithoutPermission, $this->ownAttendance))->toBeFalse();
});

test('delete allows admin to delete any attendance', function () {
    expect($this->policy->delete($this->adminUser, $this->ownAttendance))->toBeTrue();
    expect($this->policy->delete($this->adminUser, $this->otherAttendance))->toBeTrue();
});

test('delete denies supervisor even for their own attendance', function () {
    expect($this->policy->delete($this->regularUser, $this->ownAttendance))->toBeFalse();
});

test('delete denies user from deleting other users attendance', function () {
    expect($this->policy->delete($this->regularUser, $this->otherAttendance))->toBeFalse();
});

test('delete denies user without delete-attendances permission', function () {
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->delete($userWithoutPermission, $this->ownAttendance))->toBeFalse();
});

test('restore only allows admins with delete-attendances permission', function () {
    expect($this->policy->restore($this->adminUser, $this->ownAttendance))->toBeTrue();
    expect($this->policy->restore($this->supervisorUser, $this->ownAttendance))->toBeFalse();
    
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->restore($userWithoutPermission, $this->ownAttendance))->toBeFalse();
});

test('forceDelete only allows admins with delete-attendances permission', function () {
    expect($this->policy->forceDelete($this->adminUser, $this->ownAttendance))->toBeTrue();
    expect($this->policy->forceDelete($this->supervisorUser, $this->ownAttendance))->toBeFalse();
    
    $userWithoutPermission = User::factory()->create();
    expect($this->policy->forceDelete($userWithoutPermission, $this->ownAttendance))->toBeFalse();
});