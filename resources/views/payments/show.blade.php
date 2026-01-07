@extends('layouts.app')

@section('title', 'Payment Details')
@section('page-title', 'Payment Details')

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
        background: #95a5a6;
        color: white;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 500;
        color: #666;
    }
    .info-value {
        font-weight: 600;
    }
    @media print {
        .sidebar, .top-bar, .btn, nav {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
        }
        .content-area {
            padding: 20px !important;
        }
        body {
            background: white !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        .info-row {
            page-break-inside: avoid;
        }
        h2, h3 {
            page-break-after: avoid;
        }
        .receipt-header, .receipt-footer {
            display: block !important;
        }
        .received-by-highlight {
            background: #f5f5f5 !important;
            border: 2px solid #333 !important;
        }
    }
    .receipt-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #333;
    }
    .receipt-header h2 {
        margin: 0;
        font-size: 28px;
        color: #333;
    }
    .receipt-header p {
        margin: 5px 0;
        color: #666;
    }
    .receipt-footer {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #333;
        text-align: center;
    }
    .received-by-highlight {
        background: #f0f0f0;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        border-left: 4px solid #667eea;
    }
    .received-by-highlight .label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }
    .received-by-highlight .name {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Payment #{{ $payment->id }}</h2>
    <div style="display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn" style="background: #e74c3c; color: white;">Print to PDF</button>
        <a href="{{ route('payments.index') }}" class="btn">Back to Payments</a>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <!-- Receipt Header (for printing) -->
    <div class="receipt-header" style="display: none;">
        <h2>PAYMENT RECEIPT</h2>
        @if(session('hotel_id'))
            @php
                $hotel = \App\Models\Hotel::find(session('hotel_id'));
            @endphp
            @if($hotel)
                <p><strong>{{ $hotel->name }}</strong></p>
                @if($hotel->address)
                    <p>{{ $hotel->address }}</p>
                @endif
            @endif
        @endif
    </div>

    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Payment Information</h3>
    
    <!-- Received By - Prominently Displayed -->
    @if($payment->receivedBy)
        <div class="received-by-highlight">
            <div class="label">Received By</div>
            <div class="name">{{ $payment->receivedBy->name }}</div>
        </div>
    @endif

    <div class="info-row">
        <span class="info-label">Amount:</span>
        <span class="info-value">${{ number_format($payment->amount, 2) }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Payment Method:</span>
        <span class="info-value">{{ ucfirst($payment->payment_method) }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Payment Date:</span>
        <span class="info-value">{{ $payment->paid_at->format('M d, Y H:i') }}</span>
    </div>

    @if($payment->reference_number)
        <div class="info-row">
            <span class="info-label">Reference Number:</span>
            <span class="info-value">{{ $payment->reference_number }}</span>
        </div>
    @endif

    <div class="info-row">
        <span class="info-label">Received By:</span>
        <span class="info-value">
            @if($payment->receivedBy)
                <strong>{{ $payment->receivedBy->name }}</strong>
            @else
                <span style="color: #999;">Not recorded</span>
            @endif
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Type:</span>
        <span class="info-value">
            @if($payment->booking_id)
                Booking Payment
            @else
                POS Sale Payment
            @endif
        </span>
    </div>

    @if($payment->booking)
        <div class="info-row">
            <span class="info-label">Booking:</span>
            <span class="info-value">
                #{{ $payment->booking->id }} - {{ $payment->booking->guest_name }}
                (Room {{ $payment->booking->room->room_number }})
            </span>
        </div>
    @endif

    @if($payment->posSale)
        <div class="info-row">
            <span class="info-label">POS Sale:</span>
            <span class="info-value">#{{ $payment->posSale->id }}</span>
        </div>
    @endif

    @if($payment->notes)
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span class="info-value">{{ $payment->notes }}</span>
        </div>
    @endif

    <!-- Receipt Footer (for printing) -->
    <div class="receipt-footer" style="display: none;">
        <p style="margin: 10px 0; color: #666;">
            @if($payment->receivedBy)
                <strong>Received By:</strong> {{ $payment->receivedBy->name }}
            @endif
        </p>
        <p style="margin: 5px 0; color: #999; font-size: 12px;">
            Payment ID: #{{ $payment->id }} | Printed: {{ now()->format('M d, Y H:i') }}
        </p>
    </div>
</div>

@push('scripts')
<script>
    // Show receipt header and footer when printing
    window.addEventListener('beforeprint', function() {
        document.querySelectorAll('.receipt-header, .receipt-footer').forEach(el => {
            el.style.display = 'block';
        });
    });
    
    window.addEventListener('afterprint', function() {
        document.querySelectorAll('.receipt-header, .receipt-footer').forEach(el => {
            el.style.display = 'none';
        });
    });
</script>
@endpush
@endsection
