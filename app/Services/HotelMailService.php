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
