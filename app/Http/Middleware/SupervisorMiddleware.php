<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SupervisorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and has the 'supervisor' role
        if (Auth::check() && Auth::user()->role === 'supervisor') {
            return $next($request);
        }

        // If not, abort with a 403 Forbidden error
        abort(403, 'Unauthorized Action');
    }
}