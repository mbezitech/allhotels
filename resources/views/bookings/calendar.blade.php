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
        background: #667eea;
        color: white;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
    }
    .booking-item:hover {
        opacity: 0.8;
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
                    <a href="{{ route('bookings.show', $booking->id) }}" class="booking-item" title="{{ $booking->guest_name }} - Room {{ $booking->room->room_number }}" style="text-decoration: none; display: block;">
                        {{ $booking->guest_name }} ({{ $booking->room->room_number }})
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection
