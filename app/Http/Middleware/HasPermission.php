<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasPermission
{
    /**
     * Handle an incoming request.
     * Checks if the authenticated user has the required permission for the current hotel.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The permission slug required
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();
        $hotelId = session('hotel_id');

        // Super admins bypass permission checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has the permission for the current hotel
        $hasPermission = $user->roles()
            ->wherePivot('hotel_id', $hotelId)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();

        if (!$hasPermission) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
