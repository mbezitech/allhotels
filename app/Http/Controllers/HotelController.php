<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{
    /**
     * Ensure user is super admin
     */
    private function ensureSuperAdmin()
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only super admins can manage hotels.');
        }
    }

    /**
     * Display a listing of hotels
     */
    public function index(Request $request)
    {
        $this->ensureSuperAdmin();
        
        // Check if showing trashed hotels
        $showTrashed = $request->get('trashed', false);
        
        if ($showTrashed) {
            $hotels = Hotel::onlyTrashed()->with('owner')->orderBy('deleted_at', 'desc')->get();
        } else {
            $hotels = Hotel::with('owner')->orderBy('name')->get();
        }
        
        $trashedCount = Hotel::onlyTrashed()->count();
        
        return view('hotels.index', compact('hotels', 'showTrashed', 'trashedCount'));
    }

    /**
     * Show the form for creating a new hotel
     */
    public function create()
    {
        $this->ensureSuperAdmin();
        $owners = User::where('is_super_admin', false)->get();
        return view('hotels.create', compact('owners'));
    }

    /**
     * Store a newly created hotel
     */
    public function store(Request $request)
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hotels,name',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'owner_id' => 'required|exists:users,id',
        ]);

        $hotel = Hotel::create($validated);
        
        // Automatically assign admin role to the owner for this hotel
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        $owner = null;
        if ($adminRole) {
            $owner = \App\Models\User::findOrFail($validated['owner_id']);
            // Check if role is already assigned
            if (!$owner->roles()->wherePivot('hotel_id', $hotel->id)->wherePivot('role_id', $adminRole->id)->exists()) {
                $owner->roles()->attach($adminRole->id, ['hotel_id' => $hotel->id]);
            }
        }
        
        // Log creation with detailed information
        logActivity('created', $hotel, "Created hotel: {$hotel->name}" . ($owner ? " (Owner: {$owner->name})" : ''), null, null, [
            'name' => $hotel->name,
            'address' => $hotel->address,
            'phone' => $hotel->phone,
            'email' => $hotel->email,
            'owner_id' => $hotel->owner_id,
            'owner_name' => $owner ? $owner->name : null,
        ]);

        return redirect()->route('hotels.index')
            ->with('success', 'Hotel created successfully. Owner has been assigned the Admin role.');
    }

    /**
     * Display the specified hotel
     */
    public function show(Hotel $hotel)
    {
        $this->ensureSuperAdmin();
        $hotel->load('owner', 'rooms', 'bookings');
        
        // Log view activity
        logActivity('viewed', $hotel, "Viewed hotel details: {$hotel->name}");
        
        // Get statistics
        $stats = [
            'total_rooms' => $hotel->rooms()->count(),
            'total_bookings' => $hotel->bookings()->count(),
            'active_bookings' => $hotel->bookings()
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count(),
        ];

        return view('hotels.show', compact('hotel', 'stats'));
    }

    /**
     * Show the form for editing the specified hotel
     */
    public function edit(Hotel $hotel)
    {
        $this->ensureSuperAdmin();
        $owners = User::where('is_super_admin', false)->get();
        return view('hotels.edit', compact('hotel', 'owners'));
    }

    /**
     * Update the specified hotel
     */
    public function update(Request $request, Hotel $hotel)
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hotels,name,' . $hotel->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'owner_id' => 'required|exists:users,id',
        ]);

        // Capture old values before update
        $oldValues = [
            'name' => $hotel->name,
            'address' => $hotel->address,
            'phone' => $hotel->phone,
            'email' => $hotel->email,
            'owner_id' => $hotel->owner_id,
        ];
        
        $oldOwner = $hotel->owner;
        $oldOwnerName = $oldOwner ? $oldOwner->name : null;

        $hotel->update($validated);
        
        // Get new owner if changed
        $newOwner = null;
        $newOwnerName = null;
        if ($validated['owner_id'] != $oldValues['owner_id']) {
            $newOwner = \App\Models\User::findOrFail($validated['owner_id']);
            $newOwnerName = $newOwner->name;
            
            // Automatically assign admin role to the new owner for this hotel
            $adminRole = \App\Models\Role::where('slug', 'admin')->first();
            if ($adminRole) {
                // Check if role is already assigned
                if (!$newOwner->roles()->wherePivot('hotel_id', $hotel->id)->wherePivot('role_id', $adminRole->id)->exists()) {
                    $newOwner->roles()->attach($adminRole->id, ['hotel_id' => $hotel->id]);
                }
            }
        }
        
        // Prepare new values for logging
        $newValues = [
            'name' => $hotel->name,
            'address' => $hotel->address,
            'phone' => $hotel->phone,
            'email' => $hotel->email,
            'owner_id' => $hotel->owner_id,
            'owner_name' => $newOwnerName ?: $oldOwnerName,
        ];
        
        // Build description with changes
        $changes = [];
        if ($validated['name'] != $oldValues['name']) {
            $changes[] = "name: '{$oldValues['name']}' → '{$validated['name']}'";
        }
        if ($validated['address'] != $oldValues['address']) {
            $changes[] = "address changed";
        }
        if ($validated['phone'] != $oldValues['phone']) {
            $changes[] = "phone changed";
        }
        if ($validated['email'] != $oldValues['email']) {
            $changes[] = "email changed";
        }
        if ($validated['owner_id'] != $oldValues['owner_id']) {
            $changes[] = "owner: '{$oldOwnerName}' → '{$newOwnerName}'";
        }
        
        $description = "Updated hotel: {$hotel->name}";
        if (!empty($changes)) {
            $description .= " (" . implode(", ", $changes) . ")";
        }
        
        logActivity('updated', $hotel, $description, null, $oldValues, $newValues);

        return redirect()->route('hotels.index')
            ->with('success', 'Hotel updated successfully.');
    }

    /**
     * Remove the specified hotel (soft delete)
     */
    public function destroy(Hotel $hotel)
    {
        $this->ensureSuperAdmin();
        
        // With soft deletes, we can still delete hotels with rooms/bookings
        // but we'll show a warning message
        $hasRoomsOrBookings = $hotel->rooms()->exists() || $hotel->bookings()->exists();
        
        // Capture hotel details before deletion
        $hotelName = $hotel->name;
        $hotelId = $hotel->id;
        $hotelAddress = $hotel->address;
        $hotelPhone = $hotel->phone;
        $hotelEmail = $hotel->email;
        $ownerId = $hotel->owner_id;
        $ownerName = $hotel->owner ? $hotel->owner->name : null;
        
        try {
            DB::transaction(function () use ($hotel, $hotelName, $hotelId, $hotelAddress, $hotelPhone, $hotelEmail, $ownerId, $ownerName) {
                // Log deletion BEFORE soft deleting the hotel
                logActivity('deleted', $hotel, "Soft deleted hotel: {$hotelName}", [
                    'name' => $hotelName,
                    'address' => $hotelAddress,
                    'phone' => $hotelPhone,
                    'email' => $hotelEmail,
                    'owner_id' => $ownerId,
                    'owner_name' => $ownerName,
                ]);
                
                // Soft delete the hotel (sets deleted_at timestamp)
                $hotel->delete();
            });
            
            $message = $hasRoomsOrBookings 
                ? 'Hotel soft deleted successfully. The hotel can be restored later.'
                : 'Hotel deleted successfully.';
            
            return redirect()->route('hotels.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Failed to delete hotel: ' . $e->getMessage());
            return redirect()->route('hotels.index')
                ->with('error', 'Failed to delete hotel: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted hotel
     */
    public function restore($id)
    {
        $this->ensureSuperAdmin();
        
        $hotel = Hotel::onlyTrashed()->findOrFail($id);
        
        try {
            DB::transaction(function () use ($hotel) {
                $hotel->restore();
                
                // Log restoration
                logActivity('restored', $hotel, "Restored hotel: {$hotel->name}", [
                    'name' => $hotel->name,
                    'address' => $hotel->address,
                    'phone' => $hotel->phone,
                    'email' => $hotel->email,
                ]);
            });
            
            return redirect()->route('hotels.index')
                ->with('success', 'Hotel restored successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to restore hotel: ' . $e->getMessage());
            return redirect()->route('hotels.index', ['trashed' => true])
                ->with('error', 'Failed to restore hotel: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a hotel (force delete)
     */
    public function forceDelete($id)
    {
        $this->ensureSuperAdmin();
        
        $hotel = Hotel::onlyTrashed()->findOrFail($id);
        
        // Check if hotel has rooms or bookings - prevent permanent deletion if it does
        if ($hotel->rooms()->exists() || $hotel->bookings()->exists()) {
            return redirect()->route('hotels.index', ['trashed' => true])
                ->with('error', 'Cannot permanently delete hotel: it has associated rooms or bookings.');
        }

        // Capture hotel details before permanent deletion
        $hotelName = $hotel->name;
        $hotelId = $hotel->id;
        $hotelAddress = $hotel->address;
        $hotelPhone = $hotel->phone;
        $hotelEmail = $hotel->email;
        $ownerId = $hotel->owner_id;
        $ownerName = $hotel->owner ? $hotel->owner->name : null;
        
        try {
            DB::transaction(function () use ($hotel, $hotelName, $hotelId, $hotelAddress, $hotelPhone, $hotelEmail, $ownerId, $ownerName) {
                // Log permanent deletion BEFORE force deleting
                logActivity('force_deleted', null, "Permanently deleted hotel: {$hotelName}", [
                    'hotel_id' => $hotelId,
                    'name' => $hotelName,
                    'address' => $hotelAddress,
                    'phone' => $hotelPhone,
                    'email' => $hotelEmail,
                    'owner_id' => $ownerId,
                    'owner_name' => $ownerName,
                ]);
                
                // Permanently delete the hotel
                $hotel->forceDelete();
            });
            
            return redirect()->route('hotels.index', ['trashed' => true])
                ->with('success', 'Hotel permanently deleted.');
        } catch (\Exception $e) {
            \Log::error('Failed to permanently delete hotel: ' . $e->getMessage());
            return redirect()->route('hotels.index', ['trashed' => true])
                ->with('error', 'Failed to permanently delete hotel: ' . $e->getMessage());
        }
    }

    /**
     * Switch to a different hotel (super admin only)
     */
    public function switchHotel(Request $request, $hotel = null)
    {
        $this->ensureSuperAdmin();
        
        $hotelId = $request->input('hotel_id') ?? $hotel;
        
        if ($hotelId) {
            $hotel = Hotel::findOrFail($hotelId);
            session(['hotel_id' => $hotel->id]);
            logActivity('hotel_switched', $hotel, "Switched to hotel: {$hotel->name}", [
                'hotel_id' => $hotel->id,
            ]);
            return redirect()->back()->with('success', "Switched to {$hotel->name}");
        } else {
            // Clear hotel selection
            session()->forget('hotel_id');
            return redirect()->back()->with('success', "Hotel context cleared");
        }
    }
}
