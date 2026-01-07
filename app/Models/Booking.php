<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'check_in',
        'check_out',
        'adults',
        'children',
        'total_amount',
        'status',
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
}
