<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Main Orchestrator
|--------------------------------------------------------------------------
|
| This file is maintained for compatibility but the actual route loading
| is now handled by App\Providers\RouteServiceProvider.
|
| The RouteServiceProvider provides:
| - Separate rate limiting per context (users, employees)
| - Named routes with prefixes
| - Better organization and maintainability
|
| Route Files:
| - routes/users.php     → /api/users/*
| - routes/employees.php → /api/employees/*
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'QR Attendance API',
        'version' => '1.0.0',
    ]);
})->name('api.health');

// API Information endpoint
Route::get('/info', function () {
    return response()->json([
        'name' => 'QR Attendance API',
        'version' => '1.0.0',
        'contexts' => [
            'users' => [
                'base_url' => '/api/users',
                'guard' => 'api',
                'description' => 'Full system management for administrators and managers',
            ],
            'employees' => [
                'base_url' => '/api/employees',
                'guard' => 'employee',
                'description' => 'Self-service for employees (check-in/check-out)',
            ],
        ],
        'documentation' => [
            'postman' => url('/postman_collection.json'),
        ],
    ]);
})->name('api.info');
