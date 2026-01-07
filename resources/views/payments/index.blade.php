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

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
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
                    <td colspan="8" style="text-align: center; color: #999; padding: 40px;">No payments found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $payments->links() }}
    </div>
</div>
@endsection
