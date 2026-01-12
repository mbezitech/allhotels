@extends('layouts.app')

@section('title', 'Create Booking')
@section('page-title', 'Create Booking')

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
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ route('bookings.store') }}">
        @csrf

        <div class="form-group">
            <label for="room_id">Room *</label>
            <select id="room_id" name="room_id" required>
                <option value="">-- Select Room --</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" 
                            data-capacity="{{ $room->capacity }}"
                            data-price="{{ $room->price_per_night }}"
                            {{ old('room_id') == $room->id ? 'selected' : '' }}>
                        {{ $room->room_number }} - {{ $room->roomType->name ?? 'N/A' }} (Capacity: {{ $room->capacity }}, ${{ number_format($room->price_per_night, 2) }}/night)
                    </option>
                @endforeach
            </select>
            <small id="capacity-info" style="color: #666; margin-top: 5px; display: block;"></small>
            @error('room_id')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="guest_name">Guest Name *</label>
            <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required>
            @error('guest_name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="guest_email">Guest Email</label>
                <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email') }}">
                @error('guest_email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="guest_phone">Guest Phone</label>
                <input type="text" id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}">
                @error('guest_phone')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="check_in">Check In *</label>
                <input type="date" id="check_in" name="check_in" value="{{ old('check_in') }}" required>
                @error('check_in')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="check_out">Check Out *</label>
                <input type="date" id="check_out" name="check_out" value="{{ old('check_out') }}" required>
                @error('check_out')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="adults">Adults *</label>
                <input type="number" id="adults" name="adults" value="{{ old('adults', 1) }}" required min="1" max="100">
                @error('adults')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="children">Children</label>
                <input type="number" id="children" name="children" value="{{ old('children', 0) }}" min="0" max="100">
                @error('children')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div id="guest-warning" style="display: none; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; margin-bottom: 20px; color: #856404;">
            <strong>Warning:</strong> Total guests exceed room capacity!
        </div>

        <!-- Price Calculation Summary -->
        <div id="price-summary" style="display: none; background: #f8f9fa; border: 2px solid #667eea; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 15px; margin-top: 0;">Price Summary</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                <span style="color: #666;">Price per night:</span>
                <span style="font-weight: 600; color: #333;">$<span id="price-per-night">0.00</span></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                <span style="color: #666;">Number of nights:</span>
                <span style="font-weight: 600; color: #333;"><span id="nights-count">0</span> night(s)</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-top: 15px; border-top: 2px solid #667eea; margin-top: 10px;">
                <span style="font-size: 18px; font-weight: 700; color: #333;">Total Amount:</span>
                <span style="font-size: 20px; font-weight: 700; color: #667eea;">$<span id="calculated-total">0.00</span></span>
            </div>
        </div>

        <div class="form-group">
            <label for="total_amount">Total Amount *</label>
            <input type="number" id="total_amount" name="total_amount" value="{{ old('total_amount') }}" step="0.01" min="0" required readonly style="background: #f8f9fa; cursor: not-allowed;">
            <small style="color: #666; margin-top: 5px; display: block;">This amount is automatically calculated based on room price and number of nights.</small>
            @error('total_amount')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" id="submit-btn">Create Booking</button>
            <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomSelect = document.getElementById('room_id');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const adultsInput = document.getElementById('adults');
    const childrenInput = document.getElementById('children');
    const capacityInfo = document.getElementById('capacity-info');
    const guestWarning = document.getElementById('guest-warning');
    const submitBtn = document.getElementById('submit-btn');
    const totalAmountInput = document.getElementById('total_amount');
    const priceSummary = document.getElementById('price-summary');
    const pricePerNightSpan = document.getElementById('price-per-night');
    const nightsCountSpan = document.getElementById('nights-count');
    const calculatedTotalSpan = document.getElementById('calculated-total');
    
    function updateCapacityInfo() {
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        if (selectedOption.value) {
            const capacity = parseInt(selectedOption.getAttribute('data-capacity'));
            capacityInfo.textContent = `Room capacity: ${capacity} guests`;
            capacityInfo.style.display = 'block';
        } else {
            capacityInfo.style.display = 'none';
        }
        validateGuests();
        calculateTotal();
    }
    
    function validateGuests() {
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        if (!selectedOption.value) {
            guestWarning.style.display = 'none';
            submitBtn.disabled = false;
            return;
        }
        
        const capacity = parseInt(selectedOption.getAttribute('data-capacity'));
        const adults = parseInt(adultsInput.value) || 0;
        const children = parseInt(childrenInput.value) || 0;
        const totalGuests = adults + children;
        
        if (totalGuests > capacity) {
            guestWarning.style.display = 'block';
            guestWarning.innerHTML = `<strong>Warning:</strong> Total guests (${totalGuests}) exceed room capacity of ${capacity}!`;
            submitBtn.disabled = true;
        } else {
            guestWarning.style.display = 'none';
            submitBtn.disabled = false;
        }
    }
    
    function calculateTotal() {
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        
        // Hide summary if room not selected or dates not filled
        if (!selectedOption.value || !checkIn || !checkOut) {
            priceSummary.style.display = 'none';
            totalAmountInput.value = '';
            return;
        }
        
        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);
        
        // Validate dates
        if (checkOutDate <= checkInDate) {
            priceSummary.style.display = 'none';
            totalAmountInput.value = '';
            return;
        }
        
        // Calculate number of nights
        const diffTime = checkOutDate - checkInDate;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        // Get price per night
        const pricePerNight = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        
        // Calculate total
        const total = pricePerNight * diffDays;
        
        // Update display
        pricePerNightSpan.textContent = pricePerNight.toFixed(2);
        nightsCountSpan.textContent = diffDays;
        calculatedTotalSpan.textContent = total.toFixed(2);
        totalAmountInput.value = total.toFixed(2);
        
        // Show summary
        priceSummary.style.display = 'block';
    }
    
    // Event listeners
    roomSelect.addEventListener('change', updateCapacityInfo);
    checkInInput.addEventListener('change', calculateTotal);
    checkOutInput.addEventListener('change', calculateTotal);
    adultsInput.addEventListener('input', validateGuests);
    childrenInput.addEventListener('input', validateGuests);
    
    // Initialize on page load
    updateCapacityInfo();
    
    // Calculate if old values exist
    if (checkInInput.value && checkOutInput.value && roomSelect.value) {
        calculateTotal();
    }
});
</script>
@endpush
@endsection
