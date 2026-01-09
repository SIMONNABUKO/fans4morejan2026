<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiter Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the rate limiters for your application. Each
    | rate limiter is identified by a unique key and may be configured
    | with different limits and decay times.
    |
    | Note: Rate limiting logic is handled in RateLimiterServiceProvider
    | to avoid configuration serialization issues.
    |
    */

    'limiters' => [
        // Rate limiting is configured in RateLimiterServiceProvider
        // to avoid configuration serialization issues with closures
    ],
]; 