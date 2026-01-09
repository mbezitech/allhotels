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

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table>
        <thead>
            <tr>
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
                    <td colspan="6" style="text-align: center; color: #999; padding: 40px;">No sales found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $sales->links() }}
    </div>
</div>
@endsection

