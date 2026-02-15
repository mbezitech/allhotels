<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Hotel extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'email',
        'owner_id',
        'is_active',
        'timezone',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    /**
     * Get email settings for this hotel
     */
    public function emailSettings(): HasOne
    {
        return $this->hasOne(EmailSettings::class);
    }

    /**
     * Get all expense categories for this hotel
     */
    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    /**
     * Get all expenses for this hotel
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($hotel) {
            if (empty($hotel->slug)) {
                $hotel->slug = static::generateUniqueSlug($hotel->name);
            }
        });
        
        static::updating(function ($hotel) {
            if ($hotel->isDirty('name') && empty($hotel->slug)) {
                $hotel->slug = static::generateUniqueSlug($hotel->name, $hotel->id);
            }
        });
    }

    /**
     * Generate a unique slug for the hotel
     */
    protected static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($excludeId, function ($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
