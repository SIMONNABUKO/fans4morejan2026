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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the preferred language from the Accept-Language header
        $locale = $request->header('Accept-Language');

        // If no language header is present, try to get from session
        if (!$locale && $request->hasSession()) {
            $locale = $request->session()->get('locale');
        }

        // If still no locale, use the default from config
        if (!$locale) {
            $locale = config('app.locale');
        }

        // Validate if the locale is supported
        if (!in_array($locale, config('app.available_locales', ['en', 'fr']))) {
            $locale = config('app.fallback_locale', 'en');
        }

        // Set the application locale
        App::setLocale($locale);

        // Store in session for web routes
        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
} 