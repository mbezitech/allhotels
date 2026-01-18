<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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

        // Check if user is the hotel owner - owners should always have access
        $hotel = \App\Models\Hotel::find($hotelId);
        if ($hotel && $hotel->owner_id === $this->id) {
            // If owner doesn't have a role assigned, assign admin role automatically
            $hasRole = $this->roles()
                ->wherePivot('hotel_id', $hotelId)
                ->where('roles.hotel_id', $hotelId)
                ->exists();
                
            if (!$hasRole) {
                // Auto-assign admin role to owner
                $adminRole = \App\Models\Role::where('slug', 'admin')
                    ->where('hotel_id', $hotelId)
                    ->first();
                    
                if ($adminRole) {
                    $this->roles()->attach($adminRole->id, ['hotel_id' => $hotelId]);
                }
            }
            
            return true;
        }

        // Check if user has a role assigned to this hotel
        // AND the role itself belongs to this hotel (roles are now hotel-specific)
        return $this->roles()
            ->wherePivot('hotel_id', $hotelId)
            ->where('roles.hotel_id', $hotelId) // Ensure role belongs to hotel
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

        // Get hotel IDs from user_roles pivot where role also belongs to that hotel
        $hotelIds = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $this->id)
            ->whereColumn('user_roles.hotel_id', 'roles.hotel_id') // Ensure pivot hotel_id matches role hotel_id
            ->distinct()
            ->pluck('user_roles.hotel_id')
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
        // Ensure role, permission, and role_permissions all belong to this hotel
        return DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->join('role_permissions', function($join) use ($hotelId) {
                $join->on('roles.id', '=', 'role_permissions.role_id')
                     ->where('role_permissions.hotel_id', '=', $hotelId);
            })
            ->join('permissions', function($join) use ($permissionSlug, $hotelId) {
                $join->on('role_permissions.permission_id', '=', 'permissions.id')
                     ->where('permissions.slug', '=', $permissionSlug)
                     ->where('permissions.hotel_id', '=', $hotelId);
            })
            ->where('user_roles.user_id', $this->id)
            ->where('user_roles.hotel_id', $hotelId)
            ->where('roles.hotel_id', $hotelId)
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

        // Get permissions that belong to this hotel and are assigned to user's roles
        return Permission::where('hotel_id', $hotelId)
            ->whereHas('roles', function ($query) use ($hotelId) {
                $query->where('roles.hotel_id', $hotelId)
                    ->whereHas('users', function ($q) use ($hotelId) {
                        $q->where('user_id', $this->id)
                          ->where('user_roles.hotel_id', $hotelId);
                    });
            })
            ->get();
    }
}
