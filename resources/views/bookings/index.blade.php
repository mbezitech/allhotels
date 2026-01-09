@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@push('styles')
<style>
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
    }
    .btn-primary {
        background: #667eea;
        color: white;
    }
    .btn-edit {
        background: #3498db;
        color: white;
    }
    .btn-danger {
        background: #e74c3c;
        color: white;
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
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-confirmed { background: #d4edda; color: #155724; }
    .badge-checked_in { background: #d1ecf1; color: #0c5460; }
    .badge-checked_out { background: #e2e3e5; color: #383d41; }
    .badge-cancelled { background: #f8d7da; color: #721c24; }
    .badge-paid { background: #d4edda; color: #155724; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Bookings</h2>
    <div>
        <a href="{{ route('bookings.calendar') }}" class="btn" style="background: #95a5a6; color: white; margin-right: 10px;">Calendar View</a>
        @if(auth()->user()->hasPermission('bookings.create') || auth()->user()->isSuperAdmin())
            <a href="{{ route('bookings.create') }}" class="btn btn-primary">New Booking</a>
        @endif
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
                <th>Guest Name</th>
                <th>Room</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Nights</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Source</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td><strong>{{ $booking->guest_name }}</strong></td>
                    <td>{{ $booking->room->room_number }}</td>
                    <td>{{ $booking->check_in->format('M d, Y') }}</td>
                    <td>{{ $booking->check_out->format('M d, Y') }}</td>
                    <td>{{ $booking->nights }}</td>
                    <td>${{ number_format($booking->total_amount, 2) }}</td>
                    <td>${{ number_format($booking->total_paid, 2) }}</td>
                    <td>
                        ${{ number_format($booking->outstanding_balance, 2) }}
                        @if($booking->isFullyPaid())
                            <span class="badge badge-paid" style="margin-left: 5px;">Paid</span>
                        @endif
                    </td>
                    <td>
                        @if($booking->source === 'public' || $booking->isPublic())
                            <span class="badge badge-pending">Public Link</span>
                        @else
                            <span class="badge badge-confirmed">Dashboard</span>
                        @endif
                    </td>
                    <td>
                        @if($booking->source === 'public' || $booking->isPublic())
                            <span style="font-size: 12px; color: #666;">Guest (Public)</span>
                        @elseif($booking->createdBy)
                            @php
                                $hotelId = session('hotel_id');
                                $roles = $booking->createdBy->roles->where('pivot.hotel_id', $hotelId)->pluck('name')->implode(', ');
                            @endphp
                            <div style="font-size: 13px; color: #333;">
                                {{ $booking->createdBy->name }}
                            </div>
                            @if($roles)
                                <div style="font-size: 11px; color: #777;">
                                    {{ $roles }}
                                </div>
                            @endif
                        @else
                            <span style="font-size: 12px; color: #999;">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ str_replace('_', '-', $booking->status) }}">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('bookings.show', $booking) }}" class="btn" style="background: #3498db; color: white; margin-right: 5px;">View</a>
                        @if(auth()->user()->hasPermission('bookings.edit') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-edit">Edit</a>
                        @endif
                        @if(auth()->user()->hasPermission('bookings.delete') || auth()->user()->isSuperAdmin())
                            <form action="{{ route('bookings.destroy', $booking) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #999; padding: 40px;">No bookings found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
