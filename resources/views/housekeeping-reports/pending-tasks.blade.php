@extends('layouts.app')

@section('title', 'Pending Tasks Report')
@section('page-title', 'Pending Tasks Report')

@push('styles')
<style>
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
    .badge-dirty { background: #f8d7da; color: #721c24; }
    .badge-cleaning { background: #fff3cd; color: #856404; }
    .filter-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .overdue {
        color: #e74c3c;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Pending Tasks Report</h2>
    <a href="{{ route('housekeeping-reports.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Reports</a>
</div>

@if($isSuperAdmin)
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.pending-tasks') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">Hotel</label>
                <select name="hotel_id" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}" {{ $selectedHotelId == $hotel->id ? 'selected' : '' }}>
                            {{ $hotel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Pending and Overdue Cleaning Tasks ({{ $pending->count() }})</h3>
    
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pending as $record)
                <tr>
                    <td>{{ $record->room_id ? 'Room' : 'Area' }}</td>
                    <td>
                        @if($record->room)
                            <a href="{{ route('rooms.show', $record->room) }}" style="color: #667eea;">
                                Room {{ $record->room->room_number }}
                            </a>
                        @elseif($record->area)
                            {{ $record->area->name }}
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $record->cleaning_status }}">{{ ucfirst($record->cleaning_status) }}</span></td>
                    <td>{{ $record->assignedTo->name ?? 'Unassigned' }}</td>
                    <td>
                        {{ $record->created_at->format('M d, Y H:i') }}
                        @if($record->created_at->isPast() && $record->created_at->diffInDays(now()) > 1)
                            <span class="overdue">(Overdue)</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('housekeeping-records.show', $record) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999; padding: 40px;">No pending tasks found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

