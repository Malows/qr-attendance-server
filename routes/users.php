<?php

use App\Http\Controllers\Api\Users\AttendanceController;
use App\Http\Controllers\Api\Users\AuthController;
use App\Http\Controllers\Api\Users\EmployeeController;
use App\Http\Controllers\Api\Users\LocationController;
use App\Http\Controllers\Api\Users\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Users API Routes
|--------------------------------------------------------------------------
|
| Routes for user management, locations, employees, and full attendance control.
| Guard: auth:api
| Audience: Administrators, Managers, Supervisors
|
*/

Route::prefix('/auth')->name('auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');

    // Protected routes - Users (Managers/Administrators)
    Route::middleware(['auth:api', 'throttle:users-api'])->group(function () {
        Route::get('/me', [AuthController::class, 'user'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('update-password');
    });
});

// Protected routes - Users (Managers/Administrators)
Route::middleware(['auth:api', 'throttle:users-api'])->group(function () {
    // full CRUD for Locations, Employees, Attendances
    Route::apiResources([
        'locations' => LocationController::class,
        'employees' => EmployeeController::class,
        'attendances' => AttendanceController::class,
    ]);

    // Reset employee password (Administrators and Managers only)
    Route::post('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');
});

Route::middleware(['auth:api', 'throttle:users-api', 'role:administrator'])->group(function () {
    // Roles & Permissions (Admin only)
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('permissions', [RoleController::class, 'permissions'])->name('permissions.index');
    Route::post('manage/{user}/assign-role', [RoleController::class, 'assignRole'])->name('manage.assign-role');
    Route::post('manage/{user}/remove-role', [RoleController::class, 'removeRole'])->name('manage.remove-role');
    Route::post('manage/{user}/sync-roles', [RoleController::class, 'syncRoles'])->name('manage.sync-roles');
    Route::post('manage/{user}/give-permission', [RoleController::class, 'givePermission'])->name('manage.give-permission');
    Route::post('manage/{user}/revoke-permission', [RoleController::class, 'revokePermission'])->name('manage.revoke-permission');
});
