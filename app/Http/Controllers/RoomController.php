<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms for current hotel
     */
    public function index()
    {
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)
            ->withCount('bookings')
            ->orderBy('room_number')
            ->get();

        return view('rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new room
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        $roomTypes = \App\Models\RoomType::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('rooms.create', compact('roomTypes'));
    }

    /**
     * Store a newly created room
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:255',
            'room_type' => 'required|string|max:255',
            'status' => 'required|in:available,occupied,maintenance,cleaning',
            'floor' => 'nullable|integer',
            'capacity' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
        ]);

        $hotelId = session('hotel_id');

        // Check if room number already exists for this hotel
        $exists = Room::where('hotel_id', $hotelId)
            ->where('room_number', $validated['room_number'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['room_number' => 'Room number already exists for this hotel.'])->withInput();
        }

        $validated['hotel_id'] = $hotelId;
        $room = Room::create($validated);

        logActivity('created', $room, "Created room {$room->room_number}");

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * Display the specified room
     */
    public function show(Room $room)
    {
        $this->authorizeHotel($room);
        
        $room->load('bookings');
        
        return view('rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified room
     */
    public function edit(Room $room)
    {
        $this->authorizeHotel($room);
        $hotelId = session('hotel_id');
        $roomTypes = \App\Models\RoomType::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('rooms.edit', compact('room', 'roomTypes'));
    }

    /**
     * Update the specified room
     */
    public function update(Request $request, Room $room)
    {
        $this->authorizeHotel($room);

        $validated = $request->validate([
            'room_number' => 'required|string|max:255',
            'room_type' => 'required|string|max:255',
            'status' => 'required|in:available,occupied,maintenance,cleaning',
            'floor' => 'nullable|integer',
            'capacity' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
        ]);

        $hotelId = session('hotel_id');

        // Check if room number already exists for this hotel (excluding current room)
        $exists = Room::where('hotel_id', $hotelId)
            ->where('room_number', $validated['room_number'])
            ->where('id', '!=', $room->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['room_number' => 'Room number already exists for this hotel.'])->withInput();
        }

        $room->update($validated);

        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified room
     */
    public function destroy(Room $room)
    {
        $this->authorizeHotel($room);

        // Check if room has active bookings
        $hasBookings = $room->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->exists();

        if ($hasBookings) {
            return redirect()->route('rooms.index')
                ->with('error', 'Cannot delete room with active bookings.');
        }

        $roomNumber = $room->room_number;
        $roomId = $room->id;
        $room->delete();

        logActivity('deleted', null, "Deleted room {$roomNumber}", ['room_id' => $roomId]);

        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

    /**
     * Ensure room belongs to current hotel
     */
    private function authorizeHotel(Room $room)
    {
        if ($room->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this room.');
        }
    }
}
