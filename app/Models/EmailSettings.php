<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class EmailSettings extends Model
{
    protected $fillable = [
        'hotel_id',
        'enabled',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'encryption',
        'from_email',
        'from_name',
        'notification_email',
        'notify_booking',
        'booking_notification_email',
        'notify_cancellation',
        'cancellation_notification_email',
        'notify_payment',
        'payment_notification_email',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'smtp_port' => 'integer',
        'notify_booking' => 'boolean',
        'notify_cancellation' => 'boolean',
        'notify_payment' => 'boolean',
    ];

    /**
     * Get the hotel that owns these email settings
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get decrypted SMTP password
     */
    public function getDecryptedPassword(): ?string
    {
        if (!$this->smtp_password) {
            return null;
        }
        
        try {
            return Crypt::decryptString($this->smtp_password);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set encrypted SMTP password
     * Only encrypts if a new value is provided (not empty)
     * If empty, the existing password is kept (not updated)
     */
    public function setSmtpPasswordAttribute($value): void
    {
        // Only encrypt if a new value is provided (not empty)
        // If empty, don't update the password field (keep existing)
        if ($value !== null && $value !== '') {
            $this->attributes['smtp_password'] = Crypt::encryptString($value);
        }
        // If value is empty/null, don't set the attribute (Laravel will skip it in update)
    }

    /**
     * Check if email is configured and enabled
     */
    public function isConfigured(): bool
    {
        return $this->enabled 
            && $this->smtp_host 
            && $this->smtp_port 
            && $this->smtp_username 
            && $this->smtp_password 
            && $this->from_email;
    }
}
