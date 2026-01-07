<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'slug',
        'description',
        'base_price',
        'default_capacity',
        'amenities',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'amenities' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the hotel that owns this room type
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all rooms of this type
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'room_type', 'slug');
    }
}
