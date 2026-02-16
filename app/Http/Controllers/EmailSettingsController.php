<?php

namespace App\Http\Controllers;

use App\Models\EmailSettings;
use App\Models\Hotel;
use App\Services\HotelMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class EmailSettingsController extends Controller
{
    /**
     * Display email settings for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // For super admins, allow hotel selection via request parameter
        if ($isSuperAdmin && $request->has('hotel_id')) {
            $hotelId = $request->get('hotel_id');
            session(['hotel_id' => $hotelId]);
        }
        
        // Super admins can view all hotels
        $allHotels = collect();
        if ($isSuperAdmin) {
            $allHotels = Hotel::orderBy('name')->get();
        }
        
        if (!$hotelId) {
            if ($isSuperAdmin && $allHotels->count() > 0) {
                // Show hotel selector for super admins
                return view('email-settings.select-hotel', compact('allHotels'));
            }
            return redirect()->route('dashboard')
                ->with('error', 'Please select a hotel to manage email settings.');
        }
        
        $hotel = Hotel::findOrFail($hotelId);
        $emailSettings = EmailSettings::firstOrNew(['hotel_id' => $hotelId]);
        
        return view('email-settings.index', compact('hotel', 'emailSettings', 'isSuperAdmin', 'allHotels'));
    }

    /**
     * Store or update email settings
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        
        // For super admins, allow hotel selection via request parameter
        if (auth()->user()->isSuperAdmin() && $request->has('hotel_id')) {
            $hotelId = $request->get('hotel_id');
            session(['hotel_id' => $hotelId]);
        }
        
        if (!$hotelId) {
            return back()->with('error', 'Please select a hotel.');
        }
        
        $validated = $request->validate([
            'enabled' => 'boolean',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'encryption' => 'nullable|in:tls,ssl,none',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'notification_email' => 'nullable|email|max:255',
            'notify_booking' => 'boolean',
            'booking_notification_email' => 'nullable|string',
            'notify_cancellation' => 'boolean',
            'cancellation_notification_email' => 'nullable|string',
            'notify_payment' => 'boolean',
            'payment_notification_email' => 'nullable|string',
        ]);
        
        // If enabled, require essential fields
        if (!empty($validated['enabled'])) {
            $request->validate([
                'smtp_host' => 'required|string|max:255',
                'smtp_port' => 'required|integer|min:1|max:65535',
                'smtp_username' => 'required|string|max:255',
                'from_email' => 'required|email|max:255',
            ]);
            
            // Only require password if it's being set (not empty)
            // Check if existing settings have a password
            $existing = EmailSettings::where('hotel_id', $hotelId)->first();
            if (empty($validated['smtp_password']) && (!$existing || !$existing->smtp_password)) {
                $request->validate([
                    'smtp_password' => 'required|string|max:255',
                ]);
            }
        }
        
        // If password is empty, don't include it in the update (keep existing)
        if (empty($validated['smtp_password'])) {
            unset($validated['smtp_password']);
        }
        
        // Add hotel_id to validated data
        $validated['hotel_id'] = $hotelId;
        
        // Get existing settings to preserve password if not updating
        $existing = EmailSettings::where('hotel_id', $hotelId)->first();
        
        if ($existing) {
            // Update existing
            $existing->fill($validated);
            $existing->save();
            $emailSettings = $existing;
        } else {
            // Create new
            $emailSettings = EmailSettings::create($validated);
        }
        
        return redirect()->route('email-settings.index')
            ->with('success', 'Email settings saved successfully.');
    }

    /**
     * Send test email
     */
    public function sendTestEmail(Request $request)
    {
        $hotelId = session('hotel_id');
        
        // For super admins, allow hotel selection via request parameter
        if (auth()->user()->isSuperAdmin() && $request->has('hotel_id')) {
            $hotelId = $request->get('hotel_id');
        }
        
        if (!$hotelId) {
            return back()->with('error', 'Please select a hotel.');
        }
        
        $emailSettings = EmailSettings::where('hotel_id', $hotelId)->first();
        
        if (!$emailSettings || !$emailSettings->isConfigured()) {
            return back()->with('error', 'Email settings are not configured or enabled for this hotel.');
        }
        
        $testEmail = $request->validate([
            'test_email' => 'required|email',
        ])['test_email'];
        
        try {
            // Use HotelMailService to send test email
            $success = HotelMailService::sendRaw(
                $hotelId,
                $testEmail,
                'Test Email from ' . $emailSettings->hotel->name,
                'This is a test email from ' . $emailSettings->hotel->name . ' email settings. If you received this email, your SMTP configuration is working correctly.'
            );
            
            if ($success) {
                return back()->with('success', "Test email sent successfully to {$testEmail}.");
            } else {
                return back()->with('error', 'Failed to send test email. Please check your email settings and try again.');
            }
        } catch (\Exception $e) {
            Log::error('Test email failed', [
                'hotel_id' => $hotelId,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
