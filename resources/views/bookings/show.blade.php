@extends('layouts.app')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

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
    .btn-secondary {
        background: #95a5a6;
        color: white;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    .info-label {
        font-weight: 500;
        color: #666;
    }
    .info-value {
        font-weight: 600;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
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
    .badge-paid { background: #d4edda; color: #155724; }
    .badge-pending { background: #fff3cd; color: #856404; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Booking #{{ $booking->id }}</h2>
    <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Back to Bookings</a>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Booking Information</h3>
    
    <div class="info-row">
        <span class="info-label">Guest Name:</span>
        <span class="info-value">{{ $booking->guest_name }}</span>
    </div>

    @if($booking->guest_email)
    <div class="info-row">
        <span class="info-label">Email:</span>
        <span class="info-value">{{ $booking->guest_email }}</span>
    </div>
    @endif

    @if($booking->guest_phone)
    <div class="info-row">
        <span class="info-label">Phone:</span>
        <span class="info-value">{{ $booking->guest_phone }}</span>
    </div>
    @endif

    <div class="info-row">
        <span class="info-label">Room:</span>
        <span class="info-value">{{ $booking->room->room_number }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Check In:</span>
        <span class="info-value">{{ $booking->check_in->format('M d, Y') }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Check Out:</span>
        <span class="info-value">{{ $booking->check_out->format('M d, Y') }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Nights:</span>
        <span class="info-value">{{ $booking->nights }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Adults:</span>
        <span class="info-value">{{ $booking->adults }}</span>
    </div>

    @if($booking->children)
    <div class="info-row">
        <span class="info-label">Children:</span>
        <span class="info-value">{{ $booking->children }}</span>
    </div>
    @endif

    <div class="info-row">
        <span class="info-label">Total Amount:</span>
        <span class="info-value">${{ number_format($booking->total_amount, 2) }}</span>
    </div>

    @if(($booking->discount ?? 0) > 0)
    <div class="info-row">
        <span class="info-label">Discount:</span>
        <span class="info-value" style="color: #e74c3c;">-${{ number_format($booking->discount, 2) }}</span>
    </div>
    @endif

    <div class="info-row" style="border-top: 2px solid #667eea; padding-top: 15px; margin-top: 10px;">
        <span class="info-label" style="font-size: 16px; font-weight: 700;">Final Amount:</span>
        <span class="info-value" style="font-size: 18px; font-weight: 700; color: #667eea;">${{ number_format($booking->final_amount, 2) }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Total Paid:</span>
        <span class="info-value">${{ number_format($booking->total_paid, 2) }}</span>
    </div>

    @php
        $bookingBalance = max(0, $booking->final_amount - $booking->total_paid);
        $posCharges = $booking->total_pos_charges;
    @endphp

    @if($posCharges > 0)
    <div class="info-row" style="border-top: 2px solid #eee; padding-top: 15px; margin-top: 10px;">
        <span class="info-label">POS Charges (Unpaid):</span>
        <span class="info-value" style="color: #e74c3c; font-weight: 600;">
            ${{ number_format($posCharges, 2) }}
        </span>
    </div>
    <small style="color: #666; display: block; margin-top: 5px; margin-bottom: 10px;">
        Unpaid charges from room service and other POS sales.
    </small>
    @endif

    <div class="info-row" style="border-top: 2px solid #eee; padding-top: 15px; margin-top: 10px;">
        <span class="info-label" style="font-weight: 700;">Total Outstanding Balance:</span>
        <span class="info-value" style="font-weight: 700; color: {{ $booking->outstanding_balance > 0 ? '#e74c3c' : '#155724' }};">
            ${{ number_format($booking->outstanding_balance, 2) }}
            @if($booking->isFullyPaid())
                <span class="badge badge-paid">Paid</span>
            @else
                <span class="badge badge-pending">Pending</span>
            @endif
        </span>
    </div>
    <small style="color: #666; display: block; margin-top: 5px;">
        @if($posCharges > 0)
            Includes booking balance (${{ number_format($bookingBalance, 2) }}) and POS charges (${{ number_format($posCharges, 2) }}).
        @else
            Outstanding balance is calculated based on final amount (after discount).
        @endif
    </small>

    <div class="info-row">
        <span class="info-label">Booking Date/Time:</span>
        <span class="info-value">
            {{ $booking->created_at->format('M d, Y') }} at {{ $booking->created_at->format('h:i A') }}
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
    </div>

    @if($booking->status === 'cancelled' && $booking->cancellation_reason)
    <div class="info-row">
        <span class="info-label">Cancellation Reason:</span>
        <span class="info-value" style="color: #dc3545;">
            {{ $booking->cancellation_reason }}
            @if(str_starts_with($booking->cancellation_reason, 'System:'))
                <span style="font-size: 11px; color: #999; margin-left: 5px;">(Auto-cancelled)</span>
            @endif
        </span>
    </div>
    @endif

    <div class="info-row">
        <span class="info-label">Source:</span>
        <span class="info-value">
            @if($booking->source === 'public' || $booking->isPublic())
                Public Website
            @else
                Dashboard
            @endif
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Created By:</span>
        <span class="info-value">
            @if($booking->source === 'public' || $booking->isPublic())
                Guest (Public Link)
            @elseif($booking->createdBy)
                @php
                    $hotelId = session('hotel_id');
                    $roles = $booking->createdBy->roles->where('pivot.hotel_id', $hotelId)->pluck('name')->implode(', ');
                @endphp
                {{ $booking->createdBy->name }}
                @if($roles)
                    <span style="font-size: 11px; color: #777; margin-left: 6px;">({{ $roles }})</span>
                @endif
            @else
                <span style="font-size: 12px; color: #999;">-</span>
            @endif
        </span>
    </div>

    @if($booking->notes)
    <div class="info-row">
        <span class="info-label">Notes:</span>
        <span class="info-value">{{ $booking->notes }}</span>
    </div>
    @endif
</div>

@if($booking->posSales && $booking->posSales->count() > 0)
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Room Charges (POS Sales)</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="text-align: left; padding: 10px;">Date</th>
                <th style="text-align: left; padding: 10px;">Reference</th>
                <th style="text-align: left; padding: 10px;">Items</th>
                <th style="text-align: right; padding: 10px;">Amount</th>
                <th style="text-align: right; padding: 10px;">Paid</th>
                <th style="text-align: right; padding: 10px;">Balance</th>
                <th style="text-align: center; padding: 10px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->posSales as $posSale)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">{{ $posSale->sale_date->format('M d, Y') }}</td>
                    <td style="padding: 10px;">
                        <a href="{{ route('pos-sales.show', $posSale) }}" style="color: #667eea; text-decoration: none;">
                            {{ $posSale->sale_reference }}
                        </a>
                    </td>
                    <td style="padding: 10px;">
                        @foreach($posSale->items as $item)
                            <div style="font-size: 12px; color: #666;">
                                {{ $item->quantity }}x {{ $item->extra->name }}
                            </div>
                        @endforeach
                    </td>
                    <td style="text-align: right; padding: 10px;">${{ number_format($posSale->final_amount, 2) }}</td>
                    <td style="text-align: right; padding: 10px;">${{ number_format($posSale->total_paid, 2) }}</td>
                    <td style="text-align: right; padding: 10px; color: {{ $posSale->outstanding_balance > 0 ? '#e74c3c' : '#155724' }}; font-weight: 600;">
                        ${{ number_format($posSale->outstanding_balance, 2) }}
                    </td>
                    <td style="text-align: center; padding: 10px;">
                        @if($posSale->payment_status === 'paid')
                            <span class="badge badge-paid">Paid</span>
                        @elseif($posSale->payment_status === 'partial')
                            <span class="badge badge-partial">Partial</span>
                        @else
                            <span class="badge badge-pending">Pending</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: #333; font-size: 20px;">Payments</h3>
        @if(
            $booking->status !== 'cancelled' &&
            $booking->outstanding_balance > 0 &&
            (auth()->user()->hasPermission('payments.create') || auth()->user()->isSuperAdmin())
        )
            <a href="{{ route('payments.create', ['booking_id' => $booking->id]) }}" class="btn btn-primary">Add Payment</a>
        @endif
    </div>
    
    @if($booking->payments && $booking->payments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                        <td>${{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>{{ $payment->reference_number ?? '-' }}</td>
                        <td>
                            <a href="{{ route('payments.show', $payment) }}" class="btn" style="background: #3498db; color: white;">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #999; text-align: center; padding: 40px;">No payments recorded</p>
    @endif
</div>
@endsection
