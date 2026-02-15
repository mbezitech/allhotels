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
        // Get all hotels for super admins, only active hotels for regular users
        // Note: This is shown before login, so we show all active hotels
        // Access control happens during login
        $hotels = Hotel::where('is_active', true)->orderBy('name')->get();

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

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact an administrator.',
            ]);
        }

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
            // This method also handles hotel owners automatically
            $hasAccess = $user->hasAccessToHotel($hotelId);

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
        $hotel = $hotelId ? Hotel::find($hotelId) : null;
        $logDescription = "User logged in: {$user->name}";
        
        if ($user->isSuperAdmin()) {
            $logDescription .= " (Super Admin)";
            if ($hotel) {
                $logDescription .= " - Hotel: {$hotel->name}";
            } else {
                $logDescription .= " - No hotel selected (Global access)";
            }
        } elseif ($hotel) {
            $logDescription .= " - Hotel: {$hotel->name}";
        }
        
        logActivity(
            'user_login',
            null,
            $logDescription,
            [
                'hotel_id' => $hotelId,
                'is_super_admin' => $user->isSuperAdmin(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ],
            null,
            null,
            false
        );

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
        if ($user) {
            $hotel = $hotelId ? Hotel::find($hotelId) : null;
            $logDescription = "User logged out: {$user->name}";
            
            if ($user->isSuperAdmin()) {
                $logDescription .= " (Super Admin)";
                if ($hotel) {
                    $logDescription .= " - Hotel: {$hotel->name}";
                } else {
                    $logDescription .= " - No hotel selected";
                }
            } elseif ($hotel) {
                $logDescription .= " - Hotel: {$hotel->name}";
            }
            
            logActivity(
                'user_logout',
                null,
                $logDescription,
                [
                    'hotel_id' => $hotelId,
                    'is_super_admin' => $user->isSuperAdmin(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
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
