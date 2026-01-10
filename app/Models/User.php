<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_super_admin',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all roles for the user (with hotel context via pivot)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('hotel_id')
            ->withTimestamps();
    }

    /**
     * Get hotels owned by this user
     */
    public function ownedHotels()
    {
        return $this->hasMany(Hotel::class, 'owner_id');
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin ?? false;
    }

    /**
     * Check if user has access to a specific hotel
     */
    public function hasAccessToHotel(int $hotelId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles()
            ->wherePivot('hotel_id', $hotelId)
            ->exists();
    }

    /**
     * Get hotels user has access to (via roles)
     */
    public function accessibleHotels()
    {
        if ($this->isSuperAdmin()) {
            return Hotel::all();
        }

        $hotelIds = $this->roles()
            ->distinct()
            ->pluck('hotel_id')
            ->unique()
            ->filter();

        return Hotel::whereIn('id', $hotelIds)->get();
    }

    /**
     * Check if user has a specific permission for a hotel
     */
    public function hasPermission(string $permissionSlug, ?int $hotelId = null): bool
    {
        // Super admins have all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Use session hotel_id if not provided
        if ($hotelId === null) {
            $hotelId = session('hotel_id');
        }

        if (!$hotelId) {
            return false;
        }

        // Check if user has a role in this hotel that has the permission
        return $this->roles()
            ->wherePivot('hotel_id', $hotelId)
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    /**
     * Get all permissions for user in a specific hotel
     */
    public function getPermissionsForHotel(?int $hotelId = null): \Illuminate\Support\Collection
    {
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        if ($hotelId === null) {
            $hotelId = session('hotel_id');
        }

        if (!$hotelId) {
            return collect();
        }

        return Permission::whereHas('roles', function ($query) use ($hotelId) {
            $query->whereHas('users', function ($q) use ($hotelId) {
                $q->where('user_id', $this->id)
                  ->where('hotel_id', $hotelId);
            });
        })->get();
    }
}
