@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@push('styles')
<style>
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
    .filter-group select, .filter-group input {
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
    .badge-created { background: #d4edda; color: #155724; }
    .badge-updated { background: #d1ecf1; color: #0c5460; }
    .badge-deleted { background: #f8d7da; color: #721c24; }
    .badge-checked_in { background: #cce5ff; color: #004085; }
    .badge-checked_out { background: #fff3cd; color: #856404; }
    .badge-report_viewed { background: #e3f2fd; color: #1976d2; }
    .badge-report_exported { background: #cce5ff; color: #004085; }
    .badge-housekeeping_task_assigned { background: #e3f2fd; color: #1976d2; }
    .badge-housekeeping_task_status_changed { background: #fff3cd; color: #856404; }
    .badge-housekeeping_status_changed { background: #d1ecf1; color: #0c5460; }
    .badge-room_status_changed { background: #e2e3e5; color: #383d41; }
    .badge-room_cleaning_status_changed { background: #d1ecf1; color: #0c5460; }
    .badge-housekeeping_task_created { background: #e3f2fd; color: #1976d2; }
    .badge-cleaning_started { background: #fff3cd; color: #856404; }
    .badge-cleaning_completed { background: #d4edda; color: #155724; }
    .badge-room_inspected { background: #cce5ff; color: #004085; }
    .badge-issues_reported { background: #f8d7da; color: #721c24; }
    .badge-user_login { background: #d4edda; color: #155724; }
    .badge-user_logout { background: #f8d7da; color: #721c24; }
    .badge-system { background: #6c757d; color: white; }
    .model-type {
        font-size: 11px;
        color: #666;
        font-family: monospace;
    }
    .system-badge {
        background: #6c757d;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Activity Logs</h2>
    <p style="color: #666; margin-top: 5px;">Track all system activities and user actions</p>
</div>

<div class="filters">
    <form method="GET" action="{{ route('activity-logs.index') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        @if($isSuperAdmin)
            <div class="filter-group">
                <label>Hotel</label>
                <select name="hotel_id" onchange="this.form.submit()">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}" {{ $selectedHotelId == $hotel->id ? 'selected' : '' }}>
                            {{ $hotel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        
        <div class="filter-group">
            <label>Action</label>
            <select name="action" onchange="this.form.submit()">
                <option value="">All Actions</option>
                @foreach($availableActions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $action)) }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="filter-group">
            <label>Subject Type</label>
            <select name="model_type" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="Room" {{ request('model_type') == 'Room' ? 'selected' : '' }}>Room</option>
                <option value="Booking" {{ request('model_type') == 'Booking' ? 'selected' : '' }}>Booking</option>
                <option value="HousekeepingRecord" {{ request('model_type') == 'HousekeepingRecord' ? 'selected' : '' }}>Housekeeping Record</option>
                <option value="Task" {{ request('model_type') == 'Task' ? 'selected' : '' }}>Task</option>
                <option value="HotelArea" {{ request('model_type') == 'HotelArea' ? 'selected' : '' }}>Hotel Area</option>
                @foreach($availableModelTypes as $type)
                    @if(!in_array(class_basename($type), ['Room', 'Booking', 'HousekeepingRecord', 'Task', 'HotelArea']))
                        <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                            {{ class_basename($type) }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        
        <div class="filter-group">
            <label>User</label>
            <select name="user_id" onchange="this.form.submit()">
                <option value="">All Users</option>
                <option value="system" {{ request('user_id') == 'system' ? 'selected' : '' }}>SYSTEM</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="filter-group">
            <label>Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()">
        </div>
        
        <div class="filter-group">
            <label>Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()">
        </div>
        
        @if(request()->hasAny(['hotel_id', 'action', 'model_type', 'user_id', 'date_from', 'date_to']))
            <div class="filter-group">
                <a href="{{ route('activity-logs.index') }}" class="btn" style="background: #95a5a6; color: white; padding: 8px 16px;">Clear Filters</a>
            </div>
        @endif
    </form>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Actor</th>
                <th>Action</th>
                <th>Subject</th>
                <th>Description</th>
                <th>IP Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                    <td>
                        @if($log->isSystemLog())
                            <span class="system-badge">SYSTEM</span>
                        @else
                            {{ $log->user->name ?? 'Unknown' }}
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $log->action }}">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </td>
                    <td>
                        @if($log->model_type)
                            <span class="model-type">{{ class_basename($log->model_type) }}</span>
                            @if($log->model_id)
                                #{{ $log->model_id }}
                            @endif
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>{{ $log->description }}</td>
                    <td>{{ $log->ip_address ?? '-' }}</td>
                    <td>
                        <a href="{{ route('activity-logs.show', $log) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #999; padding: 40px;">No activity logs found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $logs->links() }}
    </div>
</div>
@endsection
