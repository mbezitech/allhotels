<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $this->ensureSuperAdmin();
        $hotels = Hotel::with('owner')->orderBy('name')->get();
        return view('hotels.index', compact('hotels'));
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
        if ($adminRole) {
            $owner = \App\Models\User::findOrFail($validated['owner_id']);
            // Check if role is already assigned
            if (!$owner->roles()->wherePivot('hotel_id', $hotel->id)->wherePivot('role_id', $adminRole->id)->exists()) {
                $owner->roles()->attach($adminRole->id, ['hotel_id' => $hotel->id]);
            }
        }
        
        logActivity('created', $hotel, "Created hotel: {$hotel->name}");

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

        $hotel->update($validated);
        
        logActivity('updated', $hotel, "Updated hotel: {$hotel->name}");

        return redirect()->route('hotels.index')
            ->with('success', 'Hotel updated successfully.');
    }

    /**
     * Remove the specified hotel
     */
    public function destroy(Hotel $hotel)
    {
        $this->ensureSuperAdmin();
        // Check if hotel has rooms or bookings
        if ($hotel->rooms()->exists() || $hotel->bookings()->exists()) {
            return redirect()->route('hotels.index')
                ->with('error', 'Cannot delete hotel: it has associated rooms or bookings.');
        }

        $hotelName = $hotel->name;
        $hotelId = $hotel->id;
        $hotel->delete();
        
        logActivity('deleted', null, "Deleted hotel: {$hotelName}", ['hotel_id' => $hotelId]);

        return redirect()->route('hotels.index')
            ->with('success', 'Hotel deleted successfully.');
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
