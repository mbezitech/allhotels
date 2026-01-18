<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    /**
     * Show form to assign role to user for a hotel
     */
    public function create(Request $request)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $hotelId = session('hotel_id');
        
        // For super admins, allow hotel selection via request parameter
        if ($isSuperAdmin && $request->has('hotel_id')) {
            $hotelId = $request->get('hotel_id');
            session(['hotel_id' => $hotelId]);
        }
        
        // Super admins can view all hotels, others need hotel context
        $allHotels = collect();
        if ($isSuperAdmin) {
            $allHotels = Hotel::orderBy('name')->get();
            if (!$hotelId && $allHotels->count() > 0) {
                // If no hotel selected, show hotel selector
                return view('user-roles.select-hotel', compact('allHotels'));
            }
        }
        
        // If still no hotel_id, redirect or show error
        if (!$hotelId) {
            return redirect()->route('dashboard')
                ->with('error', 'Please select a hotel to manage user roles.');
        }
        
        $hotel = Hotel::findOrFail($hotelId);
        
        // Get users who have roles assigned in this hotel
        $userIdsWithRoles = DB::table('user_roles')
            ->where('hotel_id', $hotelId)
            ->distinct()
            ->pluck('user_id')
            ->toArray();
        
        // For the dropdown: Only show users who belong to this hotel
        // Users belong to a hotel if they:
        // 1. Have roles in this hotel, OR
        // 2. Are the owner of this hotel
        $hotelOwnerId = $hotel->owner_id;
        $userIdsInHotel = DB::table('user_roles')
            ->where('hotel_id', $hotelId)
            ->distinct()
            ->pluck('user_id')
            ->toArray();
        
        // Include hotel owner if they exist
        if ($hotelOwnerId) {
            $userIdsInHotel[] = $hotelOwnerId;
        }
        
        $userIdsInHotel = array_unique($userIdsInHotel);
        
        // Only show users who belong to this hotel (have roles or are owner)
        $users = User::where('is_super_admin', false)
            ->whereIn('id', $userIdsInHotel)
            ->orderBy('name')
            ->get();
        
        // Get users who already have roles in this hotel (for display table)
        // Include hotel owner even if they don't have a role yet
        // $hotelOwnerId is already defined above
        $displayUserIds = array_unique(array_merge($userIdsWithRoles, $hotelOwnerId ? [$hotelOwnerId] : []));
        
        $usersWithRoles = User::whereIn('id', $displayUserIds)
            ->where('is_super_admin', false)
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($hotelId) {
                $roleIds = DB::table('user_roles')
                    ->where('user_id', $user->id)
                    ->where('hotel_id', $hotelId)
                    ->pluck('role_id')
                    ->toArray();
                
                $user->roles = Role::where('hotel_id', $hotelId)
                    ->whereIn('id', $roleIds)
                    ->get();
                return $user;
            });
        
        // Get only roles for this hotel
        $roles = Role::where('hotel_id', $hotelId)->orderBy('name')->get();
        
        return view('user-roles.create', compact('hotel', 'users', 'usersWithRoles', 'roles', 'isSuperAdmin', 'allHotels'));
    }

    /**
     * Assign role to user for current hotel
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);
        
        // Verify role belongs to this hotel
        $role = Role::where('id', $validated['role_id'])
            ->where('hotel_id', $hotelId)
            ->first();
            
        if (!$role) {
            return back()->with('error', 'Selected role does not belong to this hotel.');
        }

        $hotelId = session('hotel_id');
        
        // For super admins, allow hotel selection via request parameter
        if (auth()->user()->isSuperAdmin() && $request->has('hotel_id')) {
            $hotelId = $request->get('hotel_id');
            session(['hotel_id' => $hotelId]);
        }
        
        if (!$hotelId) {
            return back()->with('error', 'Please select a hotel.');
        }

        // Check if user already has this role in this hotel
        $exists = DB::table('user_roles')
            ->where('user_id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->where('hotel_id', $hotelId)
            ->exists();

        if ($exists) {
            return back()->with('error', 'User already has this role for this hotel.');
        }

        DB::table('user_roles')->insert([
            'user_id' => $validated['user_id'],
            'role_id' => $validated['role_id'],
            'hotel_id' => $hotelId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('user-roles.create')
            ->with('success', 'Role assigned successfully.');
    }

    /**
     * Remove role from user for current hotel
     */
    public function destroy(Request $request, User $user, Role $role)
    {
        $hotelId = session('hotel_id');
        
        if (!$hotelId) {
            return back()->with('error', 'Please select a hotel.');
        }
        
        // Verify role belongs to this hotel
        if ($role->hotel_id != $hotelId) {
            return back()->with('error', 'This role does not belong to the selected hotel.');
        }

        DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->where('hotel_id', $hotelId)
            ->delete();

        return redirect()->route('user-roles.create')
            ->with('success', 'Role removed successfully.');
    }
}
