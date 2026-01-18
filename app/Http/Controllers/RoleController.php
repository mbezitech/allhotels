<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Get the current hotel ID from session
     */
    private function getHotelId()
    {
        $hotelId = session('hotel_id');
        
        if (!$hotelId && !auth()->user()->isSuperAdmin()) {
            abort(403, 'No hotel selected. Please select a hotel first.');
        }
        
        return $hotelId;
    }

    /**
     * Display a listing of roles (hotel-specific)
     */
    public function index()
    {
        $hotelId = $this->getHotelId();
        
        // Super admin can view all hotels' roles if hotel is selected
        // Regular users see only their hotel's roles
        $roles = Role::where('hotel_id', $hotelId)
            ->with('permissions')
            ->orderBy('name')
            ->get();
        
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $hotelId = $this->getHotelId();
        
        $permissions = Permission::where('hotel_id', $hotelId)
            ->orderBy('slug')
            ->get();
        
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $hotelId = $this->getHotelId();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Validate slug is unique for this hotel
        $exists = Role::where('hotel_id', $hotelId)
            ->where('slug', $validated['slug'])
            ->exists();
            
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['slug' => 'A role with this slug already exists for this hotel.']);
        }

        // Validate permissions belong to this hotel
        if (isset($validated['permissions'])) {
            $permissionCount = Permission::where('hotel_id', $hotelId)
                ->whereIn('id', $validated['permissions'])
                ->count();
                
            if ($permissionCount !== count($validated['permissions'])) {
                return back()
                    ->withInput()
                    ->withErrors(['permissions' => 'One or more selected permissions do not belong to this hotel.']);
            }
        }

        DB::transaction(function () use ($validated, $hotelId) {
            $role = Role::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'hotel_id' => $hotelId,
            ]);

            if (isset($validated['permissions'])) {
                // Detach all existing permissions first
                $role->permissions()->detach();
                
                // Attach permissions with hotel_id in pivot
                foreach ($validated['permissions'] as $permissionId) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permissionId,
                        'hotel_id' => $hotelId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $hotelId = $this->getHotelId();
        
        // Ensure role belongs to current hotel
        if ($role->hotel_id != $hotelId) {
            abort(403, 'This role does not belong to the selected hotel.');
        }
        
        $role->load('permissions');
        
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $hotelId = $this->getHotelId();
        
        // Ensure role belongs to current hotel
        if ($role->hotel_id != $hotelId) {
            abort(403, 'This role does not belong to the selected hotel.');
        }
        
        $permissions = Permission::where('hotel_id', $hotelId)
            ->orderBy('slug')
            ->get();
        $role->load('permissions');
        
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $hotelId = $this->getHotelId();
        
        // Ensure role belongs to current hotel
        if ($role->hotel_id != $hotelId) {
            abort(403, 'This role does not belong to the selected hotel.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Validate slug is unique for this hotel (excluding current role)
        $exists = Role::where('hotel_id', $hotelId)
            ->where('slug', $validated['slug'])
            ->where('id', '!=', $role->id)
            ->exists();
            
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['slug' => 'A role with this slug already exists for this hotel.']);
        }

        // Validate permissions belong to this hotel
        if (isset($validated['permissions'])) {
            $permissionCount = Permission::where('hotel_id', $hotelId)
                ->whereIn('id', $validated['permissions'])
                ->count();
                
            if ($permissionCount !== count($validated['permissions'])) {
                return back()
                    ->withInput()
                    ->withErrors(['permissions' => 'One or more selected permissions do not belong to this hotel.']);
            }
        }

        DB::transaction(function () use ($role, $validated, $hotelId) {
            $role->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
            ]);

            if (isset($validated['permissions'])) {
                // Detach all existing permissions first
                $role->permissions()->detach();
                
                // Attach permissions with hotel_id in pivot
                foreach ($validated['permissions'] as $permissionId) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permissionId,
                        'hotel_id' => $hotelId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                $role->permissions()->detach();
            }
        });

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        $hotelId = $this->getHotelId();
        
        // Ensure role belongs to current hotel
        if ($role->hotel_id != $hotelId) {
            abort(403, 'This role does not belong to the selected hotel.');
        }
        
        // Prevent owners from deleting admin role
        $user = auth()->user();
        $hotel = \App\Models\Hotel::find($hotelId);
        if ($hotel && $hotel->owner_id === $user->id && $role->slug === 'admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Hotel owners cannot delete the admin role.');
        }
        
        // Check if role is assigned to any users in this hotel
        $hasUsers = DB::table('user_roles')
            ->where('role_id', $role->id)
            ->where('hotel_id', $hotelId)
            ->exists();

        if ($hasUsers) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role that is assigned to users.');
        }

        // Detach permissions for this hotel
        DB::table('role_permissions')
            ->where('role_id', $role->id)
            ->where('hotel_id', $hotelId)
            ->delete();
            
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
