<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from request header or use default
        $locale = $request->header('Accept-Language', config('app.locale'));
        
        // Extract the primary language code (e.g., 'es' from 'es-ES')
        $locale = substr($locale, 0, 2);
        
        // Check if the locale is supported
        $supportedLocales = ['en', 'es'];
        
        if (in_array($locale, $supportedLocales)) {
            App::setLocale($locale);
        }
        
        return $next($request);
    }
}
