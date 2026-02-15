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
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Check if showing deleted rooms
        $showDeleted = $request->has('show_deleted') && $request->show_deleted == '1';
        
        // Super admins can see all rooms, others only their hotel
        $query = Room::query();
        
        if ($showDeleted) {
            $query->withTrashed();
        }
        
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        }
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
            $selectedHotelId = $request->hotel_id;
        } else {
            $selectedHotelId = $hotelId;
        }
        
        $rooms = $query->with(['roomType', 'hotel'])
            ->withCount('bookings')
            ->orderBy('hotel_id')
            ->orderBy('room_number')
            ->get();

        // Get hotel for display (selected hotel for super admin, or current hotel)
        // For super admins, if no hotel selected, use first room's hotel or null
        if ($isSuperAdmin && !$selectedHotelId && $rooms->count() > 0) {
            $hotel = $rooms->first()->hotel;
        } else {
            $hotel = $selectedHotelId ? \App\Models\Hotel::find($selectedHotelId) : null;
        }
        
        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? \App\Models\Hotel::orderBy('name')->get() : collect();

        // Count deleted rooms
        $deletedCount = 0;
        $deletedQuery = Room::onlyTrashed();
        if (!$isSuperAdmin) {
            $deletedQuery->where('hotel_id', $hotelId);
        }
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $deletedQuery->where('hotel_id', $request->hotel_id);
        }
        $deletedCount = $deletedQuery->count();

        return view('rooms.index', compact('rooms', 'hotel', 'hotels', 'isSuperAdmin', 'selectedHotelId', 'showDeleted', 'deletedCount'));
    }

    /**
     * Show the form for creating a new room
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        
        // Log access to room creation form
        logActivity('create_form_accessed', null, "Accessed room creation form", [
            'user_id' => auth()->id(),
            'hotel_id' => $hotelId,
        ]);
        
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
            'name' => 'nullable|string|max:255',
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
        
        // Log room viewing
        logActivity('viewed', $room, "Viewed room: {$room->room_number}", [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'status' => $room->status,
            'cleaning_status' => $room->cleaning_status,
        ]);
        
        $room->load('bookings');
        
        return view('rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified room
     */
    public function edit(Room $room)
    {
        $this->authorizeHotel($room);
        
        // Log room edit form access
        logActivity('edit_form_accessed', $room, "Accessed edit form for room: {$room->room_number}", [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
        ]);
        
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
            'name' => 'nullable|string|max:255',
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

        // Check permission if trying to set cleaning_status to "inspected"
        if (isset($validated['cleaning_status']) && $validated['cleaning_status'] === 'inspected') {
            $user = auth()->user();
            if (!$user->isSuperAdmin() && !$user->hasPermission('housekeeping_records.inspect', $hotelId)) {
                return back()->withErrors(['cleaning_status' => 'You do not have permission to set cleaning status to "Inspected".']);
            }
        }

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
        
        // Check if user has permission to delete rooms
        $user = auth()->user();
        $hotelId = session('hotel_id') ?? $room->hotel_id;
        
        if (!$user->isSuperAdmin() && !$user->hasPermission('rooms.delete', $hotelId)) {
            abort(403, 'You do not have permission to delete rooms.');
        }

        // Check if room has active bookings
        $hasBookings = $room->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->exists();

        if ($hasBookings) {
            return redirect()->route('rooms.index')
                ->with('error', 'Cannot delete room with active bookings.');
        }

        // Capture room details before deletion
        $roomDetails = [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'status' => $room->status,
            'cleaning_status' => $room->cleaning_status,
            'capacity' => $room->capacity,
            'price_per_night' => $room->price_per_night,
        ];
        
        // Soft delete the room
        $room->delete();

        logActivity('deleted', $room, "Deleted room: {$roomDetails['room_number']}", $roomDetails);

        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

    /**
     * Restore a soft-deleted room
     */
    public function restore($id)
    {
        $room = Room::withTrashed()->findOrFail($id);
        $this->authorizeHotel($room);

        $room->restore();

        // Log the restoration
        logActivity('restored', $room, "Restored room: {$room->room_number}", [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
        ]);

        return redirect()->route('rooms.index')
            ->with('success', 'Room restored successfully.');
    }

    /**
     * Permanently delete a room
     */
    public function forceDelete($id)
    {
        $room = Room::withTrashed()->findOrFail($id);
        $this->authorizeHotel($room);

        // Check if room has any bookings (including soft-deleted)
        $hasBookings = $room->bookings()->withTrashed()->exists();

        if ($hasBookings) {
            return redirect()->route('rooms.index')
                ->with('error', 'Cannot permanently delete room with associated bookings.');
        }

        // Capture room details before permanent deletion
        $roomDetails = [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'status' => $room->status,
            'cleaning_status' => $room->cleaning_status,
            'capacity' => $room->capacity,
            'price_per_night' => $room->price_per_night,
        ];

        // Delete images if exist
        if ($room->images) {
            foreach ($room->images as $image) {
                if (Storage::disk('public')->exists(str_replace('/storage/', '', $image))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $image));
                }
            }
        }

        // Permanently delete the room
        $room->forceDelete();

        // Log the permanent deletion
        logActivity('force_deleted', null, "Permanently deleted room: {$roomDetails['room_number']}", $roomDetails);

        return redirect()->route('rooms.index')
            ->with('success', 'Room permanently deleted.');
    }

    /**
     * Ensure room belongs to current hotel
     */
    private function authorizeHotel(Room $room)
    {
        // Super admins can access any room
        if (auth()->user()->isSuperAdmin()) {
            return;
        }
        
        if ($room->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this room.');
        }
    }
}
