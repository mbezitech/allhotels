<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of room types for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admins can see all room types, others only their hotel
        $query = RoomType::query();
        if (!$isSuperAdmin) {
            if (!$hotelId) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a hotel to view room types.');
            }
            $query->where('hotel_id', $hotelId);
        }
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
            $selectedHotelId = $request->hotel_id;
        } else {
            $selectedHotelId = $hotelId;
        }
        
        $roomTypes = $query->with('hotel')
            ->orderBy('hotel_id')
            ->orderBy('name')
            ->get();

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? \App\Models\Hotel::orderBy('name')->get() : collect();

        return view('room-types.index', compact('roomTypes', 'hotels', 'isSuperAdmin', 'selectedHotelId'));
    }

    /**
     * Show the form for creating a new room type
     */
    public function create()
    {
        return view('room-types.create');
    }

    /**
     * Store a newly created room type
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'default_capacity' => 'required|integer|min:1',
            'amenities' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $hotelId = session('hotel_id');

        // Generate slug from name
        $slug = Str::slug($validated['name']);
        
        // Ensure slug is unique
        $counter = 1;
        $originalSlug = $slug;
        while (RoomType::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['hotel_id'] = $hotelId;
        $validated['slug'] = $slug;
        $validated['is_active'] = $request->has('is_active');

        // Check if name already exists for this hotel
        $exists = RoomType::where('hotel_id', $hotelId)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Room type name already exists for this hotel.'])->withInput();
        }

        $roomType = RoomType::create($validated);

        logActivity('created', $roomType, "Created room type: {$roomType->name}");

        return redirect()->route('room-types.index')
            ->with('success', 'Room type created successfully.');
    }

    /**
     * Display the specified room type
     */
    public function show(RoomType $roomType)
    {
        $this->authorizeHotel($roomType);
        $roomType->load('rooms');
        return view('room-types.show', compact('roomType'));
    }

    /**
     * Show the form for editing the specified room type
     */
    public function edit(RoomType $roomType)
    {
        $this->authorizeHotel($roomType);
        return view('room-types.edit', compact('roomType'));
    }

    /**
     * Update the specified room type
     */
    public function update(Request $request, RoomType $roomType)
    {
        $this->authorizeHotel($roomType);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'default_capacity' => 'required|integer|min:1',
            'amenities' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Check if name already exists for this hotel (excluding current)
        $exists = RoomType::where('hotel_id', $roomType->hotel_id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $roomType->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Room type name already exists for this hotel.'])->withInput();
        }

        $validated['is_active'] = $request->has('is_active');
        $roomType->update($validated);

        logActivity('updated', $roomType, "Updated room type: {$roomType->name}");

        return redirect()->route('room-types.index')
            ->with('success', 'Room type updated successfully.');
    }

    /**
     * Remove the specified room type
     */
    public function destroy(RoomType $roomType)
    {
        $this->authorizeHotel($roomType);

        // Check if room type has rooms
        if ($roomType->rooms()->exists()) {
            return redirect()->route('room-types.index')
                ->with('error', 'Cannot delete room type: it has associated rooms.');
        }

        $roomTypeName = $roomType->name;
        $roomType->delete();

        logActivity('deleted', null, "Deleted room type: {$roomTypeName}", ['room_type_id' => $roomType->id]);

        return redirect()->route('room-types.index')
            ->with('success', 'Room type deleted successfully.');
    }

    /**
     * Ensure room type belongs to current hotel
     */
    private function authorizeHotel(RoomType $roomType)
    {
        // Super admins can access any room type
        if (auth()->user()->isSuperAdmin()) {
            return;
        }
        
        if ($roomType->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this room type.');
        }
    }
}
