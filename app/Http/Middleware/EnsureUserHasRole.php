<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;
        
        // Check if user has any of the required roles
        foreach ($roles as $role) {
            $requiredRole = UserRole::from($role);
            if ($userRole === $requiredRole) {
                return $next($request);
            }
        }

        abort(403, 'Access denied. Insufficient permissions.');
    }
}