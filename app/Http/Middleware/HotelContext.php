<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HotelContext
{
    /**
     * Handle an incoming request.
     * Ensures that every request has a hotel context in the session.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip hotel context check for login/logout routes and hotel management (super admin only)
        if ($request->routeIs('login') || $request->routeIs('logout') || $request->routeIs('hotels.*')) {
            return $next($request);
        }

        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if hotel_id exists in session
        $hotelId = session('hotel_id');
        $user = Auth::user();

        // Super admin can proceed without hotel context
        if ($user->isSuperAdmin()) {
            // Super admin can access all hotels, hotel_id is optional
            if ($hotelId) {
                $request->merge(['hotel_id' => $hotelId]);
            }
            return $next($request);
        }

        // Regular users must have a hotel context
        if (!$hotelId) {
            // If no hotel context, redirect to hotel selection or login
            return redirect()->route('login')
                ->with('error', 'Please select a hotel to continue.');
        }

        // Verify user has access to this hotel
        if (!$user->hasAccessToHotel($hotelId)) {
            // User doesn't have access to this hotel
            session()->forget('hotel_id');
            return redirect()->route('login')
                ->with('error', 'You do not have access to this hotel.');
        }

        // Make hotel_id available to all requests
        $request->merge(['hotel_id' => $hotelId]);

        return $next($request);
    }
}
