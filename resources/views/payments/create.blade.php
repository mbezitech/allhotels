@extends('layouts.app')

@section('title', 'Record Payment')
@section('page-title', 'Record Payment')

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    input, select, textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
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
    .error {
        color: #e74c3c;
        font-size: 13px;
        margin-top: 5px;
        display: block;
    }
    .info-box {
        background: #e3f2fd;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .info-box strong {
        display: block;
        margin-bottom: 5px;
        color: #1976d2;
    }
</style>
@endpush

@section('content')
@if($booking)
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <div class="info-box">
            <strong>Booking Payment</strong>
            Guest: {{ $booking->guest_name }}<br>
            Room: {{ $booking->room->room_number }}<br>
            Total: ${{ number_format($booking->total_amount, 2) }}<br>
            Paid: ${{ number_format($booking->total_paid, 2) }}<br>
            Outstanding: ${{ number_format($booking->outstanding_balance, 2) }}
        </div>
    </div>
@endif

@if($posSale)
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <div class="info-box">
            <strong>POS Sale Payment</strong>
            Sale Date: {{ $posSale->sale_date->format('M d, Y') }}<br>
            @if($posSale->room)
                Room: {{ $posSale->room->room_number }}<br>
            @endif
            Total: ${{ number_format($posSale->final_amount, 2) }}<br>
            Paid: ${{ number_format($posSale->total_paid, 2) }}<br>
            Outstanding: ${{ number_format($posSale->outstanding_balance, 2) }}
        </div>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ route('payments.store') }}">
        @csrf

        @if($booking)
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
        @endif

        @if($posSale)
            <input type="hidden" name="pos_sale_id" value="{{ $posSale->id }}">
        @endif

        <div class="form-group">
            <label for="amount">Amount *</label>
            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required>
            @if($booking)
                <small style="color: #666; display: block; margin-top: 5px;">Outstanding: ${{ number_format($booking->outstanding_balance, 2) }}</small>
            @endif
            @if($posSale)
                <small style="color: #666; display: block; margin-top: 5px;">Outstanding: ${{ number_format($posSale->outstanding_balance, 2) }}</small>
            @endif
            @error('amount')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method *</label>
            <select id="payment_method" name="payment_method" required>
                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('payment_method')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="reference_number">Reference Number</label>
            <input type="text" id="reference_number" name="reference_number" value="{{ old('reference_number') }}" placeholder="Transaction ID, Check #, etc.">
            @error('reference_number')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="paid_at">Payment Date *</label>
            <input type="datetime-local" id="paid_at" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required>
            @error('paid_at')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Additional notes about this payment...">{{ old('notes') }}</textarea>
            @error('notes')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Record Payment</button>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
