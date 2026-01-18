@extends('layouts.app')

@section('title', 'New POS Sale')
@section('page-title', 'New POS Sale')

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    input, select, textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
    }
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
    .btn-danger {
        background: #e74c3c;
        color: white;
    }
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    .cart-total {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 20px;
    }
    .category-section {
        margin-bottom: 30px;
    }
    .category-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }
    .extra-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: all 0.2s;
    }
    .extra-item:hover {
        background: #f8f9fa;
        border-color: #667eea;
    }
    .extra-item-info {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
    }
    .extra-item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }
    .extra-item-details {
        flex: 1;
    }
    .extra-item-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    .extra-item-price {
        color: #667eea;
        font-weight: 600;
        font-size: 16px;
    }
    .extra-item-stock {
        font-size: 12px;
        color: #666;
        margin-top: 3px;
    }
    .stock-low {
        color: #dc3545;
        font-weight: 600;
    }
    input[type="number"] {
        width: 80px;
        padding: 8px;
        text-align: center;
    }
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="container">
        <form method="POST" action="{{ route('pos-sales.store') }}" id="posForm">
            @csrf

            <div class="card">
                <h2>Sale Information</h2>
                <div class="grid-2">
                    <div class="form-group">
                        <label for="sale_date">Sale Date *</label>
                        <input type="date" id="sale_date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="room_id">Room (Optional)</label>
                        <select id="room_id" name="room_id" onchange="updateBookings()">
                            <option value="">-- No Room --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" id="booking_group">
                    <label for="booking_id">Attach to Guest Booking (Optional)</label>
                    <select id="booking_id" name="booking_id">
                        <option value="">-- No Booking --</option>
                        @foreach($activeBookings as $booking)
                            <option value="{{ $booking->id }}" data-room-id="{{ $booking->room_id }}">
                                {{ $booking->guest_name }} - Room {{ $booking->room->room_number }} 
                                ({{ ucfirst($booking->status) }} | Check-in: {{ $booking->check_in->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Select a booking to attach this charge to the guest's room bill. Charges will be included in checkout balance. 
                        <span id="booking_filter_hint" style="color: #999; font-style: italic;"></span>
                    </small>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="card">
                <h2>Select Items</h2>
                @foreach($extras as $category => $items)
                    <div class="category-section">
                        <div class="category-title">{{ ucfirst($category) }}</div>
                        @foreach($items as $extra)
                            <div class="extra-item">
                                <div class="extra-item-info">
                                    @if($extra->images && count($extra->images) > 0)
                                        <img src="{{ Storage::url($extra->images[0]) }}" alt="{{ $extra->name }}" class="extra-item-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\'%3E%3Crect fill=\'%23e0e0e0\' width=\'60\' height=\'60\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-size=\'10\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                    @else
                                        <div style="width: 60px; height: 60px; background: #e0e0e0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 10px;">No Image</div>
                                    @endif
                                    <div class="extra-item-details">
                                        <div class="extra-item-name">{{ $extra->name }}</div>
                                        <div class="extra-item-price">${{ number_format($extra->price, 2) }}</div>
                                        @if($extra->stock_tracked)
                                            <div class="extra-item-stock {{ $extra->is_low_stock ? 'stock-low' : '' }}">
                                                Stock: {{ $extra->current_stock ?? 0 }}
                                                @if($extra->is_low_stock)
                                                    ⚠️ Low Stock
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="quantity-controls">
                                    <button type="button" onclick="decreaseQuantity({{ $extra->id }})" style="padding: 5px 10px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer;">-</button>
                                    <input type="number" 
                                           name="items[{{ $extra->id }}][quantity]" 
                                           id="qty-{{ $extra->id }}"
                                           value="0" 
                                           min="0" 
                                           class="item-quantity"
                                           data-extra-id="{{ $extra->id }}"
                                           data-price="{{ $extra->price }}"
                                           onchange="updateCart()">
                                    <button type="button" onclick="increaseQuantity({{ $extra->id }})" style="padding: 5px 10px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer;">+</button>
                                    <input type="hidden" name="items[{{ $extra->id }}][extra_id]" value="{{ $extra->id }}">
                                    <input type="hidden" name="items[{{ $extra->id }}][unit_price]" value="{{ $extra->price }}" class="unit-price-{{ $extra->id }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="card">
                <h2>Cart Summary</h2>
                <div id="cartItems"></div>
                <div class="cart-total">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #dee2e6;">
                        <strong style="font-size: 16px;">Subtotal:</strong>
                        <span id="subtotal" style="font-size: 16px; font-weight: 600;">$0.00</span>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px; margin-top: 15px;">
                        <label for="discount" style="font-weight: 500;">Discount ($)</label>
                        <input type="number" id="discount" name="discount" value="0" step="0.01" min="0" oninput="updateCart()" onchange="updateCart()" placeholder="0.00" style="width: 100%;">
                        <small style="color: #666; display: block; margin-top: 5px;">Enter discount amount in dollars</small>
                        <div id="discount-warning" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;">
                            ⚠️ Discount cannot exceed subtotal
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: bold; padding-top: 15px; border-top: 2px solid #667eea; margin-top: 10px; color: #667eea;">
                        <span>Total:</span>
                        <span id="total">$0.00</span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <a href="{{ route('pos-sales.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Complete Sale</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
        function increaseQuantity(extraId) {
            const input = document.getElementById('qty-' + extraId);
            const currentValue = parseInt(input.value) || 0;
            input.value = currentValue + 1;
            updateCart();
        }
        
        function decreaseQuantity(extraId) {
            const input = document.getElementById('qty-' + extraId);
            const currentValue = parseInt(input.value) || 0;
            if (currentValue > 0) {
                input.value = currentValue - 1;
                updateCart();
            }
        }
        
        function updateCart() {
            const quantities = document.querySelectorAll('.item-quantity');
            const cartItems = document.getElementById('cartItems');
            const subtotalEl = document.getElementById('subtotal');
            const totalEl = document.getElementById('total');
            const discountEl = document.getElementById('discount');
            
            let items = [];
            let subtotal = 0;

            quantities.forEach(qty => {
                const quantity = parseInt(qty.value) || 0;
                if (quantity > 0) {
                    const extraId = qty.dataset.extraId;
                    const price = parseFloat(qty.dataset.price);
                    const extraItem = qty.closest('.extra-item');
                    const extraNameEl = extraItem.querySelector('.extra-item-name');
                    const extraName = extraNameEl ? extraNameEl.textContent.trim() : 'Item #' + extraId;
                    
                    const itemTotal = quantity * price;
                    subtotal += itemTotal;
                    
                    items.push({
                        name: extraName,
                        quantity: quantity,
                        price: price,
                        total: itemTotal
                    });
                }
            });

            // Update cart display
            if (items.length === 0) {
                cartItems.innerHTML = '<p style="color: #999; text-align: center;">No items selected</p>';
            } else {
                cartItems.innerHTML = items.map(item => `
                    <div class="cart-item">
                        <div>
                            <strong>${item.name}</strong>
                            <div style="color: #666; font-size: 12px;">${item.quantity} x $${item.price.toFixed(2)}</div>
                        </div>
                        <div>$${item.total.toFixed(2)}</div>
                    </div>
                `).join('');
            }

            const discount = parseFloat(discountEl.value) || 0;
            const total = Math.max(0, subtotal - discount);
            const discountWarning = document.getElementById('discount-warning');

            subtotalEl.textContent = '$' + subtotal.toFixed(2);
            totalEl.textContent = '$' + total.toFixed(2);
            
            // Update discount display if it exceeds subtotal
            if (discount > subtotal && subtotal > 0) {
                discountEl.style.borderColor = '#dc3545';
                discountEl.style.backgroundColor = '#fff5f5';
                if (discountWarning) {
                    discountWarning.style.display = 'block';
                }
            } else {
                discountEl.style.borderColor = '#e0e0e0';
                discountEl.style.backgroundColor = 'white';
                if (discountWarning) {
                    discountWarning.style.display = 'none';
                }
            }
        }

        // Filter out items with quantity 0 before submit
        document.getElementById('posForm').addEventListener('submit', function(e) {
            const quantities = document.querySelectorAll('.item-quantity');
            let hasItems = false;
            
            quantities.forEach(qty => {
                if (parseInt(qty.value) > 0) {
                    hasItems = true;
                } else {
                    // Remove items with 0 quantity
                    const extraId = qty.dataset.extraId;
                    const itemInputs = document.querySelectorAll(`[name*="[${extraId}]"]`);
                    itemInputs.forEach(input => input.remove());
                }
            });

            if (!hasItems) {
                e.preventDefault();
                alert('Please select at least one item.');
                return false;
            }
        });

        updateCart();

        // Update bookings dropdown when room changes
        function updateBookings() {
            const roomId = document.getElementById('room_id').value;
            const bookingSelect = document.getElementById('booking_id');
            const bookingGroup = document.getElementById('booking_group');
            
            if (roomId) {
                bookingGroup.style.display = 'block';
                // Filter bookings by selected room
                const options = bookingSelect.querySelectorAll('option');
                options.forEach(option => {
                    if (option.value === '') {
                        option.style.display = 'block';
                    } else {
                        const optionRoomId = option.getAttribute('data-room-id');
                        if (optionRoomId === roomId) {
                            option.style.display = 'block';
                        } else {
                            option.style.display = 'none';
                        }
                    }
                });
            } else {
                bookingGroup.style.display = 'none';
                bookingSelect.value = '';
            }
        }
    </script>
@endpush
@endsection

