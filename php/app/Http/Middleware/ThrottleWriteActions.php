<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleWriteActions
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 10, $decayMinutes = 1): Response
    {
        // Only throttle POST, PUT, PATCH, DELETE
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return app(ThrottleRequests::class)
                ->handle($request, $next, $maxAttempts, $decayMinutes);
        }

        return $next($request);
    }
}
