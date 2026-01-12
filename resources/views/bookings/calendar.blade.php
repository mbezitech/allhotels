@extends('layouts.app')

@section('title', 'Booking Calendar')
@section('page-title', 'Booking Calendar')

@push('styles')
<style>
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .calendar-nav {
        display: flex;
        gap: 10px;
        align-items: center;
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
        opacity: 0.9;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #e0e0e0;
    }
    .calendar-day {
        background: white;
        min-height: 120px;
        padding: 8px;
        position: relative;
    }
    .calendar-day.other-month {
        background: #f8f9fa;
        color: #999;
    }
    .calendar-day.today {
        background: #e3f2fd;
        font-weight: bold;
    }
    .day-number {
        font-size: 14px;
        margin-bottom: 4px;
    }
    .booking-item {
        font-size: 11px;
        padding: 2px 4px;
        margin: 2px 0;
        border-radius: 3px;
        color: white;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        border-left: 3px solid rgba(255, 255, 255, 0.5);
    }
    .booking-item:hover {
        opacity: 0.9;
        transform: translateX(2px);
        transition: all 0.2s;
    }
    .booking-item.confirmed {
        background: #28a745;
    }
    .booking-item.pending {
        background: #ffc107;
        color: #333;
    }
    .booking-item.checked_in {
        background: #17a2b8;
    }
    .booking-item.checked_out {
        background: #6c757d;
    }
    .booking-item.cancelled {
        background: #dc3545;
    }
    .status-badge {
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        margin-right: 4px;
        opacity: 0.9;
    }
    .pending-payment-indicator {
        display: inline-block;
        font-size: 10px;
        margin-left: 4px;
        font-weight: bold;
        color: #ffc107;
        text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    }
    .booking-item.has-pending-payment {
        border-left: 3px solid #ffc107;
        box-shadow: 0 0 3px rgba(255, 193, 7, 0.5);
    }
    .weekday-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #e0e0e0;
        margin-bottom: 1px;
    }
    .weekday {
        background: #667eea;
        color: white;
        padding: 10px;
        text-align: center;
        font-weight: 600;
    }
    .calendar-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .status-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        border-left: 3px solid rgba(255, 255, 255, 0.5);
    }
    .legend-color.confirmed { background: #28a745; }
    .legend-color.pending { background: #ffc107; }
    .legend-color.checked_in { background: #17a2b8; }
    .legend-color.checked_out { background: #6c757d; }
    .legend-color.cancelled { background: #dc3545; }
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div class="calendar-actions">
        <a href="{{ route('bookings.index') }}" class="btn" style="background: #95a5a6;">List View</a>
        @if(auth()->user()->hasPermission('bookings.create') || auth()->user()->isSuperAdmin())
            <a href="{{ route('bookings.create') }}" class="btn">New Booking</a>
        @endif
    </div>

    <div class="status-legend">
        <div class="legend-item">
            <div class="legend-color confirmed"></div>
            <span><strong>Confirmed</strong> - Booking confirmed</span>
        </div>
        <div class="legend-item">
            <div class="legend-color pending"></div>
            <span><strong>Pending</strong> - Awaiting confirmation</span>
        </div>
        <div class="legend-item">
            <div class="legend-color checked_in"></div>
            <span><strong>Checked In</strong> - Guest currently staying</span>
        </div>
        <div class="legend-item">
            <div class="legend-color checked_out"></div>
            <span><strong>Checked Out</strong> - Stay completed</span>
        </div>
        <div class="legend-item">
            <div class="legend-color cancelled"></div>
            <span><strong>Cancelled</strong> - Booking cancelled</span>
        </div>
        <div class="legend-item">
            <span style="font-size: 14px; color: #ffc107; font-weight: bold;">💰</span>
            <span><strong>Pending Payment</strong> - Outstanding balance</span>
        </div>
    </div>

    <div class="calendar-header">
        <h2 style="color: #333; font-size: 24px;">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h2>
        <div class="calendar-nav">
            <a href="{{ route('bookings.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="btn">← Previous</a>
            <a href="{{ route('bookings.calendar') }}" class="btn" style="background: #95a5a6;">Today</a>
            <a href="{{ route('bookings.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="btn">Next →</a>
        </div>
    </div>

    <div class="weekday-header">
        <div class="weekday">Sun</div>
        <div class="weekday">Mon</div>
        <div class="weekday">Tue</div>
        <div class="weekday">Wed</div>
        <div class="weekday">Thu</div>
        <div class="weekday">Fri</div>
        <div class="weekday">Sat</div>
    </div>

    <div class="calendar-grid">
        @foreach($calendar as $day)
            <div class="calendar-day {{ !$day['isCurrentMonth'] ? 'other-month' : '' }} {{ $day['isToday'] ? 'today' : '' }}">
                <div class="day-number">{{ $day['date']->day }}</div>
                @foreach($day['bookings'] as $booking)
                    @php
                        $statusClass = str_replace('_', '-', $booking->status);
                        $statusLabel = ucfirst(str_replace('_', ' ', $booking->status));
                        $hasPendingPayment = $booking->outstanding_balance > 0 && $booking->status !== 'cancelled';
                        $paymentInfo = $hasPendingPayment ? ' - Balance: $' . number_format($booking->outstanding_balance, 2) : '';
                    @endphp
                    <a href="{{ route('bookings.show', $booking->id) }}" 
                       class="booking-item {{ $statusClass }} {{ $hasPendingPayment ? 'has-pending-payment' : '' }}" 
                       title="{{ $booking->guest_name }} - Room {{ $booking->room->room_number }} - Status: {{ $statusLabel }}{{ $paymentInfo }}" 
                       style="text-decoration: none; display: block;">
                        <span class="status-badge">{{ $statusLabel }}</span>
                        {{ $booking->guest_name }} ({{ $booking->room->room_number }})
                        @if($hasPendingPayment)
                            <span class="pending-payment-indicator" title="Pending Payment: ${{ number_format($booking->outstanding_balance, 2) }}">💰</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection
