@extends('layouts.app')

@section('title', 'Tasks')
@section('page-title', 'Tasks')

@push('styles')
<style>
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
    }
    .btn-primary {
        background: #667eea;
        color: white;
    }
    .btn-edit {
        background: #3498db;
        color: white;
    }
    .btn-danger {
        background: #e74c3c;
        color: white;
    }
    .filters {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .filter-group {
        display: inline-block;
        margin-right: 15px;
        margin-bottom: 10px;
    }
    .filter-group label {
        display: block;
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
    }
    .filter-group select {
        padding: 8px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-in_progress { background: #d1ecf1; color: #0c5460; }
    .badge-completed { background: #d4edda; color: #155724; }
    .badge-cancelled { background: #f8d7da; color: #721c24; }
    .badge-low { background: #e2e3e5; color: #383d41; }
    .badge-medium { background: #d1ecf1; color: #0c5460; }
    .badge-high { background: #fff3cd; color: #856404; }
    .badge-urgent { background: #f8d7da; color: #721c24; }
    .badge-housekeeping { background: #e3f2fd; color: #1976d2; }
    .badge-maintenance { background: #fff3cd; color: #856404; }
    .badge-cleaning { background: #d1ecf1; color: #0c5460; }
    .overdue {
        color: #e74c3c;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <h2 style="color: #333; font-size: 24px;">All Tasks</h2>
    @if(auth()->user()->hasPermission('housekeeping.manage') || auth()->user()->isSuperAdmin())
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">Create Task</a>
    @endif
</div>

<div class="filters">
    <form method="GET" action="{{ route('tasks.index') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        @if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
        <div class="filter-group">
            <label>Hotel</label>
            <select name="hotel_id" onchange="this.form.submit()">
                <option value="">All Hotels</option>
                @foreach($hotels as $h)
                    <option value="{{ $h->id }}" {{ (isset($selectedHotelId) && $selectedHotelId == $h->id) || request('hotel_id') == $h->id ? 'selected' : '' }}>
                        {{ $h->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="filter-group">
            <label>Type</label>
            <select name="type" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="housekeeping" {{ request('type') == 'housekeeping' ? 'selected' : '' }}>Housekeeping</option>
                <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="cleaning" {{ request('type') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Priority</label>
            <select name="priority" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Room</label>
            <select name="room_id" onchange="this.form.submit()">
                <option value="">All Rooms</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                        {{ $room->room_number }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Assigned To</label>
            <select name="assigned_to" onchange="this.form.submit()">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if(request()->hasAny(['type', 'status', 'priority', 'room_id', 'assigned_to', 'hotel_id']))
            <div class="filter-group">
                <a href="{{ route('tasks.index') }}" class="btn" style="background: #95a5a6; color: white;">Clear Filters</a>
            </div>
        @endif
    </form>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                <th>Hotel</th>
                @endif
                <th>Title</th>
                <th>Type</th>
                <th>Room</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <td>
                        <strong style="color: #667eea;">{{ $task->hotel->name ?? 'Unknown Hotel' }}</strong>
                        @if($task->hotel && $task->hotel->address)
                            <div style="font-size: 11px; color: #999; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($task->hotel->address, 30) }}</div>
                        @endif
                    </td>
                    @endif
                    <td>
                        <strong>{{ $task->title }}</strong>
                        @if($task->isOverdue())
                            <span class="overdue" style="font-size: 11px; margin-left: 5px;">(Overdue)</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $task->type }}">
                            {{ ucfirst($task->type) }}
                        </span>
                    </td>
                    <td>
                        @if($task->room)
                            <a href="{{ route('rooms.show', $task->room) }}" style="color: #667eea;">
                                Room {{ $task->room->room_number }}
                            </a>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $task->priority }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $task->status }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td>
                        @if($task->assignedTo)
                            {{ $task->assignedTo->name }}
                        @else
                            <span style="color: #999;">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        @if($task->due_date)
                            {{ $task->due_date->format('M d, Y') }}
                            @if($task->isOverdue())
                                <span class="overdue">⚠️</span>
                            @endif
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('tasks.show', $task) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View</a>
                        @if(auth()->user()->hasPermission('housekeeping.manage') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-edit" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($isSuperAdmin) && $isSuperAdmin) ? '9' : '8' }}" style="text-align: center; color: #999; padding: 40px;">No tasks found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $tasks->links() }}
    </div>
</div>
@endsection

