<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // Get all hotels for the dropdown
        $hotels = Hotel::all();

        return view('auth.login', compact('hotels'));
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'hotel_id' => 'nullable|exists:hotels,id',
        ]);

        $credentials = $request->only('email', 'password');
        $hotelId = $request->hotel_id;

        // Attempt to authenticate user
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials do not match our records.'),
            ]);
        }

        $user = Auth::user();

        // Super admin can login without hotel selection
        if ($user->isSuperAdmin()) {
            // If hotel selected, use it; otherwise set to null (super admin can access all)
            Session::put('hotel_id', $hotelId);
        } else {
            // Regular users must select a hotel
            if (!$hotelId) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'hotel_id' => 'Please select a hotel to continue.',
                ]);
            }

            // Check if user has access to the selected hotel
            $hasAccess = $user->roles()
                ->wherePivot('hotel_id', $hotelId)
                ->exists();

            if (!$hasAccess) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'hotel_id' => 'You do not have access to this hotel.',
                ]);
            }

            // Set hotel context in session
            Session::put('hotel_id', $hotelId);
        }

        // Regenerate session for security
        $request->session()->regenerate();

        // Log user login
        if ($hotelId) {
            $hotel = Hotel::find($hotelId);
            logActivity(
                'user_login',
                null,
                "User logged in: {$user->name}" . ($hotel ? " - Hotel: {$hotel->name}" : ''),
                ['hotel_id' => $hotelId, 'is_super_admin' => $user->isSuperAdmin()],
                null,
                null,
                false
            );
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $hotelId = session('hotel_id');
        
        // Log user logout before session is destroyed
        if ($user && $hotelId) {
            $hotel = Hotel::find($hotelId);
            logActivity(
                'user_logout',
                null,
                "User logged out: {$user->name}" . ($hotel ? " - Hotel: {$hotel->name}" : ''),
                ['hotel_id' => $hotelId, 'is_super_admin' => $user->isSuperAdmin()],
                null,
                null,
                false
            );
        }
        
        Auth::logout();
        Session::forget('hotel_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
