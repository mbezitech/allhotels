<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'hotel_id',
        'room_number',
        'name',
        'room_type_id',
        'status',
        'cleaning_status',
        'floor',
        'capacity',
        'price_per_night',
        'description',
        'amenities',
        'images',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
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
     * Get the room type for this room
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
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

    /**
     * Boot method to log room status changes
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($room) {
            // Log room status changes
            if ($room->isDirty('status')) {
                $oldStatus = $room->getOriginal('status');
                $newStatus = $room->status;
                logActivity(
                    'room_status_changed',
                    $room,
                    "Room {$room->room_number} status changed from {$oldStatus} to {$newStatus}",
                    null,
                    ['status' => $oldStatus],
                    ['status' => $newStatus]
                );
            }

            // Log cleaning status changes
            if ($room->isDirty('cleaning_status')) {
                $oldCleaningStatus = $room->getOriginal('cleaning_status');
                $newCleaningStatus = $room->cleaning_status;
                
                $statusLabels = [
                    'dirty' => 'DIRTY',
                    'cleaning' => 'CLEANING',
                    'clean' => 'CLEAN',
                    'inspected' => 'READY'
                ];
                
                $oldLabel = $statusLabels[$oldCleaningStatus] ?? $oldCleaningStatus;
                $newLabel = $statusLabels[$newCleaningStatus] ?? $newCleaningStatus;
                
                logActivity(
                    'room_cleaning_status_changed',
                    $room,
                    "Room {$room->room_number} cleaning status changed from {$oldLabel} to {$newLabel}",
                    null,
                    ['cleaning_status' => $oldCleaningStatus],
                    ['cleaning_status' => $newCleaningStatus]
                );
            }
        });
    }
}
