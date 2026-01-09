<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Booking extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_id',
        'booking_reference',
        'guest_name',
        'guest_email',
        'guest_phone',
        'check_in',
        'check_out',
        'adults',
        'children',
        'total_amount',
        'status',
        'source',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the hotel that owns the booking
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room for this booking
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get all payments for this booking
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * User who created the booking (internal dashboard)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if booking was created from public link
     */
    public function isPublic(): bool
    {
        return $this->source === 'public';
    }

    /**
     * Calculate number of nights
     */
    public function getNightsAttribute(): int
    {
        return $this->check_in->diffInDays($this->check_out);
    }

    /**
     * Get total paid amount
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount') ?? 0;
    }

    /**
     * Get outstanding balance
     */
    public function getOutstandingBalanceAttribute(): float
    {
        return max(0, $this->total_amount - $this->total_paid);
    }

    /**
     * Check if booking is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->outstanding_balance <= 0;
    }

    /**
     * Generate unique booking reference
     */
    public static function generateBookingReference(): string
    {
        do {
            $reference = 'BK' . strtoupper(substr(uniqid(), -8)) . rand(1000, 9999);
        } while (self::where('booking_reference', $reference)->exists());
        
        return $reference;
    }

    /**
     * Boot method to auto-generate booking reference and update room status
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = self::generateBookingReference();
            }
        });

        static::created(function ($booking) {
            // Log booking creation
            $isPublic = request()->routeIs('public.booking.*');
            $roomNumber = $booking->room ? $booking->room->room_number : 'N/A';
            logActivity(
                'created',
                $booking,
                $isPublic 
                    ? "Guest booking created: {$booking->guest_name} - Room {$roomNumber}"
                    : "Booking created: {$booking->guest_name} - Room {$roomNumber}",
                ['booking_reference' => $booking->booking_reference, 'is_guest_booking' => $isPublic]
            );
        });

        // Auto-update room cleaning status when booking is checked out
        static::updating(function ($booking) {
            $oldStatus = $booking->getOriginal('status');
            $newStatus = $booking->status;
            
            // Log status changes
            if ($booking->isDirty('status')) {
                $oldValues = ['status' => $oldStatus];
                $newValues = ['status' => $newStatus];
                
                if ($newStatus === 'checked_in') {
                    $roomNumber = $booking->room ? $booking->room->room_number : 'N/A';
                    logActivity('checked_in', $booking, "Guest checked in: {$booking->guest_name} - Room {$roomNumber}", null, $oldValues, $newValues);
                } elseif ($newStatus === 'checked_out') {
                    $roomNumber = $booking->room ? $booking->room->room_number : 'N/A';
                    logActivity('checked_out', $booking, "Guest checked out: {$booking->guest_name} - Room {$roomNumber}", null, $oldValues, $newValues);
                    
                    // Auto-update room cleaning status
                    $room = $booking->room;
                    if ($room) {
                        $oldRoomStatus = $room->cleaning_status;
                        $room->cleaning_status = 'dirty';
                        $room->save();
                        
                        // Log room cleaning status change (system action)
                        logSystemActivity(
                            'room_cleaning_status_changed',
                            $room,
                            "Room {$room->room_number} automatically marked as DIRTY after checkout",
                            null,
                            ['cleaning_status' => $oldRoomStatus],
                            ['cleaning_status' => 'dirty']
                        );
                    }
                } else {
                    // Other status changes
                    logActivity('updated', $booking, "Booking status changed from {$oldStatus} to {$newStatus} for {$booking->guest_name}", null, $oldValues, $newValues);
                }
            }
        });
    }

    /**
     * Expire stale pending bookings (e.g. pending payment) after given minutes.
     *
     * @param int $minutes
     * @return int Number of bookings expired
     */
    public static function expireStalePending(int $minutes = 10): int
    {
        $cutoff = now()->subMinutes($minutes);

        $stale = self::where('status', 'pending')
            ->where('created_at', '<=', $cutoff)
            ->with('room')
            ->get();

        $count = 0;

        foreach ($stale as $booking) {
            $oldStatus = $booking->status;
            $booking->status = 'cancelled';
            $booking->save();
            $count++;

            $roomNumber = $booking->room ? $booking->room->room_number : 'N/A';

            // Log as system action
            logSystemActivity(
                'booking_expired',
                $booking,
                "Booking auto-cancelled after remaining pending for more than {$minutes} minutes: {$booking->guest_name} - Room {$roomNumber}",
                [
                    'booking_id' => $booking->id,
                    'reason' => 'pending_timeout',
                    'minutes' => $minutes,
                ],
                ['status' => $oldStatus],
                ['status' => 'cancelled']
            );
        }

        return $count;
    }
}
