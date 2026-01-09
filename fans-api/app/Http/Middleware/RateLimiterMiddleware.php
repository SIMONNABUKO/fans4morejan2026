<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter as FacadesRateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use PDOException;

class RateLimiterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiterName = 'api'): Response
    {
        $key = $limiterName . ':' . ($request->user()?->id ?: $request->ip());
        
        try {
            $maxAttempts = 3; // Number of retries
            $attempt = 0;
            $success = false;
            
            while (!$success && $attempt < $maxAttempts) {
                try {
                    if (FacadesRateLimiter::tooManyAttempts($key, 60)) {
                        return response()->json([
                            'message' => 'Too many requests',
                        ], 429);
                    }

                    FacadesRateLimiter::hit($key);
                    $success = true;
                } catch (PDOException $e) {
                    // Only retry on deadlock
                    if ($e->getCode() !== '40001' || strpos($e->getMessage(), 'Deadlock') === false) {
                        throw $e;
                    }
                    
                    $attempt++;
                    if ($attempt < $maxAttempts) {
                        // Add a small random delay before retrying
                        usleep(rand(10000, 50000)); // 10-50ms
                        Log::info('Retrying rate limiter after deadlock', [
                            'attempt' => $attempt,
                            'key' => $key
                        ]);
                    }
                }
            }

            if (!$success) {
                Log::error('Rate limiter failed after max retries', [
                    'key' => $key,
                    'attempts' => $maxAttempts
                ]);
                // Allow the request to proceed if we can't rate limit
                // You might want to change this behavior depending on your requirements
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Rate limiter error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Allow the request to proceed if rate limiting fails
            // You might want to change this behavior depending on your requirements
            return $next($request);
        }
    }
} 