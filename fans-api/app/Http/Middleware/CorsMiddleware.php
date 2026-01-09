<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }
        
        $origin = $request->header('Origin');
        $allowedOrigins = [
            'http://localhost',
            'http://localhost:5174',
            'http://localhost:5173',
            'http://localhost:3000',
            'http://localhost:8080',
            'http://localhost:8000',
            'http://127.0.0.1',
            'http://127.0.0.1:5174',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:8080',
            'http://127.0.0.1:8000',
            'https://fans4more.com',
            'https://admin.fans4more.com',
            'https://logout.fans4more.com',
        ];
        
        // Check if the origin is in our allowed list or matches a pattern
        $isAllowed = in_array($origin, $allowedOrigins);
        
        // Check if it matches any of our patterns
        if (!$isAllowed && $origin) {
            foreach (['http://localhost:[0-9]+', 'http://127.0.0.1:[0-9]+'] as $pattern) {
                if (preg_match('#^' . str_replace(['*', '.'], ['[^/]*', '\.'], $pattern) . '$#', $origin)) {
                    $isAllowed = true;
                    break;
                }
            }
        }
        
        if ($isAllowed && $origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
        }
        
        return $response;
    }
} 