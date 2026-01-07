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
        $hotelId = session('hotel_id');
        $hotel = Hotel::findOrFail($hotelId);
        
        // Get all users (they can be assigned roles)
        $users = User::all();
        
        // Get users who already have roles in this hotel
        $userIds = DB::table('user_roles')
            ->where('hotel_id', $hotelId)
            ->distinct()
            ->pluck('user_id')
            ->toArray();
        
        $usersWithRoles = User::whereIn('id', $userIds)
            ->get()
            ->map(function ($user) use ($hotelId) {
                $roleIds = DB::table('user_roles')
                    ->where('user_id', $user->id)
                    ->where('hotel_id', $hotelId)
                    ->pluck('role_id')
                    ->toArray();
                
                $user->roles = Role::whereIn('id', $roleIds)->get();
                return $user;
            });
        
        $roles = Role::all();
        
        return view('user-roles.create', compact('hotel', 'users', 'usersWithRoles', 'roles'));
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

        $hotelId = session('hotel_id');

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

        DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->where('hotel_id', $hotelId)
            ->delete();

        return redirect()->route('user-roles.create')
            ->with('success', 'Role removed successfully.');
    }
}
