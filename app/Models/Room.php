<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_number',
        'room_type',
        'status',
        'floor',
        'capacity',
        'price_per_night',
        'description',
        'amenities',
    ];

    protected $casts = [
        'amenities' => 'array',
        'price_per_night' => 'decimal:2',
    ];

    /**
     * Get the hotel that owns the room
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all bookings for this room
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the room type (if using room_types table)
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type', 'slug');
    }

    /**
     * Check if room is available for given dates
     */
    public function isAvailableForDates(string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
    {
        $query = $this->bookings()
            ->where('hotel_id', $this->hotel_id)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q) use ($checkIn, $checkOut) {
                      $q->where('check_in', '<=', $checkIn)
                        ->where('check_out', '>=', $checkOut);
                  });
            })
            ->whereIn('status', ['pending', 'confirmed', 'checked_in']);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }
}
