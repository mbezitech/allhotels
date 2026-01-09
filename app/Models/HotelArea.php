<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HotelArea extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($area) {
            if (empty($area->slug)) {
                $area->slug = Str::slug($area->name);
            }
        });
        static::updating(function ($area) {
            if ($area->isDirty('name') && empty($area->slug)) {
                $area->slug = Str::slug($area->name);
            }
        });
    }

    /**
     * Get the hotel that owns the area
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all housekeeping records for this area
     */
    public function housekeepingRecords(): HasMany
    {
        return $this->hasMany(HousekeepingRecord::class, 'area_id');
    }
}
