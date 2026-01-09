<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class QueueLockTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Exception $e) {
            // Check if it's a lock timeout error
            if ($this->isLockTimeoutError($e)) {
                Log::error('Queue lock timeout detected', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => $request->user()?->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Return a more user-friendly error response
                return response()->json([
                    'error' => 'Service temporarily unavailable due to high load. Please try again in a moment.',
                    'retry_after' => 5 // Suggest retry after 5 seconds
                ], 503);
            }

            // Re-throw non-lock timeout errors
            throw $e;
        }
    }

    /**
     * Check if the error is a lock timeout error
     */
    private function isLockTimeoutError(Exception $e): bool
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        return (
            strpos($message, 'Lock wait timeout exceeded') !== false ||
            strpos($message, 'Deadlock') !== false ||
            strpos($message, 'Queue') !== false ||
            strpos($message, 'Job') !== false ||
            $code === 1205 || // Lock wait timeout
            $code === 1213    // Deadlock
        );
    }
}
