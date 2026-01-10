@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payments')

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
    .badge-booking { background: #d1ecf1; color: #0c5460; }
    .badge-pos { background: #d4edda; color: #155724; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Payments</h2>
    @if(auth()->user()->hasPermission('payments.create') || auth()->user()->isSuperAdmin())
        <a href="{{ route('payments.create') }}" class="btn btn-primary">Record Payment</a>
    @endif
</div>

@if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('payments.index') }}" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
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
                <a href="{{ route('payments.index') }}" style="padding: 8px 16px; background: #95a5a6; color: white; border-radius: 6px; text-decoration: none; font-size: 14px;">
                    Clear Filter
                </a>
            @endif
        </form>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                <th>Hotel</th>
                @endif
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference #</th>
                <th>Received By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <td>
                        <strong style="color: #667eea;">{{ $payment->hotel->name ?? 'Unknown Hotel' }}</strong>
                        @if($payment->hotel && $payment->hotel->address)
                            <div style="font-size: 11px; color: #999; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($payment->hotel->address, 30) }}</div>
                        @endif
                    </td>
                    @endif
                    <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                    <td>
                        @if($payment->booking_id)
                            <span class="badge badge-booking">Booking</span>
                        @else
                            <span class="badge badge-pos">POS Sale</span>
                        @endif
                    </td>
                    <td>
                        @if($payment->booking_id)
                            Booking #{{ $payment->booking_id }} - {{ $payment->booking->guest_name }}
                        @else
                            POS Sale #{{ $payment->pos_sale_id }}
                        @endif
                    </td>
                    <td><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td>{{ $payment->reference_number ?? '-' }}</td>
                    <td>
                        @if($payment->receivedBy)
                            {{ $payment->receivedBy->name }}
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('payments.show', $payment) }}" class="btn" style="background: #3498db; color: white;">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($isSuperAdmin) && $isSuperAdmin) ? '9' : '8' }}" style="text-align: center; color: #999; padding: 40px;">No payments found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $payments->links() }}
    </div>
</div>
@endsection
