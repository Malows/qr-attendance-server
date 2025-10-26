<?php

use App\Http\Controllers\Api\Employees\AttendanceController;
use App\Http\Controllers\Api\Employees\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Employees API Routes
|--------------------------------------------------------------------------
|
| Routes for employee self-service: login and attendance tracking.
| Guard: auth:employee
| Audience: Employees (mobile apps, kiosks, terminals)
|
*/

// Public routes - Employees
Route::prefix('/auth')->name('auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login');

    Route::middleware(['auth:employee', 'throttle:employees-api'])->group(function () {
        // Auth
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('update-password');
    });
});

// Protected routes - Employees (Self-service)
Route::middleware(['auth:employee', 'throttle:employees-api'])->group(function () {
    Route::prefix('/attendances')->name('attendances.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/current', [AttendanceController::class, 'current'])->name('current');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->middleware('throttle:attendance')->name('check-in');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->middleware('throttle:attendance')->name('check-out');
    });

    // Available locations
    Route::get('/locations', [AttendanceController::class, 'locations'])->name('locations.index');
});
