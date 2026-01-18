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
        cursor: pointer;
        border-left: 3px solid rgba(255, 255, 255, 0.5);
    }
    .booking-item-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .booking-item-header {
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .booking-item-footer {
        font-size: 9px;
        opacity: 0.9;
        margin-top: 2px;
        padding-left: 2px;
    }
    .booking-item:hover {
        opacity: 0.9;
        transform: translateX(2px);
        transition: all 0.2s;
    }
    .booking-item.pending-payment {
        background: #ffc107;
        color: #333;
        border-left: 3px solid #ff9800;
    }
    .booking-item.paid-in-full {
        background: #28a745;
        color: white;
        border-left: 3px solid #1e7e34;
    }
    .booking-item.checked-in {
        background: #17a2b8;
        color: white;
        border-left: 3px solid #117a8b;
    }
    .booking-item.cancelled {
        background: #dc3545;
        color: white;
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
    .pos-charges-indicator {
        display: inline-block;
        font-size: 10px;
        margin-left: 4px;
        font-weight: bold;
        color: #ff5722;
        text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    }
    .booking-item.has-pending-payment {
        border-left: 3px solid #ffc107;
        box-shadow: 0 0 3px rgba(255, 193, 7, 0.5);
    }
    .booking-item.has-pos-charges {
        border-left: 3px solid #ff5722;
        box-shadow: 0 0 3px rgba(255, 87, 34, 0.5);
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
    .legend-color.pending-payment { background: #ffc107; border-left: 3px solid #ff9800; }
    .legend-color.paid-in-full { background: #28a745; border-left: 3px solid #1e7e34; }
    .legend-color.checked-in { background: #17a2b8; border-left: 3px solid #117a8b; }
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
            <div class="legend-color pending-payment"></div>
            <span><strong>Pending Payment</strong> - Outstanding balance</span>
        </div>
        <div class="legend-item">
            <span style="font-size: 14px; color: #ff5722; font-weight: bold;">🛒</span>
            <span><strong>POS Charges</strong> - Outstanding room service/POS charges</span>
        </div>
        <div class="legend-item">
            <div class="legend-color paid-in-full"></div>
            <span><strong>Paid in Full</strong> - Payment complete</span>
        </div>
        <div class="legend-item">
            <div class="legend-color checked-in"></div>
            <span><strong>Currently Checked In</strong> - Guest currently staying</span>
        </div>
        <div class="legend-item">
            <div class="legend-color cancelled"></div>
            <span><strong>Cancelled</strong> - Booking cancelled</span>
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
                        // Determine payment status and display class
                        $outstandingBalance = $booking->outstanding_balance;
                        $bookingBalance = max(0, $booking->final_amount - $booking->total_paid);
                        $posCharges = $booking->total_pos_charges;
                        $isCheckedIn = $booking->status === 'checked_in';
                        $isCancelled = $booking->status === 'cancelled';
                        $isFullyPaid = $outstandingBalance <= 0;
                        $hasPosCharges = $posCharges > 0;
                        
                        // Build title info with breakdown
                        $titleBreakdown = [];
                        if ($bookingBalance > 0) {
                            $titleBreakdown[] = 'Booking: $' . number_format($bookingBalance, 2);
                        }
                        if ($hasPosCharges) {
                            $titleBreakdown[] = 'POS: $' . number_format($posCharges, 2);
                        }
                        $titleInfo = !empty($titleBreakdown) ? ' - ' . implode(', ', $titleBreakdown) : '';
                        
                        if ($isCancelled) {
                            $statusClass = 'cancelled';
                            $statusLabel = 'Cancelled';
                            $titleInfo = ' - Cancelled';
                        } elseif ($isCheckedIn) {
                            // Currently checked in - show this status
                            $statusClass = 'checked-in';
                            $statusLabel = 'Currently Checked In';
                            if ($isFullyPaid) {
                                $titleInfo = ' - Paid in Full';
                            } else {
                                $titleInfo = ' - Balance: $' . number_format($outstandingBalance, 2) . $titleInfo;
                            }
                        } elseif ($outstandingBalance > 0) {
                            // Pending payment
                            $statusClass = 'pending-payment';
                            $statusLabel = 'Pending Payment';
                            $titleInfo = ' - Balance: $' . number_format($outstandingBalance, 2) . $titleInfo;
                        } else {
                            // Paid in full
                            $statusClass = 'paid-in-full';
                            $statusLabel = 'Paid in Full';
                            $titleInfo = ' - Payment Complete';
                        }
                    @endphp
                    <a href="{{ route('bookings.show', $booking->id) }}" 
                       class="booking-item {{ $statusClass }} {{ $hasPosCharges ? 'has-pos-charges' : '' }}" 
                       title="{{ $booking->guest_name }} - Room {{ $booking->room->room_number }} - {{ $statusLabel }}{{ $titleInfo }}" 
                       style="text-decoration: none; display: block;">
                        <div class="booking-item-content">
                            <div class="booking-item-header">
                                <span class="status-badge">{{ $statusLabel }}</span>
                                @if($outstandingBalance > 0 && !$isCancelled && !$hasPosCharges)
                                    <span class="pending-payment-indicator" title="Outstanding: ${{ number_format($outstandingBalance, 2) }}">💰</span>
                                @endif
                            </div>
                            <div style="font-size: 10px; font-weight: 500; margin-top: 2px; padding-left: 2px;">
                                {{ $booking->guest_name }}
                            </div>
                            <div style="font-size: 9px; opacity: 0.9; margin-top: 1px; padding-left: 2px;">
                                Room: {{ $booking->room->room_number ?? 'N/A' }}
                                @if($booking->room && $booking->room->roomType)
                                    <span style="margin-left: 4px; opacity: 0.8;">({{ $booking->room->roomType->name }})</span>
                                @endif
                            </div>
                            @if($hasPosCharges && !$isCancelled)
                                <div class="booking-item-footer">
                                    🛒 POS Outstanding: ${{ number_format($posCharges, 2) }}
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection
