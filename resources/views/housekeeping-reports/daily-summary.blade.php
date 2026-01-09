@extends('layouts.app')

@section('title', 'Daily Housekeeping Summary')
@section('page-title', 'Daily Housekeeping Summary')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    .stat-card h3 {
        font-size: 32px;
        color: #667eea;
        margin: 0 0 10px 0;
    }
    .stat-card p {
        color: #666;
        margin: 0;
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
    .badge-dirty { background: #f8d7da; color: #721c24; }
    .badge-cleaning { background: #fff3cd; color: #856404; }
    .badge-clean { background: #d4edda; color: #155724; }
    .badge-inspected { background: #cce5ff; color: #004085; }
    .filter-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Daily Housekeeping Summary</h2>
    <a href="{{ route('housekeeping-reports.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Reports</a>
</div>

@if($isSuperAdmin)
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.daily-summary') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
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
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">Date</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
        </form>
    </div>
@else
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.daily-summary') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">Date</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
        </form>
    </div>
@endif

<div class="stats-grid">
    <div class="stat-card">
        <h3>{{ $stats['total_rooms_cleaned'] }}</h3>
        <p>Rooms Cleaned</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['total_areas_cleaned'] }}</h3>
        <p>Areas Cleaned</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['pending_tasks'] }}</h3>
        <p>Pending Tasks</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['completed_tasks'] }}</h3>
        <p>Completed Tasks</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['total_duration'] ? round($stats['total_duration'] / 60, 1) . 'h' : '0h' }}</h3>
        <p>Total Duration</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['issues_found'] }}</h3>
        <p>Issues Found</p>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Records for {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</h3>
    
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
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->room_id ? 'Room' : 'Area' }}</td>
                    <td>
                        @if($record->room)
                            Room {{ $record->room->room_number }}
                        @elseif($record->area)
                            {{ $record->area->name }}
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $record->cleaning_status }}">{{ ucfirst($record->cleaning_status) }}</span></td>
                    <td>{{ $record->assignedTo->name ?? '-' }}</td>
                    <td>{{ $record->started_at ? $record->started_at->format('H:i') : '-' }}</td>
                    <td>{{ $record->completed_at ? $record->completed_at->format('H:i') : '-' }}</td>
                    <td>{{ $record->duration_minutes ? $record->duration_minutes . ' min' : '-' }}</td>
                    <td>{{ $record->has_issues ? '⚠️ Yes' : 'No' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #999; padding: 40px;">No records found for this date</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

