<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // Ensure both role and permission belong to the hotel, and role_permissions has hotel_id
        $hasPermission = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->join('role_permissions', function($join) use ($hotelId) {
                $join->on('roles.id', '=', 'role_permissions.role_id')
                     ->where('role_permissions.hotel_id', '=', $hotelId);
            })
            ->join('permissions', function($join) use ($permission, $hotelId) {
                $join->on('role_permissions.permission_id', '=', 'permissions.id')
                     ->where('permissions.slug', '=', $permission)
                     ->where('permissions.hotel_id', '=', $hotelId);
            })
            ->where('user_roles.user_id', $user->id)
            ->where('user_roles.hotel_id', $hotelId)
            ->where('roles.hotel_id', $hotelId)
            ->exists();

        if (!$hasPermission) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
