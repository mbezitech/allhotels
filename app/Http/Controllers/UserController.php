<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Ensure user has permission to manage users
     */
    private function ensureCanManageUsers()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'You must be logged in to manage users.');
        }
        
        // Super admins can always manage users
        if ($user->isSuperAdmin()) {
            return;
        }
        
        // Check if user has users.manage permission for current hotel
        $hotelId = session('hotel_id');
        if (!$hotelId) {
            abort(403, 'Please select a hotel to manage users.');
        }
        
        if (!$user->hasPermission('users.manage', $hotelId)) {
            abort(403, 'You do not have permission to manage users.');
        }
    }
    
    /**
     * Ensure user has permission to view users
     */
    private function ensureCanViewUsers()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'You must be logged in to view users.');
        }
        
        // Super admins can always view users
        if ($user->isSuperAdmin()) {
            return;
        }
        
        // Check if user has users.view or users.manage permission for current hotel
        $hotelId = session('hotel_id');
        if (!$hotelId) {
            abort(403, 'Please select a hotel to view users.');
        }
        
        if (!$user->hasPermission('users.view', $hotelId) && !$user->hasPermission('users.manage', $hotelId)) {
            abort(403, 'You do not have permission to view users.');
        }
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $this->ensureCanViewUsers();
        
        $user = Auth::user();
        $hotelId = session('hotel_id');
        
        // Check if showing deleted users
        $showDeleted = $request->has('show_deleted') && $request->show_deleted == '1';
        
        // All users (including super admins) should only see users belonging to the current hotel
        // Users belong to a hotel if they:
        // 1. Have roles in this hotel, OR
        // 2. Are the owner of this hotel
        if ($hotelId) {
            $hotel = \App\Models\Hotel::find($hotelId);
            $hotelOwnerId = $hotel ? $hotel->owner_id : null;
            
            // Get users who have roles in the current hotel
            $userIds = DB::table('user_roles')
                ->where('hotel_id', $hotelId)
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            
            // Include hotel owner if they exist
            if ($hotelOwnerId) {
                $userIds[] = $hotelOwnerId;
            }
            
            // Include current user if they're viewing
            if (!$user->isSuperAdmin()) {
                $userIds[] = $user->id;
            }
            
            $userIds = array_unique($userIds);
            
            $query = User::whereIn('id', $userIds);
            
            if ($showDeleted) {
                $query->withTrashed();
            }
            
            $users = $query->with('ownedHotels')
                ->orderBy('is_super_admin', 'desc')
                ->orderBy('name')
                ->get();
        } else {
            // If no hotel context, super admins can see ALL users
            if ($user->isSuperAdmin()) {
                // No specific query needed to filter by hotel, just get all users
                // But we still need to build the query to handle sorting and relationships
                $query = User::query();
                
                if ($showDeleted) {
                    $query->withTrashed();
                }
                
                $users = $query->with('ownedHotels')
                    ->orderBy('is_super_admin', 'desc')
                    ->orderBy('name')
                    ->get();
            } else {
                $users = collect();
            }
        }
        
        // Count deleted users
        $deletedCount = 0;
        if ($hotelId) {
            $hotel = \App\Models\Hotel::find($hotelId);
            $hotelOwnerId = $hotel ? $hotel->owner_id : null;
            
            $userIds = DB::table('user_roles')
                ->where('hotel_id', $hotelId)
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            
            if ($hotelOwnerId) {
                $userIds[] = $hotelOwnerId;
            }
            
            if (!$user->isSuperAdmin()) {
                $userIds[] = $user->id;
            }
            
            $userIds = array_unique($userIds);
            
            $deletedCount = User::onlyTrashed()
                ->whereIn('id', $userIds)
                ->count();
        } else {
            if ($user->isSuperAdmin()) {
                $deletedCount = User::onlyTrashed()->count();
            }
        }
        
        return view('users.index', compact('users', 'showDeleted', 'deletedCount'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $this->ensureCanManageUsers();
        
        // Log access to user creation form
        logActivity('create_form_accessed', null, "Accessed user creation form", [
            'user_id' => auth()->id(),
            'hotel_id' => session('hotel_id'),
        ]);
        
        return view('users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $this->ensureCanManageUsers();
        
        $currentUser = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_super_admin' => 'nullable|boolean',
            'hotel_id' => 'nullable|exists:hotels,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Only super admins can create super admin users
        if ($currentUser->isSuperAdmin()) {
            $validated['is_super_admin'] = $request->has('is_super_admin');
        } else {
            $validated['is_super_admin'] = false; // Non-super admins cannot create super admins
        }
        
        // New users are active by default
        $validated['is_active'] = true;

        $newUser = User::create($validated);
        
        // Log user creation
        logActivity('created', $newUser, "Created user: {$newUser->name} ({$newUser->email})", [
            'user_id' => $newUser->id,
            'email' => $newUser->email,
            'is_super_admin' => $newUser->is_super_admin,
            'is_active' => $newUser->is_active,
        ]);
        
        // If hotel_id is provided and user is super admin, assign user as owner and give admin role
        if ($currentUser->isSuperAdmin() && $request->has('hotel_id') && $request->hotel_id) {
            $hotelId = $request->hotel_id;
            $hotel = \App\Models\Hotel::find($hotelId);
            
            if ($hotel) {
                // Update hotel owner
                $hotel->update(['owner_id' => $newUser->id]);
                
                // Assign admin role to the new owner
                $adminRole = \App\Models\Role::where('slug', 'admin')
                    ->where('hotel_id', $hotelId)
                    ->first();
                    
                if ($adminRole) {
                    // Check if role is already assigned
                    if (!$newUser->roles()->wherePivot('hotel_id', $hotelId)->wherePivot('role_id', $adminRole->id)->exists()) {
                        $newUser->roles()->attach($adminRole->id, ['hotel_id' => $hotelId]);
                    }
                }
                
                logActivity('updated', $newUser, "Assigned user as owner of hotel: {$hotel->name}", [
                    'hotel_id' => $hotelId,
                    'hotel_name' => $hotel->name,
                ]);
            }
        }

        // If coming from user-roles page, redirect back there
        if ($request->has('return_to') && $request->get('return_to') === 'user-roles') {
            $hotelId = session('hotel_id');
            if ($hotelId) {
                return redirect()->route('user-roles.create', ['hotel_id' => $hotelId])
                    ->with('success', 'User created successfully. You can now assign a role to this user.');
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $this->ensureCanManageUsers();
        
        // Log user viewing
        logActivity('viewed', $user, "Viewed user: {$user->name} ({$user->email})", [
            'user_id' => $user->id,
            'email' => $user->email,
            'is_super_admin' => $user->is_super_admin,
            'is_active' => $user->is_active,
        ]);
        
        $user->load('ownedHotels');
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $this->ensureCanManageUsers();
        
        // Log user edit form access
        logActivity('edit_form_accessed', $user, "Accessed edit form for user: {$user->name} ({$user->email})", [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $this->ensureCanManageUsers();
        
        $currentUser = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
            'is_super_admin' => 'nullable|boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle is_active - users with users.activate permission can change it
        if ($currentUser->isSuperAdmin() || $currentUser->hasPermission('users.activate', session('hotel_id'))) {
            // CRITICAL: Prevent users from disabling themselves through edit form
            if ($user->id === $currentUser->id) {
                // Never allow users to change their own is_active status
                if ($request->has('is_active')) {
                    $requestIsActive = $request->boolean('is_active');
                    // If trying to disable themselves, block it
                    if (!$requestIsActive || ($user->is_active && !$requestIsActive)) {
                        \Log::warning("User {$currentUser->id} ({$currentUser->email}) attempted to disable their own account via edit form");
                        return redirect()->route('users.edit', $user)
                            ->with('error', 'You cannot disable your own account. This action is not allowed for security reasons.');
                    }
                }
                // Remove is_active from validated data to prevent any changes
                unset($validated['is_active']);
            } else {
                // For other users, allow is_active changes
                $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : ($user->is_active ?? true);
            }
        } else {
            // Non-authorized users cannot change active status
            unset($validated['is_active']);
        }
        
        // Only super admins can change super admin status
        if ($currentUser->isSuperAdmin()) {
            $validated['is_super_admin'] = $request->has('is_super_admin');
        } else {
            // Non-super admins cannot change super admin status
            unset($validated['is_super_admin']);
        }

        // Capture old values for logging
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'is_super_admin' => $user->is_super_admin,
        ];
        
        $user->update($validated);
        
        // Capture new values for logging
        $user->refresh();
        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'is_super_admin' => $user->is_super_admin,
        ];
        
        // Log user update
        $changedFields = [];
        foreach ($oldValues as $key => $oldValue) {
            if (isset($newValues[$key]) && $oldValue != $newValues[$key]) {
                $changedFields[$key] = ['old' => $oldValue, 'new' => $newValues[$key]];
            }
        }
        
        if (!empty($changedFields)) {
            $fieldNames = implode(', ', array_keys($changedFields));
            logActivity('updated', $user, "Updated user: {$user->name} - Changed: {$fieldNames}", null, $oldValues, $newValues);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        $this->ensureCanManageUsers();
        
        $currentUser = Auth::user();
        
        // Prevent deleting yourself
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        
        // Non-super admins cannot delete super admin users
        if (!$currentUser->isSuperAdmin() && $user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete super admin users.');
        }

        // Capture user details before deletion
        $userDetails = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_super_admin' => $user->is_super_admin,
            'is_active' => $user->is_active,
        ];

        // Soft delete the user
        $user->delete();

        // Log the deletion
        logActivity('deleted', $user, "Deleted user: {$userDetails['name']} ({$userDetails['email']})", $userDetails);

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Activate a user account
     */
    public function activate(User $user)
    {
        $this->ensureCanActivateUsers();
        
        $currentUser = Auth::user();
        
        // Prevent activating yourself (you're already active if you can do this)
        // This is mainly for consistency, but also prevents any edge cases
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('info', 'You cannot change your own account status.');
        }
        
        $oldActive = $user->is_active;
        $user->update(['is_active' => true]);
        
        logActivity('updated', $user, "Activated user account: {$user->name}", null, ['is_active' => $oldActive], ['is_active' => true]);

        return redirect()->route('users.index')
            ->with('success', 'User enabled successfully.');
    }

    /**
     * Deactivate a user account
     */
    public function deactivate(User $user)
    {
        $this->ensureCanActivateUsers();
        
        $currentUser = Auth::user();
        
        // CRITICAL: Prevent deactivating yourself - this is a hard block
        if ($user->id === $currentUser->id) {
            \Log::warning("User {$currentUser->id} ({$currentUser->email}) attempted to disable their own account");
            return redirect()->route('users.index')
                ->with('error', 'You cannot disable your own account. This action is not allowed for security reasons.');
        }
        
        // Prevent deactivating super admins (unless current user is also super admin)
        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot disable super admin accounts.');
        }
        
        $oldActive = $user->is_active;
        $user->update(['is_active' => false]);
        
        logActivity('updated', $user, "Deactivated user account: {$user->name}", null, ['is_active' => $oldActive], ['is_active' => false]);

        return redirect()->route('users.index')
            ->with('success', 'User disabled successfully.');
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore($id)
    {
        $this->ensureCanManageUsers();
        
        $user = User::withTrashed()->findOrFail($id);
        
        $user->restore();

        // Log the restoration
        logActivity('restored', $user, "Restored user: {$user->name} ({$user->email})", [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User restored successfully.');
    }

    /**
     * Permanently delete a user
     */
    public function forceDelete($id)
    {
        $this->ensureCanManageUsers();
        
        $currentUser = Auth::user();
        $user = User::withTrashed()->findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot permanently delete your own account.');
        }
        
        // Non-super admins cannot permanently delete super admin users
        if (!$currentUser->isSuperAdmin() && $user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot permanently delete super admin users.');
        }

        // Capture user details before permanent deletion
        $userDetails = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_super_admin' => $user->is_super_admin,
            'is_active' => $user->is_active,
        ];

        // Permanently delete the user
        $user->forceDelete();

        // Log the permanent deletion
        logActivity('force_deleted', null, "Permanently deleted user: {$userDetails['name']} ({$userDetails['email']})", $userDetails);

        return redirect()->route('users.index')
            ->with('success', 'User permanently deleted.');
    }

    /**
     * Ensure user has permission to activate/deactivate users
     */
    private function ensureCanActivateUsers()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'You must be logged in to activate/deactivate users.');
        }
        
        // Super admins can always activate/deactivate users
        if ($user->isSuperAdmin()) {
            return;
        }
        
        // Check if user has users.activate permission for current hotel
        $hotelId = session('hotel_id');
        if (!$hotelId) {
            abort(403, 'Please select a hotel to manage users.');
        }
        
        if (!$user->hasPermission('users.activate', $hotelId)) {
            abort(403, 'You do not have permission to activate/deactivate users.');
        }
    }
}
