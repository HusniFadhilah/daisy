<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncUserRolesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            // Sync roles periodically (e.g., every 5 minutes)
            $lastSync = $request->user()->last_role_switch ?? $request->user()->updated_at;

            if (!$lastSync || $lastSync->diffInMinutes(now()) > 5) {
                $request->user()->syncRolesFromAssignments();
            }
        }

        return $next($request);
    }
}
