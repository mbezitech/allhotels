<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New POS Sale - Hotel Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 30px;
        }
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
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .extra-item:hover {
            background: #f8f9fa;
        }
        input[type="number"] {
            width: 80px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New POS Sale</h1>
    </div>

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
                        <select id="room_id" name="room_id">
                            <option value="">-- No Room --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
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
                                <div>
                                    <strong>{{ $extra->name }}</strong>
                                    <div style="color: #666; font-size: 12px;">${{ number_format($extra->price, 2) }}</div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="number" 
                                           name="items[{{ $extra->id }}][quantity]" 
                                           value="0" 
                                           min="0" 
                                           class="item-quantity"
                                           data-extra-id="{{ $extra->id }}"
                                           data-price="{{ $extra->price }}"
                                           onchange="updateCart()">
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
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong>Subtotal:</strong>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label for="discount">Discount</label>
                        <input type="number" id="discount" name="discount" value="0" step="0.01" min="0" onchange="updateCart()">
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold;">
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

    <script>
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
                    const unitPriceEl = document.querySelector(`.unit-price-${extraId}`);
                    const extraName = qty.closest('.extra-item').querySelector('strong').textContent;
                    
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
            const total = subtotal - discount;

            subtotalEl.textContent = '$' + subtotal.toFixed(2);
            totalEl.textContent = '$' + total.toFixed(2);
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
    </script>
</body>
</html>

