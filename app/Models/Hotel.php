<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'owner_id',
    ];

    /**
     * Get the owner of the hotel
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get users with roles in this hotel
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Get all rooms for this hotel
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get all bookings for this hotel
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all extras for this hotel
     */
    public function extras(): HasMany
    {
        return $this->hasMany(Extra::class);
    }

    /**
     * Get all POS sales for this hotel
     */
    public function posSales(): HasMany
    {
        return $this->hasMany(PosSale::class);
    }

    /**
     * Get all room types for this hotel
     */
    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }
}
