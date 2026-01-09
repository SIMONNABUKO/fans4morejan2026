<?php

namespace App\Http\Middleware;

use App\Services\LocationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LocationDetectionMiddleware
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $ip = $request->ip();
            
            // Skip if location is already detected and cached in session
            if (!session()->has('user_location') || session('location_ip') !== $ip) {
                $location = $this->locationService->getUserLocation($ip);
                
                if ($location) {
                    session([
                        'user_location' => $location,
                        'location_ip' => $ip,
                        'location_detected_at' => now()
                    ]);
                    
                    Log::info('Location detected and stored in session', [
                        'ip' => $ip,
                        'country' => $location['country'] ?? 'Unknown',
                        'country_code' => $location['country_code'] ?? 'Unknown'
                    ]);
                } else {
                    Log::info('Location detection failed', ['ip' => $ip]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in location detection middleware', [
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);
            
            // Continue processing even if location detection fails
        }

        return $next($request);
    }
}
