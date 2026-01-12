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

<!-- Search and Filters -->
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <form method="GET" action="{{ route('bookings.index') }}" id="searchForm">
        <!-- Search Bar -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by guest name, email, phone, or booking reference..." 
                    style="flex: 1; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"
                >
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">Search</button>
                @if(request()->hasAny(['search', 'status', 'room_id', 'source', 'check_in_from', 'check_in_to', 'check_out_from', 'check_out_to', 'booking_date_from', 'booking_date_to']))
                    <a href="{{ route('bookings.index') }}" class="btn" style="background: #95a5a6; color: white; padding: 12px 24px;">Clear</a>
                @endif
            </div>
        </div>

        <!-- Filters Toggle -->
        <div style="margin-bottom: 15px;">
            <button 
                type="button" 
                onclick="toggleFilters()" 
                style="background: #f8f9fa; border: 1px solid #e0e0e0; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px;"
            >
                <span id="filterToggleText">Show Filters</span> ▼
            </button>
        </div>

        <!-- Filters Panel -->
        <div id="filtersPanel" style="display: none; border-top: 1px solid #e0e0e0; padding-top: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 15px;">
                @if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels))
                <!-- Hotel Filter (Super Admin Only) -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Hotel</label>
                    <select name="hotel_id" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                        <option value="">All Hotels</option>
                        @foreach($hotels as $h)
                            <option value="{{ $h->id }}" {{ request('hotel_id') == $h->id ? 'selected' : '' }}>
                                {{ $h->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Status Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Status</label>
                    <select name="status" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Room Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Room</label>
                    <select name="room_id" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                        <option value="">All Rooms</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->room_number }} - {{ $room->roomType->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Source Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Source</label>
                    <select name="source" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                        <option value="">All Sources</option>
                        <option value="dashboard" {{ request('source') == 'dashboard' ? 'selected' : '' }}>Dashboard</option>
                        <option value="public" {{ request('source') == 'public' ? 'selected' : '' }}>Public Link</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Sort By</label>
                    <select name="sort_by" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                        <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>Booking Date</option>
                        <option value="check_in" {{ request('sort_by') == 'check_in' ? 'selected' : '' }}>Check In</option>
                        <option value="check_out" {{ request('sort_by') == 'check_out' ? 'selected' : '' }}>Check Out</option>
                        <option value="guest_name" {{ request('sort_by') == 'guest_name' ? 'selected' : '' }}>Guest Name</option>
                        <option value="total_amount" {{ request('sort_by') == 'total_amount' ? 'selected' : '' }}>Total Amount</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <!-- Check-in Date Range -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Check-in From</label>
                    <input type="date" name="check_in_from" value="{{ request('check_in_from') }}" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Check-in To</label>
                    <input type="date" name="check_in_to" value="{{ request('check_in_to') }}" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>

                <!-- Check-out Date Range -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Check-out From</label>
                    <input type="date" name="check_out_from" value="{{ request('check_out_from') }}" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Check-out To</label>
                    <input type="date" name="check_out_to" value="{{ request('check_out_to') }}" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <!-- Booking Date Range -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Booking Date From</label>
                    <input type="date" name="booking_date_from" value="{{ request('booking_date_from') }}" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Booking Date To</label>
                    <input type="date" name="booking_date_to" value="{{ request('booking_date_to') }}" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>

                <!-- Sort Order -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Sort Order</label>
                    <select name="sort_order" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>
    </form>

    <!-- Active Filters Display -->
    @if(request()->hasAny(['hotel_id', 'status', 'room_id', 'source', 'check_in_from', 'check_in_to', 'check_out_from', 'check_out_to', 'booking_date_from', 'booking_date_to']))
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
            <div style="font-size: 13px; color: #666; margin-bottom: 8px;">Active Filters:</div>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                @if(isset($isSuperAdmin) && $isSuperAdmin && request('hotel_id'))
                    @php $selectedHotel = isset($hotels) ? $hotels->firstWhere('id', request('hotel_id')) : null; @endphp
                    @if($selectedHotel)
                        <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                            Hotel: {{ $selectedHotel->name }}
                        </span>
                    @endif
                @endif
                @if(request('status'))
                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                        Status: {{ ucfirst(request('status')) }}
                    </span>
                @endif
                @if(request('room_id'))
                    @php $selectedRoom = $rooms->firstWhere('id', request('room_id')); @endphp
                    @if($selectedRoom)
                        <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                            Room: {{ $selectedRoom->room_number }}
                        </span>
                    @endif
                @endif
                @if(request('source'))
                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                        Source: {{ ucfirst(request('source')) }}
                    </span>
                @endif
                @if(request('check_in_from') || request('check_in_to'))
                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                        Check-in: {{ request('check_in_from') ?? 'Any' }} to {{ request('check_in_to') ?? 'Any' }}
                    </span>
                @endif
                @if(request('check_out_from') || request('check_out_to'))
                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                        Check-out: {{ request('check_out_from') ?? 'Any' }} to {{ request('check_out_to') ?? 'Any' }}
                    </span>
                @endif
                @if(request('booking_date_from') || request('booking_date_to'))
                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                        Booking Date: {{ request('booking_date_from') ?? 'Any' }} to {{ request('booking_date_to') ?? 'Any' }}
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                <th>Hotel</th>
                @endif
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
                <th>Booking Date/Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <td>
                        <strong style="color: #667eea;">{{ $booking->hotel->name ?? 'Unknown Hotel' }}</strong>
                        @if($booking->hotel && $booking->hotel->address)
                            <div style="font-size: 11px; color: #999; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($booking->hotel->address, 30) }}</div>
                        @endif
                    </td>
                    @endif
                    <td><strong>{{ $booking->guest_name }}</strong></td>
                    <td>{{ $booking->room->room_number ?? 'N/A' }}</td>
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
                        <div style="font-size: 13px; color: #333;">
                            {{ $booking->created_at->format('M d, Y') }}
                        </div>
                        <div style="font-size: 11px; color: #777;">
                            {{ $booking->created_at->format('h:i A') }}
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-{{ str_replace('_', '-', $booking->status) }}">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                        @if($booking->outstanding_balance > 0 && $booking->status !== 'cancelled')
                            <div style="margin-top: 4px;">
                                <span class="badge" style="background: #ffc107; color: #856404; font-size: 11px;">
                                    Pending Payment
                                </span>
                            </div>
                        @endif
                        @if($booking->status === 'cancelled' && $booking->cancellation_reason)
                            <div style="font-size: 11px; color: #dc3545; margin-top: 4px;">
                                {{ $booking->cancellation_reason }}
                                @if(str_starts_with($booking->cancellation_reason, 'System:'))
                                    <span style="font-size: 10px; color: #999; margin-left: 4px;">(Auto)</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <a href="{{ route('bookings.show', $booking) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View</a>
                            
                            @if(auth()->user()->hasPermission('bookings.edit', session('hotel_id')) || auth()->user()->isSuperAdmin())
                                @php
                                    $today = \Carbon\Carbon::today();
                                    $checkInDate = \Carbon\Carbon::parse($booking->check_in);
                                    $checkOutDate = \Carbon\Carbon::parse($booking->check_out);
                                    $canCheckIn = $booking->status === 'confirmed' 
                                        && $today->gte($checkInDate) 
                                        && $today->lte($checkOutDate)
                                        && $booking->isFullyPaid();
                                @endphp
                                @if($canCheckIn)
                                    <form action="{{ route('bookings.check-in', $booking) }}" method="POST" style="display: inline;" onsubmit="return confirm('Check in {{ $booking->guest_name }}?')">
                                        @csrf
                                        <button type="submit" class="btn" style="background: #28a745; color: white; padding: 6px 12px; font-size: 12px;">Check In</button>
                                    </form>
                                @endif
                                
                                @if($booking->status === 'checked_in')
                                    <form action="{{ route('bookings.check-out', $booking) }}" method="POST" style="display: inline;" onsubmit="return confirm('Check out {{ $booking->guest_name }}?')">
                                        @csrf
                                        <button type="submit" class="btn" style="background: #ff9800; color: white; padding: 6px 12px; font-size: 12px;">Check Out</button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-edit" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                            @endif
                            
                            @if(auth()->user()->hasPermission('bookings.delete', session('hotel_id')) || auth()->user()->isSuperAdmin())
                                <form action="{{ route('bookings.destroy', $booking) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($isSuperAdmin) && $isSuperAdmin) ? '14' : '13' }}" style="text-align: center; color: #999; padding: 40px;">No bookings found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div style="color: #666; font-size: 14px;">
            Showing {{ $bookings->firstItem() ?? 0 }} to {{ $bookings->lastItem() ?? 0 }} of {{ $bookings->total() }} bookings
        </div>
        <div>
            {{ $bookings->links() }}
        </div>
    </div>
</div>

<script>
    function toggleFilters() {
        const panel = document.getElementById('filtersPanel');
        const toggleText = document.getElementById('filterToggleText');
        
        if (panel.style.display === 'none') {
            panel.style.display = 'block';
            toggleText.textContent = 'Hide Filters';
        } else {
            panel.style.display = 'none';
            toggleText.textContent = 'Show Filters';
        }
    }

    // Show filters panel if any filters are active
    @if(request()->hasAny(['status', 'room_id', 'source', 'check_in_from', 'check_in_to', 'check_out_from', 'check_out_to', 'booking_date_from', 'booking_date_to']))
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filtersPanel').style.display = 'block';
            document.getElementById('filterToggleText').textContent = 'Hide Filters';
        });
    @endif
</script>
@endsection
