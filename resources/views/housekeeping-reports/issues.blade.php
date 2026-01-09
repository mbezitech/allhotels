@extends('layouts.app')

@section('title', 'Issues & Damages Report')
@section('page-title', 'Issues & Damages Report')

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
    .issues-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
        margin-top: 10px;
    }
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
    <h2 style="color: #333; font-size: 24px;">Issues & Damages Report</h2>
    <a href="{{ route('housekeeping-reports.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Reports</a>
</div>

@if($isSuperAdmin)
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.issues') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
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
        <form method="GET" action="{{ route('housekeeping-reports.issues') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
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
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Issues & Damages ({{ $issues->count() }} found)</h3>
    
    @forelse($issues as $issue)
        <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                <div>
                    <strong style="color: #333;">
                        @if($issue->room)
                            Room {{ $issue->room->room_number }}
                        @elseif($issue->area)
                            {{ $issue->area->name }}
                        @endif
                    </strong>
                    <span style="color: #666; margin-left: 10px;">
                        - Reported by {{ $issue->assignedTo->name ?? 'Unknown' }} on {{ $issue->created_at->format('M d, Y H:i') }}
                    </span>
                </div>
                <a href="{{ route('housekeeping-records.show', $issue) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View Details</a>
            </div>
            <div class="issues-box">
                <strong>⚠️ Issues Found:</strong>
                <p style="margin-top: 10px; margin-bottom: 0;">{{ $issue->issues_found }}</p>
            </div>
        </div>
    @empty
        <div style="text-align: center; color: #999; padding: 40px;">
            No issues or damages reported for this period
        </div>
    @endforelse
</div>
@endsection

