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
    public function index()
    {
        $this->ensureCanViewUsers();
        
        $user = Auth::user();
        $hotelId = session('hotel_id');
        
        // Super admins see all users, others see users with roles in their hotel
        if ($user->isSuperAdmin()) {
            $users = User::with('ownedHotels')
                ->orderBy('is_super_admin', 'desc')
                ->orderBy('name')
                ->get();
        } else {
            // Get users who have roles in the current hotel
            $userIds = DB::table('user_roles')
                ->where('hotel_id', $hotelId)
                ->distinct()
                ->pluck('user_id');
            
            $users = User::whereIn('id', $userIds)
                ->orWhere('id', $user->id) // Include current user
                ->orderBy('name')
                ->get();
        }
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $this->ensureCanManageUsers();
        return view('users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $this->ensureCanManageUsers();
        
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_super_admin' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Only super admins can create super admin users
        if ($user->isSuperAdmin()) {
            $validated['is_super_admin'] = $request->has('is_super_admin');
        } else {
            $validated['is_super_admin'] = false; // Non-super admins cannot create super admins
        }
        
        // New users are active by default
        $validated['is_active'] = true;

        $user = User::create($validated);

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
        $user->load('ownedHotels');
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $this->ensureCanManageUsers();
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
            // Prevent users from deactivating themselves
            if ($user->id === $currentUser->id && !$request->has('is_active')) {
                return redirect()->route('users.edit', $user)
                    ->with('error', 'You cannot deactivate your own account.');
            }
            $validated['is_active'] = $request->has('is_active');
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

        $user->update($validated);

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

        $user->delete();

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
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot change your own account status.');
        }
        
        $user->update(['is_active' => true]);
        
        logActivity('updated', $user, "Activated user account: {$user->name}");

        return redirect()->route('users.index')
            ->with('success', 'User activated successfully.');
    }

    /**
     * Deactivate a user account
     */
    public function deactivate(User $user)
    {
        $this->ensureCanActivateUsers();
        
        $currentUser = Auth::user();
        
        // Prevent deactivating yourself
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }
        
        // Prevent deactivating super admins (unless current user is also super admin)
        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot deactivate super admin accounts.');
        }
        
        $user->update(['is_active' => false]);
        
        logActivity('updated', $user, "Deactivated user account: {$user->name}");

        return redirect()->route('users.index')
            ->with('success', 'User deactivated successfully.');
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
