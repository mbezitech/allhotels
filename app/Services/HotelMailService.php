<?php

namespace App\Services;

use App\Models\EmailSettings;
use App\Models\Hotel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class HotelMailService
{
    /**
     * Send email using hotel-specific email settings
     * 
     * @param int $hotelId
     * @param string|array $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return bool
     */
    public static function send(int $hotelId, $to, string $subject, string $view, array $data = []): bool
    {
        $emailSettings = EmailSettings::where('hotel_id', $hotelId)->first();
        
        // If email is disabled or not configured, don't send
        if (!$emailSettings || !$emailSettings->isConfigured()) {
            Log::info('Email not sent - settings not configured', ['hotel_id' => $hotelId]);
            return false;
        }
        
        try {
            // Configure mail settings for this hotel
            self::configureMailForHotel($emailSettings);
            
            // Send email
            Mail::send($view, $data, function ($message) use ($emailSettings, $to, $subject) {
                $message->to($to)
                    ->subject($subject)
                    ->from($emailSettings->from_email, $emailSettings->from_name ?? $emailSettings->hotel->name);
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error('Email send failed', [
                'hotel_id' => $hotelId,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send raw email using hotel-specific email settings
     * 
     * @param int $hotelId
     * @param string|array $to
     * @param string $subject
     * @param string $content
     * @return bool
     */
    public static function sendRaw(int $hotelId, $to, string $subject, string $content): bool
    {
        $emailSettings = EmailSettings::where('hotel_id', $hotelId)->first();
        
        // If email is disabled or not configured, don't send
        if (!$emailSettings || !$emailSettings->isConfigured()) {
            Log::info('Email not sent - settings not configured', ['hotel_id' => $hotelId]);
            return false;
        }
        
        try {
            // Configure mail settings for this hotel
            self::configureMailForHotel($emailSettings);
            
            // Send email
            Mail::raw($content, function ($message) use ($emailSettings, $to, $subject) {
                $message->to($to)
                    ->subject($subject)
                    ->from($emailSettings->from_email, $emailSettings->from_name ?? $emailSettings->hotel->name);
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error('Email send failed', [
                'hotel_id' => $hotelId,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send notification email to hotel staff
     * 
     * @param int $hotelId
     * @param string $type ('booking', 'cancellation', 'payment')
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return bool
     */
    public static function sendNotification(int $hotelId, string $type, string $subject, string $view, array $data = []): bool
    {
        $emailSettings = EmailSettings::where('hotel_id', $hotelId)->first();
        
        // If no settings found, we can't send via hotel SMTP, but maybe we should log it
        if (!$emailSettings || !$emailSettings->isConfigured()) {
            Log::info("Hotel notification not sent - SMTP not configured", ['hotel_id' => $hotelId, 'type' => $type]);
            return false;
        }

        // Check if specific notification is enabled
        $notifyField = 'notify_' . $type;
        // Default to true if field doesn't exist (for backward compatibility if any)
        if (isset($emailSettings->$notifyField) && !$emailSettings->$notifyField) {
            return false;
        }

        // Determine recipients
        $recipientStr = null;
        $emailField = $type . '_notification_email';
        
        if (!empty($emailSettings->$emailField)) {
            $recipientStr = $emailSettings->$emailField;
        } elseif (!empty($emailSettings->notification_email)) {
            $recipientStr = $emailSettings->notification_email;
        } else {
            // Fallback to system admin email from config
            $recipientStr = config('mail.from.address');
        }

        if (!$recipientStr) {
            return false;
        }

        // Parse multiple emails (comma separated)
        $recipients = array_map('trim', explode(',', $recipientStr));
        $recipients = array_filter($recipients, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (empty($recipients)) {
            Log::warning("No valid recipients for hotel notification", ['hotel_id' => $hotelId, 'type' => $type, 'raw' => $recipientStr]);
            return false;
        }

        try {
            // Configure mail settings for this hotel
            self::configureMailForHotel($emailSettings);
            
            // Send email to all recipients
            Mail::send($view, $data, function ($message) use ($emailSettings, $recipients, $subject) {
                foreach ($recipients as $email) {
                    $message->to($email);
                }
                $message->subject($subject)
                    ->from($emailSettings->from_email, $emailSettings->from_name ?? $emailSettings->hotel->name);
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error("Hotel notification email failed", [
                'hotel_id' => $hotelId,
                'type' => $type,
                'recipients' => $recipients,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Configure mail settings for a specific hotel
     */
    private static function configureMailForHotel(EmailSettings $emailSettings): void
    {
        Config::set('mail.mailers.smtp.host', $emailSettings->smtp_host);
        Config::set('mail.mailers.smtp.port', $emailSettings->smtp_port);
        Config::set('mail.mailers.smtp.encryption', $emailSettings->encryption === 'none' ? null : $emailSettings->encryption);
        Config::set('mail.mailers.smtp.username', $emailSettings->smtp_username);
        Config::set('mail.mailers.smtp.password', $emailSettings->getDecryptedPassword());
        Config::set('mail.from.address', $emailSettings->from_email);
        Config::set('mail.from.name', $emailSettings->from_name ?? $emailSettings->hotel->name);
    }

    /**
     * Check if email is enabled for a hotel
     */
    public static function isEnabled(int $hotelId): bool
    {
        $emailSettings = EmailSettings::where('hotel_id', $hotelId)->first();
        return $emailSettings && $emailSettings->isConfigured();
    }
}
