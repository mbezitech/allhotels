@extends('layouts.app')

@section('title', 'Staff Performance Report')
@section('page-title', 'Staff Performance Report')

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
    .filter-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .performance-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Staff Performance Report</h2>
    <a href="{{ route('housekeeping-reports.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Reports</a>
</div>

@if($isSuperAdmin)
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.staff-performance') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
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
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
        </form>
    </div>
@else
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.staff-performance') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
        </form>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Staff Performance ({{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }})</h3>
    
    <table>
        <thead>
            <tr>
                <th>Staff Member</th>
                <th>Total Tasks</th>
                <th>Completed</th>
                <th>Completion Rate</th>
                <th>Avg Duration</th>
                <th>Total Duration</th>
                <th>Issues Found</th>
            </tr>
        </thead>
        <tbody>
            @forelse($performance as $perf)
                <tr>
                    <td><strong>{{ $perf->assignedTo->name ?? 'Unknown' }}</strong></td>
                    <td>{{ $perf->total_tasks }}</td>
                    <td>{{ $perf->completed_tasks }}</td>
                    <td>{{ $perf->total_tasks > 0 ? round(($perf->completed_tasks / $perf->total_tasks) * 100, 1) : 0 }}%</td>
                    <td>{{ $perf->avg_duration ? round($perf->avg_duration, 1) . ' min' : '-' }}</td>
                    <td>{{ $perf->total_duration ? round($perf->total_duration / 60, 1) . 'h' : '-' }}</td>
                    <td>{{ $perf->issues_count ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #999; padding: 40px;">No performance data found for this period</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

