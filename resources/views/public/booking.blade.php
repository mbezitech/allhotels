<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book {{ $room->room_number }} - {{ $hotel->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #667eea;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
        }
        .booking-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
            }
        }
        .room-details {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .room-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #e0e0e0;
        }
        .room-image-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .room-title {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }
        .room-type {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .room-info {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .room-info-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .price {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        .booking-form {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .price-total {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .error {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $hotel->name }}</h1>
            <p>{{ $hotel->address }}</p>
        </div>

        <div class="booking-container">
            <!-- Room Details -->
            <div class="room-details">
                <h2 class="room-title">Room {{ $room->room_number }}</h2>
                @if($room->roomType)
                    <div class="room-type">{{ $room->roomType->name }}</div>
                @endif

                @if($room->images && count($room->images) > 0)
                    <img src="{{ $room->images[0] }}" alt="Room {{ $room->room_number }}" class="room-image">
                @else
                    <div class="room-image-placeholder">Room {{ $room->room_number }}</div>
                @endif

                <div class="price">${{ number_format($room->price_per_night, 2) }} <span style="font-size: 16px; font-weight: normal;">per night</span></div>

                @if($room->description)
                    <p style="margin: 15px 0; color: #666;">{{ $room->description }}</p>
                @endif

                <div class="room-info">
                    <div class="room-info-item">
                        <span>Max Guests:</span>
                        <strong>{{ $room->capacity }}</strong>
                    </div>
                    @if($room->floor)
                        <div class="room-info-item">
                            <span>Floor:</span>
                            <strong>{{ $room->floor }}</strong>
                        </div>
                    @endif
                    @if($room->amenities && count($room->amenities) > 0)
                        <div style="margin-top: 15px;">
                            <strong>Amenities:</strong>
                            <div style="margin-top: 8px;">
                                @foreach($room->amenities as $amenity)
                                    <span style="display: inline-block; background: #e3f2fd; padding: 4px 8px; border-radius: 4px; margin: 4px 4px 4px 0; font-size: 12px;">{{ $amenity }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Booking Form -->
            <div class="booking-form">
                <h2 style="margin-bottom: 20px;">Book This Room</h2>

                @if($errors->any())
                    <div class="alert alert-error">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.booking.store', ['hotel_slug' => $hotel->slug, 'room_id' => $room->id]) }}" id="bookingForm">
                    @csrf

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="check_in">Check In *</label>
                            <input type="date" id="check_in" name="check_in" value="{{ old('check_in', $checkIn ?? '') }}" required min="{{ date('Y-m-d') }}" onchange="calculatePrice()">
                            @error('check_in')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="check_out">Check Out *</label>
                            <input type="date" id="check_out" name="check_out" value="{{ old('check_out', $checkOut ?? '') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}" onchange="calculatePrice()">
                            @error('check_out')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="price-summary" id="priceSummary" style="display: none;">
                        <div class="price-row">
                            <span>Price per night:</span>
                            <span>$<span id="pricePerNight">{{ number_format($room->price_per_night, 2) }}</span></span>
                        </div>
                        <div class="price-row">
                            <span>Number of nights:</span>
                            <span id="nights">0</span>
                        </div>
                        <div class="price-row price-total">
                            <span>Total:</span>
                            <span>$<span id="totalAmount">0.00</span></span>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="adults">Adults *</label>
                            <input type="number" id="adults" name="adults" value="{{ old('adults', 1) }}" required min="1" max="{{ $room->capacity }}">
                            @error('adults')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="children">Children</label>
                            <input type="number" id="children" name="children" value="{{ old('children', 0) }}" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="guest_name">Full Name *</label>
                        <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required>
                        @error('guest_name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="guest_email">Email Address *</label>
                        <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email') }}" required>
                        @error('guest_email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="guest_phone">Phone Number *</label>
                        <input type="tel" id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}" required>
                        @error('guest_phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn" id="submitBtn">Complete Booking</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const pricePerNight = {{ $room->price_per_night }};
        const maxGuests = {{ $room->capacity }};

        function calculatePrice() {
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            const priceSummary = document.getElementById('priceSummary');

            if (checkIn && checkOut) {
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                
                if (checkOutDate > checkInDate) {
                    const diffTime = checkOutDate - checkInDate;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const total = pricePerNight * diffDays;

                    document.getElementById('nights').textContent = diffDays;
                    document.getElementById('totalAmount').textContent = total.toFixed(2);
                    priceSummary.style.display = 'block';
                } else {
                    priceSummary.style.display = 'none';
                }
            } else {
                priceSummary.style.display = 'none';
            }
        }

        // Calculate on page load if dates are already filled
        if (document.getElementById('check_in').value && document.getElementById('check_out').value) {
            calculatePrice();
        }
    </script>
</body>
</html>

