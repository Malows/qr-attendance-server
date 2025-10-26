<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->apiRoutes();
            $this->usersApiRoutes();
            $this->employeesApiRoutes();
            // $this->webRoutes();
            // $this->settingsRoutes();
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // General API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Users API rate limiting (more permissive for admins)
        RateLimiter::for('users-api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Employees API rate limiting (stricter for mobile clients)
        RateLimiter::for('employees-api', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Login rate limiting (prevent brute force)
        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('email') ?? $request->ip();

            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again later.',
                ], 429);
            });
        });

        // Check-in/check-out rate limiting
        RateLimiter::for('attendance', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function apiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    }

    protected function webRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    protected function settingsRoutes(): void
    {
        if (file_exists(base_path('routes/settings.php'))) {
            Route::middleware(['api', 'auth:api'])
                ->prefix('api/settings')
                ->name('settings.')
                ->group(base_path('routes/settings.php'));
        }
    }

    protected function usersApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/users')
            ->name('users.')
            ->group(base_path('routes/users.php'));
    }

    protected function employeesApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/employees')
            ->name('employees.')
            ->group(base_path('routes/employees.php'));

    }
}
