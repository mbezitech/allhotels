<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingRecord;
use App\Models\Room;
use App\Models\HotelArea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HousekeepingRecordController extends Controller
{
    /**
     * Display a listing of housekeeping records
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        
        $query = HousekeepingRecord::where('hotel_id', $hotelId)
            ->with('room', 'area', 'assignedTo', 'inspectedBy');

        // Filter by room
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by area
        if ($request->has('area_id') && $request->area_id) {
            $query->where('area_id', $request->area_id);
        }

        // Filter by status
        if ($request->has('cleaning_status') && $request->cleaning_status) {
            $query->where('cleaning_status', $request->cleaning_status);
        }

        // Filter by staff
        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by issues
        if ($request->has('has_issues') && $request->has_issues !== '') {
            $query->where('has_issues', $request->has_issues);
        }

        $records = $query->orderBy('created_at', 'desc')->paginate(20);
        $rooms = Room::where('hotel_id', $hotelId)->orderBy('room_number')->get();
        $areas = HotelArea::where('hotel_id', $hotelId)->where('is_active', true)->orderBy('name')->get();
        $users = User::all();

        return view('housekeeping-records.index', compact('records', 'rooms', 'areas', 'users'));
    }

    /**
     * Show the form for creating a new housekeeping record
     */
    public function create(Request $request)
    {
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)->orderBy('room_number')->get();
        $areas = HotelArea::where('hotel_id', $hotelId)->where('is_active', true)->orderBy('name')->get();
        $users = User::all();
        
        // Pre-select room if provided
        $roomId = $request->get('room_id');
        $areaId = $request->get('area_id');

        return view('housekeeping-records.create', compact('rooms', 'areas', 'users', 'roomId', 'areaId'));
    }

    /**
     * Store a newly created housekeeping record
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'area_id' => 'nullable|exists:hotel_areas,id',
            'assigned_to' => 'required|exists:users,id',
            'cleaning_status' => 'required|in:dirty,cleaning,clean,inspected',
            'notes' => 'nullable|string',
            'issues_found' => 'nullable|string',
        ]);

        // Must have either room_id or area_id, but not both
        if (empty($validated['room_id']) && empty($validated['area_id'])) {
            return back()->withErrors(['room_id' => 'Either a room or area must be selected.']);
        }
        if (!empty($validated['room_id']) && !empty($validated['area_id'])) {
            return back()->withErrors(['room_id' => 'Please select either a room OR an area, not both.']);
        }

        // Verify room belongs to hotel if provided
        if ($validated['room_id']) {
            $room = Room::findOrFail($validated['room_id']);
            if ($room->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this room.');
            }
        }

        // Verify area belongs to hotel if provided
        if ($validated['area_id']) {
            $area = HotelArea::findOrFail($validated['area_id']);
            if ($area->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this area.');
            }
        }

        $validated['hotel_id'] = $hotelId;
        $validated['has_issues'] = !empty($validated['issues_found']);

        // Set started_at if status is cleaning or beyond
        if (in_array($validated['cleaning_status'], ['cleaning', 'clean', 'inspected'])) {
            $validated['started_at'] = now();
        }

        // Set completed_at if status is clean or inspected
        if (in_array($validated['cleaning_status'], ['clean', 'inspected'])) {
            $validated['completed_at'] = now();
            if ($validated['started_at']) {
                $started = \Carbon\Carbon::parse($validated['started_at']);
                $completed = \Carbon\Carbon::parse($validated['completed_at']);
                $validated['duration_minutes'] = $started->diffInMinutes($completed);
            }
        }

        // Set inspected_at if status is inspected
        if ($validated['cleaning_status'] === 'inspected') {
            $validated['inspected_by'] = Auth::id();
            $validated['inspected_at'] = now();
        }

        $record = HousekeepingRecord::create($validated);

        // Update room cleaning status if applicable
        if ($record->room_id) {
            $room = $record->room;
            $oldCleaningStatus = $room->cleaning_status;
            $room->cleaning_status = $validated['cleaning_status'];
            $room->save();
            
            // Log room cleaning status change
            if ($oldCleaningStatus !== $validated['cleaning_status']) {
                logActivity('room_cleaning_status_changed', $room, "Room {$room->room_number} cleaning status changed to {$validated['cleaning_status']} via housekeeping record", null,
                    ['cleaning_status' => $oldCleaningStatus],
                    ['cleaning_status' => $validated['cleaning_status']]
                );
            }
        }

        logActivity('housekeeping_task_created', $record, "Created housekeeping record for " . ($record->room ? "Room {$record->room->room_number}" : $record->area->name), [
            'type' => $record->room_id ? 'room' : 'area',
            'assigned_to' => $record->assignedTo->name ?? 'Unknown',
            'status' => $validated['cleaning_status'],
        ]);

        return redirect()->route('housekeeping-records.index')
            ->with('success', 'Housekeeping record created successfully.');
    }

    /**
     * Display the specified housekeeping record
     */
    public function show(HousekeepingRecord $housekeepingRecord)
    {
        $this->authorizeHotel($housekeepingRecord);
        $housekeepingRecord->load('room', 'area', 'assignedTo', 'inspectedBy');
        return view('housekeeping-records.show', compact('housekeepingRecord'));
    }

    /**
     * Show the form for editing the specified housekeeping record
     */
    public function edit(HousekeepingRecord $housekeepingRecord)
    {
        $this->authorizeHotel($housekeepingRecord);
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)->orderBy('room_number')->get();
        $areas = HotelArea::where('hotel_id', $hotelId)->where('is_active', true)->orderBy('name')->get();
        $users = User::all();
        return view('housekeeping-records.edit', compact('housekeepingRecord', 'rooms', 'areas', 'users'));
    }

    /**
     * Update the specified housekeeping record
     */
    public function update(Request $request, HousekeepingRecord $housekeepingRecord)
    {
        $this->authorizeHotel($housekeepingRecord);
        $hotelId = session('hotel_id');

        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'area_id' => 'nullable|exists:hotel_areas,id',
            'assigned_to' => 'required|exists:users,id',
            'cleaning_status' => 'required|in:dirty,cleaning,clean,inspected',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'issues_found' => 'nullable|string',
        ]);

        // Must have either room_id or area_id, but not both
        if (empty($validated['room_id']) && empty($validated['area_id'])) {
            return back()->withErrors(['room_id' => 'Either a room or area must be selected.']);
        }
        if (!empty($validated['room_id']) && !empty($validated['area_id'])) {
            return back()->withErrors(['room_id' => 'Please select either a room OR an area, not both.']);
        }

        $validated['has_issues'] = !empty($validated['issues_found']);

        // Auto-set started_at if status is cleaning or beyond and not already set
        if (in_array($validated['cleaning_status'], ['cleaning', 'clean', 'inspected']) && !$housekeepingRecord->started_at) {
            $validated['started_at'] = $validated['started_at'] ?? now();
        }

        // Auto-set completed_at if status is clean or inspected and not already set
        if (in_array($validated['cleaning_status'], ['clean', 'inspected']) && !$housekeepingRecord->completed_at) {
            $validated['completed_at'] = $validated['completed_at'] ?? now();
        }

        // Calculate duration if both times are set
        if (!empty($validated['started_at']) && !empty($validated['completed_at'])) {
            $started = \Carbon\Carbon::parse($validated['started_at']);
            $completed = \Carbon\Carbon::parse($validated['completed_at']);
            $validated['duration_minutes'] = $started->diffInMinutes($completed);
        }

        // Auto-set inspected_by and inspected_at if status is inspected
        if ($validated['cleaning_status'] === 'inspected' && !$housekeepingRecord->inspected_by) {
            $validated['inspected_by'] = Auth::id();
            $validated['inspected_at'] = now();
        }

        $oldStatus = $housekeepingRecord->cleaning_status;
        $oldHasIssues = $housekeepingRecord->has_issues;
        
        $housekeepingRecord->update($validated);

        // Log issues reported
        if ($validated['has_issues'] && !$oldHasIssues && !empty($validated['issues_found'])) {
            logActivity('issues_reported', $housekeepingRecord, "Issues/damages reported for " . ($housekeepingRecord->room ? "Room {$housekeepingRecord->room->room_number}" : $housekeepingRecord->area->name), [
                'issues' => $validated['issues_found'],
                'reported_by' => $housekeepingRecord->assignedTo->name ?? 'Unknown',
            ]);
        }

        // Update room cleaning status if applicable
        if ($housekeepingRecord->room_id) {
            $room = $housekeepingRecord->room;
            $oldRoomStatus = $room->cleaning_status;
            if (isset($validated['cleaning_status']) && $oldRoomStatus !== $validated['cleaning_status']) {
                $room->cleaning_status = $validated['cleaning_status'];
                $room->save();
                
                logActivity('room_cleaning_status_changed', $room, "Room {$room->room_number} cleaning status changed to {$validated['cleaning_status']}", null,
                    ['cleaning_status' => $oldRoomStatus],
                    ['cleaning_status' => $validated['cleaning_status']]
                );
            }
        }

        if ($oldStatus !== $validated['cleaning_status']) {
            logActivity('housekeeping_status_changed', $housekeepingRecord, "Housekeeping record status changed from {$oldStatus} to {$validated['cleaning_status']}", null,
                ['cleaning_status' => $oldStatus],
                ['cleaning_status' => $validated['cleaning_status']]
            );
        } else {
            logActivity('updated', $housekeepingRecord, "Updated housekeeping record #{$housekeepingRecord->id}");
        }

        return redirect()->route('housekeeping-records.index')
            ->with('success', 'Housekeeping record updated successfully.');
    }

    /**
     * Start cleaning (update status to cleaning and set started_at)
     */
    public function startCleaning(HousekeepingRecord $housekeepingRecord)
    {
        $this->authorizeHotel($housekeepingRecord);
        
        $oldStatus = $housekeepingRecord->cleaning_status;
        $housekeepingRecord->update([
            'cleaning_status' => 'cleaning',
            'started_at' => now(),
        ]);

        if ($housekeepingRecord->room_id) {
            $room = $housekeepingRecord->room;
            $oldRoomStatus = $room->cleaning_status;
            $room->cleaning_status = 'cleaning';
            $room->save();
            
            logActivity('room_cleaning_status_changed', $room, "Room {$room->room_number} cleaning started", null,
                ['cleaning_status' => $oldRoomStatus],
                ['cleaning_status' => 'cleaning']
            );
        }

        logActivity('cleaning_started', $housekeepingRecord, "Started cleaning for " . ($housekeepingRecord->room ? "Room {$housekeepingRecord->room->room_number}" : $housekeepingRecord->area->name), null,
            ['cleaning_status' => $oldStatus],
            ['cleaning_status' => 'cleaning']
        );

        return back()->with('success', 'Cleaning started.');
    }

    /**
     * Complete cleaning (update status to clean and set completed_at)
     */
    public function completeCleaning(HousekeepingRecord $housekeepingRecord)
    {
        $this->authorizeHotel($housekeepingRecord);
        
        $startedAt = $housekeepingRecord->started_at ?? now();
        $completedAt = now();
        $duration = \Carbon\Carbon::parse($startedAt)->diffInMinutes($completedAt);

        $oldStatus = $housekeepingRecord->cleaning_status;
        $housekeepingRecord->update([
            'cleaning_status' => 'clean',
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'duration_minutes' => $duration,
        ]);

        if ($housekeepingRecord->room_id) {
            $room = $housekeepingRecord->room;
            $oldRoomStatus = $room->cleaning_status;
            $room->cleaning_status = 'clean';
            $room->save();
            
            logActivity('room_cleaning_status_changed', $room, "Room {$room->room_number} cleaning completed", null,
                ['cleaning_status' => $oldRoomStatus],
                ['cleaning_status' => 'clean']
            );
        }

        logActivity('cleaning_completed', $housekeepingRecord, "Completed cleaning for " . ($housekeepingRecord->room ? "Room {$housekeepingRecord->room->room_number}" : $housekeepingRecord->area->name) . " (Duration: {$duration} minutes)", [
            'duration_minutes' => $duration,
        ], ['cleaning_status' => $oldStatus], ['cleaning_status' => 'clean']);

        return back()->with('success', 'Cleaning completed.');
    }

    /**
     * Inspect cleaning (update status to inspected)
     */
    public function inspectCleaning(HousekeepingRecord $housekeepingRecord)
    {
        $this->authorizeHotel($housekeepingRecord);
        
        $oldStatus = $housekeepingRecord->cleaning_status;
        $housekeepingRecord->update([
            'cleaning_status' => 'inspected',
            'inspected_by' => Auth::id(),
            'inspected_at' => now(),
        ]);

        if ($housekeepingRecord->room_id) {
            $room = $housekeepingRecord->room;
            $oldRoomStatus = $room->cleaning_status;
            $room->cleaning_status = 'inspected';
            $room->save();
            
            logActivity('room_cleaning_status_changed', $room, "Room {$room->room_number} inspected and marked as READY", null,
                ['cleaning_status' => $oldRoomStatus],
                ['cleaning_status' => 'inspected']
            );
        }

        logActivity('room_inspected', $housekeepingRecord, "Inspected and approved cleaning for " . ($housekeepingRecord->room ? "Room {$housekeepingRecord->room->room_number}" : $housekeepingRecord->area->name), [
            'inspected_by' => Auth::user()->name,
        ], ['cleaning_status' => $oldStatus], ['cleaning_status' => 'inspected']);

        return back()->with('success', 'Cleaning inspected and approved.');
    }

    /**
     * Remove the specified housekeeping record
     */
    public function destroy(HousekeepingRecord $housekeepingRecord)
    {
        $this->authorizeHotel($housekeepingRecord);

        $recordId = $housekeepingRecord->id;
        $housekeepingRecord->delete();

        logActivity('deleted', null, "Deleted housekeeping record #{$recordId}", ['record_id' => $recordId]);

        return redirect()->route('housekeeping-records.index')
            ->with('success', 'Housekeeping record deleted successfully.');
    }

    /**
     * Ensure record belongs to current hotel
     */
    private function authorizeHotel(HousekeepingRecord $record)
    {
        if ($record->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this record.');
        }
    }
}
