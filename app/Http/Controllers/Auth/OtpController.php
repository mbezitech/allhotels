<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OtpController extends Controller
{
    /**
     * Show OTP verification form
     */
    public function show()
    {
        if (!Session::has('otp_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.otp');
    }

    /**
     * Verify OTP code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $userId = Session::get('otp_user_id');
        $hotelId = Session::get('otp_hotel_id');

        $otp = Otp::where('user_id', $userId)
            ->where('code', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP code.']);
        }

        // Mark OTP as used
        $otp->update(['used' => true]);

        // Login the user
        $user = User::find($userId);
        Auth::login($user);

        // Set hotel context
        Session::put('hotel_id', $hotelId);
        Session::forget(['otp_user_id', 'otp_hotel_id']);

        // Regenerate session
        $request->session()->regenerate();

        // Log user login
        $hotel = $hotelId ? \App\Models\Hotel::find($hotelId) : null;
        $logDescription = "User logged in: {$user->name} (Super Admin - OTP verified)";
        
        if ($hotel) {
            $logDescription .= " - Hotel: {$hotel->name}";
        } else {
            $logDescription .= " - No hotel selected (Global access)";
        }
        
        logActivity(
            'user_login',
            null,
            $logDescription,
            [
                'hotel_id' => $hotelId,
                'is_super_admin' => true,
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
     * Resend OTP code
     */
    public function resend(Request $request)
    {
        $userId = Session::get('otp_user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        
        // Invalidate old OTPs
        Otp::where('user_id', $userId)
            ->where('used', false)
            ->update(['used' => true]);

        // Generate new OTP
        $otpCode = rand(100000, 999999);
        Otp::create([
            'user_id' => $user->id,
            'code' => $otpCode,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        // Send OTP via email (with error handling)
        try {
            \Illuminate\Support\Facades\Mail::raw("Your new OTP code is: {$otpCode}", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Super Admin Login OTP');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP email failed: ' . $e->getMessage());
        }

        return back()->with('status', 'New OTP sent to your email.');
    }
}
