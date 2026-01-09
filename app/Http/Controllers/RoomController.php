<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('rooms')->where(fn ($query) => $query->where('hotel_id', $hotelId))],
            'room_type_id' => ['required', 'exists:room_types,id', \Illuminate\Validation\Rule::exists('room_types', 'id')->where(fn ($query) => $query->where('hotel_id', $hotelId))],
            'status' => 'required|in:available,occupied,maintenance,cleaning',
            'floor' => 'nullable|integer',
            'capacity' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $validated['hotel_id'] = $hotelId;
        
        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('rooms', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }
        $validated['images'] = $imagePaths;

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
        $hotelId = session('hotel_id');

        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('rooms')->where(fn ($query) => $query->where('hotel_id', $hotelId))->ignore($room->id)],
            'room_type_id' => ['required', 'exists:room_types,id', \Illuminate\Validation\Rule::exists('room_types', 'id')->where(fn ($query) => $query->where('hotel_id', $hotelId))],
            'status' => 'required|in:available,occupied,maintenance,cleaning',
            'cleaning_status' => 'nullable|in:dirty,cleaning,clean,inspected',
            'floor' => 'nullable|integer',
            'capacity' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string',
        ]);

        // Handle image uploads and existing images
        $imagePaths = $request->input('existing_images', []);
        
        // Add new uploaded images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('rooms', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }
        
        $validated['images'] = $imagePaths;

        // Track changes for logging
        $oldStatus = $room->status;
        $oldCleaningStatus = $room->cleaning_status;
        $oldValues = [
            'status' => $oldStatus,
            'cleaning_status' => $oldCleaningStatus,
        ];
        $newValues = [
            'status' => $validated['status'],
            'cleaning_status' => $validated['cleaning_status'] ?? $oldCleaningStatus,
        ];

        $room->update($validated);

        // Log status changes
        if ($oldStatus !== $validated['status']) {
            logActivity('room_status_changed', $room, "Room {$room->room_number} status changed from {$oldStatus} to {$validated['status']}", null, 
                ['status' => $oldStatus], 
                ['status' => $validated['status']]
            );
        }
        
        // Log cleaning status changes if cleaning_status field exists in validated
        if (isset($validated['cleaning_status']) && $oldCleaningStatus !== $validated['cleaning_status']) {
            logActivity('room_cleaning_status_changed', $room, "Room {$room->room_number} cleaning status changed from {$oldCleaningStatus} to {$validated['cleaning_status']}", null,
                ['cleaning_status' => $oldCleaningStatus],
                ['cleaning_status' => $validated['cleaning_status']]
            );
        } else {
            logActivity('updated', $room, "Updated room {$room->room_number}");
        }

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
