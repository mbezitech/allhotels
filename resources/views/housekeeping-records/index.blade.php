@extends('layouts.app')

@section('title', 'Housekeeping Records')
@section('page-title', 'Housekeeping Records')

@push('styles')
<style>
    .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-primary { background: #667eea; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: #333; }
    .filters { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .filter-group { display: inline-block; margin-right: 15px; margin-bottom: 10px; }
    .filter-group label { display: block; font-size: 12px; color: #666; margin-bottom: 5px; }
    .filter-group select, .filter-group input { padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f8f9fa; font-weight: 600; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
    .badge-dirty { background: #f8d7da; color: #721c24; }
    .badge-cleaning { background: #fff3cd; color: #856404; }
    .badge-clean { background: #d4edda; color: #155724; }
    .badge-inspected { background: #cce5ff; color: #004085; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Housekeeping Records</h2>
    @if(auth()->user()->hasPermission('housekeeping.manage') || auth()->user()->isSuperAdmin())
        <a href="{{ route('housekeeping-records.create') }}" class="btn btn-primary">Create Record</a>
    @endif
</div>

<div class="filters">
    <form method="GET" action="{{ route('housekeeping-records.index') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="filter-group">
            <label>Room</label>
            <select name="room_id" onchange="this.form.submit()">
                <option value="">All Rooms</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>Room {{ $room->room_number }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Area</label>
            <select name="area_id" onchange="this.form.submit()">
                <option value="">All Areas</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="cleaning_status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="dirty" {{ request('cleaning_status') == 'dirty' ? 'selected' : '' }}>Dirty</option>
                <option value="cleaning" {{ request('cleaning_status') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                <option value="clean" {{ request('cleaning_status') == 'clean' ? 'selected' : '' }}>Clean</option>
                <option value="inspected" {{ request('cleaning_status') == 'inspected' ? 'selected' : '' }}>Inspected</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Staff</label>
            <select name="assigned_to" onchange="this.form.submit()">
                <option value="">All Staff</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
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
        @if(request()->hasAny(['room_id', 'area_id', 'cleaning_status', 'assigned_to', 'date_from', 'date_to']))
            <div class="filter-group">
                <a href="{{ route('housekeeping-records.index') }}" class="btn" style="background: #95a5a6; color: white;">Clear</a>
            </div>
        @endif
    </form>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px;">{{ session('success') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Started</th>
                <th>Completed</th>
                <th>Duration</th>
                <th>Issues</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->room_id ? 'Room' : 'Area' }}</td>
                    <td>
                        @if($record->room)
                            <a href="{{ route('rooms.show', $record->room) }}">Room {{ $record->room->room_number }}</a>
                        @elseif($record->area)
                            {{ $record->area->name }}
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $record->cleaning_status }}">{{ ucfirst($record->cleaning_status) }}</span></td>
                    <td>{{ $record->assignedTo->name ?? '-' }}</td>
                    <td>{{ $record->started_at ? $record->started_at->format('M d, H:i') : '-' }}</td>
                    <td>{{ $record->completed_at ? $record->completed_at->format('M d, H:i') : '-' }}</td>
                    <td>{{ $record->duration_minutes ? $record->duration_minutes . ' min' : '-' }}</td>
                    <td>{{ $record->has_issues ? '⚠️ Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('housekeeping-records.show', $record) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View</a>
                        @if($record->cleaning_status == 'dirty')
                            <form action="{{ route('housekeeping-records.start', $record) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning" style="padding: 6px 12px; font-size: 12px;">Start</button>
                            </form>
                        @endif
                        @if($record->cleaning_status == 'cleaning')
                            <form action="{{ route('housekeeping-records.complete', $record) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">Complete</button>
                            </form>
                        @endif
                        @if($record->cleaning_status == 'clean')
                            <form action="{{ route('housekeeping-records.inspect', $record) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">Inspect</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align: center; color: #999; padding: 40px;">No records found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">{{ $records->links() }}</div>
</div>
@endsection

