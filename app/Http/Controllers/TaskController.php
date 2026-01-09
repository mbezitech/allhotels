<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Room;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        
        $query = Task::where('hotel_id', $hotelId)
            ->with('room', 'assignedTo', 'createdBy');

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned user
        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by room
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(20);
        $rooms = Room::where('hotel_id', $hotelId)->orderBy('room_number')->get();
        $users = User::all();

        return view('tasks.index', compact('tasks', 'rooms', 'users'));
    }

    /**
     * Show the form for creating a new task
     */
    public function create(Request $request)
    {
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)->orderBy('room_number')->get();
        $users = User::all();
        
        // Pre-select room if provided
        $roomId = $request->get('room_id');
        $bookingId = $request->get('booking_id');

        return view('tasks.create', compact('rooms', 'users', 'roomId', 'bookingId'));
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'type' => 'required|in:housekeeping,maintenance,cleaning',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Verify room belongs to hotel if provided
        if ($validated['room_id']) {
            $room = Room::findOrFail($validated['room_id']);
            if ($room->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this room.');
            }
        }

        // Verify booking belongs to hotel if provided
        if ($validated['booking_id']) {
            $booking = Booking::findOrFail($validated['booking_id']);
            if ($booking->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this booking.');
            }
        }

        $validated['hotel_id'] = $hotelId;
        $validated['created_by'] = Auth::id();

        $task = Task::create($validated);

        logActivity('housekeeping_task_created', $task, "Created {$task->type} task: {$task->title}" . ($task->room ? " - Room {$task->room->room_number}" : ''), [
            'type' => $task->type,
            'priority' => $task->priority,
            'assigned_to' => $task->assignedTo->name ?? 'Unassigned',
            'room_id' => $task->room_id,
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task
     */
    public function show(Task $task)
    {
        $this->authorizeHotel($task);
        $task->load('room', 'booking', 'assignedTo', 'createdBy');
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task
     */
    public function edit(Task $task)
    {
        $this->authorizeHotel($task);
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)->orderBy('room_number')->get();
        $users = User::all();
        return view('tasks.edit', compact('task', 'rooms', 'users'));
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeHotel($task);
        $hotelId = session('hotel_id');

        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'type' => 'required|in:housekeeping,maintenance,cleaning',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Verify room belongs to hotel if provided
        if ($validated['room_id']) {
            $room = Room::findOrFail($validated['room_id']);
            if ($room->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this room.');
            }
        }

        $oldStatus = $task->status;
        $oldAssignedTo = $task->assigned_to;
        
        // Auto-set completed_at when status changes to completed
        if ($validated['status'] == 'completed' && $task->status != 'completed') {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] != 'completed' && $task->status == 'completed') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        // Log status changes
        if ($oldStatus !== $validated['status']) {
            if ($validated['status'] === 'completed') {
                logActivity('housekeeping_task_completed', $task, "Housekeeping task completed: {$task->title}", null,
                    ['status' => $oldStatus],
                    ['status' => 'completed', 'completed_at' => now()->toDateTimeString()]
                );
            } else {
                logActivity('updated', $task, "Task status changed from {$oldStatus} to {$validated['status']}: {$task->title}", null,
                    ['status' => $oldStatus],
                    ['status' => $validated['status']]
                );
            }
        }
        
        // Log assignment changes
        if ($oldAssignedTo != $validated['assigned_to']) {
            $oldUser = $oldAssignedTo ? \App\Models\User::find($oldAssignedTo) : null;
            $newUser = $validated['assigned_to'] ? \App\Models\User::find($validated['assigned_to']) : null;
            logActivity('housekeeping_task_assigned', $task, "Task '{$task->title}' assigned to " . ($newUser ? $newUser->name : 'Unassigned'), [
                'old_assigned_to' => $oldUser ? $oldUser->name : 'Unassigned',
                'new_assigned_to' => $newUser ? $newUser->name : 'Unassigned',
            ]);
        }

        if ($oldStatus === $validated['status'] && $oldAssignedTo === $validated['assigned_to']) {
            logActivity('updated', $task, "Updated task: {$task->title}");
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task
     */
    public function destroy(Task $task)
    {
        $this->authorizeHotel($task);

        $taskTitle = $task->title;
        $task->delete();

        logActivity('deleted', null, "Deleted task: {$taskTitle}", ['task_id' => $task->id]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Ensure task belongs to current hotel
     */
    private function authorizeHotel(Task $task)
    {
        if ($task->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this task.');
        }
    }
}
