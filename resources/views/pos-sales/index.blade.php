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

@if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('pos-sales.index') }}" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Filter by Hotel:</label>
                <select name="hotel_id" onchange="this.form.submit()" style="padding: 8px 16px; border: 2px solid #667eea; border-radius: 6px; background: white; cursor: pointer; min-width: 200px;">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ request('hotel_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('hotel_id'))
                <a href="{{ route('pos-sales.index') }}" style="padding: 8px 16px; background: #95a5a6; color: white; border-radius: 6px; text-decoration: none; font-size: 14px;">
                    Clear Filter
                </a>
            @endif
        </form>
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
                <th>Room</th>
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
                    <td>{{ $sale->room ? $sale->room->room_number : '-' }}</td>
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
                    <td colspan="{{ (isset($isSuperAdmin) && $isSuperAdmin) ? '7' : '6' }}" style="text-align: center; color: #999; padding: 40px;">No sales found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $sales->links() }}
    </div>
</div>
@endsection

