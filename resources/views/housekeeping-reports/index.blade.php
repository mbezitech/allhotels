@extends('layouts.app')

@section('title', 'Housekeeping Reports')
@section('page-title', 'Housekeeping Reports')

@push('styles')
<style>
    .report-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .report-card h3 {
        color: #333;
        font-size: 20px;
        margin-bottom: 10px;
    }
    .report-card p {
        color: #666;
        margin-bottom: 20px;
    }
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        background: #667eea;
        color: white;
    }
    .btn:hover {
        background: #5568d3;
    }
    .filter-box {
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
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
</style>
@endpush

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Housekeeping Reports</h2>
    <p style="color: #666; margin-top: 5px;">View comprehensive housekeeping reports and analytics</p>
</div>

@if($isSuperAdmin)
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.index') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
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
        </form>
    </div>
@endif

<div class="grid-2">
    <div class="report-card">
        <h3>📊 Daily Summary</h3>
        <p>View daily housekeeping summary including rooms cleaned, areas cleaned, and pending tasks.</p>
        <form method="GET" action="{{ route('housekeeping-reports.daily-summary') }}" style="display: inline;">
            @if($isSuperAdmin && $selectedHotelId)
                <input type="hidden" name="hotel_id" value="{{ $selectedHotelId }}">
            @endif
            <div style="margin-bottom: 10px;">
                <label for="date" style="display: block; margin-bottom: 5px; font-size: 12px; color: #666;">Select Date:</label>
                <input type="date" name="date" value="{{ $dateFrom }}" required style="padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <button type="submit" class="btn">View Daily Summary</button>
        </form>
    </div>

    <div class="report-card">
        <h3>👥 Staff Performance</h3>
        <p>Analyze staff performance metrics including tasks completed, average duration, and issues found.</p>
        <form method="GET" action="{{ route('housekeeping-reports.staff-performance') }}" style="display: inline;">
            @if($isSuperAdmin && $selectedHotelId)
                <input type="hidden" name="hotel_id" value="{{ $selectedHotelId }}">
            @endif
            <div style="margin-bottom: 10px;">
                <label for="date_from" style="display: block; margin-bottom: 5px; font-size: 12px; color: #666;">From:</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" required style="padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px; width: 100%;">
            </div>
            <div style="margin-bottom: 10px;">
                <label for="date_to" style="display: block; margin-bottom: 5px; font-size: 12px; color: #666;">To:</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" required style="padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px; width: 100%;">
            </div>
            <button type="submit" class="btn">View Staff Performance</button>
        </form>
    </div>

    <div class="report-card">
        <h3>⏳ Pending Tasks</h3>
        <p>View all pending and overdue cleaning tasks that need attention.</p>
        <form method="GET" action="{{ route('housekeeping-reports.pending-tasks') }}" style="display: inline;">
            @if($isSuperAdmin && $selectedHotelId)
                <input type="hidden" name="hotel_id" value="{{ $selectedHotelId }}">
            @endif
            <button type="submit" class="btn">View Pending Tasks</button>
        </form>
    </div>

    <div class="report-card">
        <h3>⚠️ Issues & Damages</h3>
        <p>Review all issues, damages, and missing items reported by housekeeping staff.</p>
        <form method="GET" action="{{ route('housekeeping-reports.issues') }}" style="display: inline;">
            @if($isSuperAdmin && $selectedHotelId)
                <input type="hidden" name="hotel_id" value="{{ $selectedHotelId }}">
            @endif
            <div style="margin-bottom: 10px;">
                <label for="date_from" style="display: block; margin-bottom: 5px; font-size: 12px; color: #666;">From:</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" required style="padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px; width: 100%;">
            </div>
            <div style="margin-bottom: 10px;">
                <label for="date_to" style="display: block; margin-bottom: 5px; font-size: 12px; color: #666;">To:</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" required style="padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px; width: 100%;">
            </div>
            <button type="submit" class="btn">View Issues Report</button>
        </form>
    </div>
</div>
@endsection

