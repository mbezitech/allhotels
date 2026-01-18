@extends('layouts.app')

@section('title', 'POS Sales')
@section('page-title', 'POS Sales')

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
    .badge-partial { background: #d1ecf1; color: #0c5460; }
    .badge-paid { background: #d4edda; color: #155724; }
    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All POS Sales</h2>
    @if(auth()->user()->hasPermission('pos.sell') || auth()->user()->isSuperAdmin())
        <a href="{{ route('pos-sales.create') }}" class="btn btn-primary">New Sale</a>
    @endif
</div>

<!-- Filters Section -->
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Filters</h3>
    <form method="GET" action="{{ route('pos-sales.index') }}" id="filterForm">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
            @if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Hotel:</label>
                <select name="hotel_id" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ request('hotel_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Payment Status:</label>
                <select name="payment_status" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Room:</label>
                <select name="room_id" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All Rooms</option>
                    @if(isset($rooms))
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->room_number }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Booking:</label>
                <select name="booking_id" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All Bookings</option>
                    @if(isset($bookings))
                        @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}" {{ request('booking_id') == $booking->id ? 'selected' : '' }}>
                                {{ $booking->booking_reference }} - {{ $booking->guest_name }} ({{ $booking->room->room_number ?? 'N/A' }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">From Date:</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">To Date:</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Amount From:</label>
                <input type="number" name="amount_from" value="{{ request('amount_from') }}" step="0.01" min="0" placeholder="0.00" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Amount To:</label>
                <input type="number" name="amount_to" value="{{ request('amount_to') }}" step="0.01" min="0" placeholder="0.00" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Search:</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Sale reference, notes..." style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Apply Filters</button>
            @if(request()->hasAny(['hotel_id', 'payment_status', 'room_id', 'booking_id', 'from_date', 'to_date', 'amount_from', 'amount_to', 'search']))
                <a href="{{ route('pos-sales.index') }}" class="btn" style="background: #95a5a6; color: white; padding: 10px 20px; text-decoration: none;">Clear All Filters</a>
            @endif
        </div>
    </form>
</div>

@if(request()->hasAny(['hotel_id', 'payment_status', 'room_id', 'booking_id', 'from_date', 'to_date', 'amount_from', 'amount_to', 'search']))
    <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="color: #1976d2;">Filters Active:</strong>
                <span style="color: #666; font-size: 13px; margin-left: 10px;">
                    @if(request('payment_status'))
                        Status: {{ ucfirst(request('payment_status')) }},
                    @endif
                    @if(request('from_date') || request('to_date'))
                        Date: {{ request('from_date') ? request('from_date') : 'Any' }} - {{ request('to_date') ? request('to_date') : 'Any' }},
                    @endif
                    @if(request('amount_from') || request('amount_to'))
                        Amount: ${{ request('amount_from') ? number_format(request('amount_from'), 2) : '0.00' }} - ${{ request('amount_to') ? number_format(request('amount_to'), 2) : 'Any' }}
                    @endif
                </span>
            </div>
            <div style="font-size: 12px; color: #666;">
                Showing {{ $sales->total() }} sale(s)
            </div>
        </div>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                <th>Hotel</th>
                @endif
                <th>Date</th>
                <th>Reference</th>
                <th>Room</th>
                <th>Booking</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <td>
                        <strong style="color: #667eea;">{{ $sale->hotel->name ?? 'Unknown Hotel' }}</strong>
                        @if($sale->hotel && $sale->hotel->address)
                            <div style="font-size: 11px; color: #999; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($sale->hotel->address, 30) }}</div>
                        @endif
                    </td>
                    @endif
                    <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                    <td>
                        @if($sale->sale_reference)
                            <span style="font-family: monospace; font-size: 12px; color: #667eea;">{{ $sale->sale_reference }}</span>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>{{ $sale->room ? $sale->room->room_number : '-' }}</td>
                    <td>
                        @if($sale->booking)
                            <span style="font-size: 12px;">
                                {{ $sale->booking->booking_reference }}<br>
                                <span style="color: #666;">{{ $sale->booking->guest_name }}</span>
                            </span>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>{{ $sale->items->count() }} item(s)</td>
                    <td>${{ number_format($sale->final_amount, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $sale->payment_status }}">
                            {{ ucfirst($sale->payment_status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('pos-sales.show', $sale) }}" class="btn" style="background: #3498db; color: white;">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($isSuperAdmin) && $isSuperAdmin) ? '9' : '8' }}" style="text-align: center; color: #999; padding: 40px;">No sales found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $sales->links() }}
    </div>
</div>
@endsection

