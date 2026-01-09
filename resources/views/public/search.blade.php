<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Rooms - {{ $hotel->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 18px;
            opacity: 0.9;
        }
        .search-box {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        .form-group input {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-search {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-search:hover {
            background: #5568d3;
        }
        .results-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .results-header {
            margin-bottom: 25px;
        }
        .results-header h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .results-header p {
            color: #666;
            font-size: 14px;
        }
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        .room-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .room-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            position: relative;
            overflow: hidden;
        }
        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .room-image .placeholder {
            position: absolute;
            font-size: 48px;
        }
        .room-content {
            padding: 20px;
        }
        .room-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .room-number {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }
        .room-type {
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
        }
        .room-price {
            text-align: right;
        }
        .price-amount {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        .price-label {
            font-size: 12px;
            color: #999;
        }
        .room-details {
            margin-bottom: 20px;
        }
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
            color: #666;
        }
        .detail-item strong {
            margin-right: 8px;
            color: #333;
        }
        .btn-book {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: background 0.3s;
        }
        .btn-book:hover {
            background: #5568d3;
        }
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .no-results-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .no-results h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        .no-results p {
            font-size: 16px;
        }
        .date-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .date-info-item {
            display: flex;
            flex-direction: column;
        }
        .date-info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .date-info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            .rooms-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $hotel->name }}</h1>
            <p>Find your perfect room</p>
        </div>

        <div class="search-box">
            <form action="{{ route('public.search', $hotel->slug) }}" method="GET" class="search-form">
                <div class="form-group">
                    <label for="check_in">Check-In Date</label>
                    <input type="date" 
                           id="check_in" 
                           name="check_in" 
                           value="{{ $checkIn ?? '' }}"
                           min="{{ date('Y-m-d') }}"
                           required>
                </div>
                <div class="form-group">
                    <label for="check_out">Check-Out Date</label>
                    <input type="date" 
                           id="check_out" 
                           name="check_out" 
                           value="{{ $checkOut ?? '' }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           required>
                </div>
                <button type="submit" class="btn-search">Search Rooms</button>
            </form>
        </div>

        @if($checkIn && $checkOut)
            <div class="results-section">
                <div class="date-info">
                    <div class="date-info-item">
                        <span class="date-info-label">Check-In</span>
                        <span class="date-info-value">{{ \Carbon\Carbon::parse($checkIn)->format('M d, Y') }}</span>
                    </div>
                    <div class="date-info-item">
                        <span class="date-info-label">Check-Out</span>
                        <span class="date-info-value">{{ \Carbon\Carbon::parse($checkOut)->format('M d, Y') }}</span>
                    </div>
                    <div class="date-info-item">
                        <span class="date-info-label">Nights</span>
                        <span class="date-info-value">{{ \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)) }}</span>
                    </div>
                </div>

                <div class="results-header">
                    <h2>Available Rooms</h2>
                    <p>{{ $availableRooms->count() }} room(s) available for your selected dates</p>
                </div>

                @if($availableRooms->count() > 0)
                    <div class="rooms-grid">
                        @foreach($availableRooms as $room)
                            @php
                                $nights = \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut));
                                $totalPrice = $room->price_per_night * $nights;
                                $firstImage = $room->images && count($room->images) > 0 ? $room->images[0] : null;
                            @endphp
                            <div class="room-card">
                                <div class="room-image">
                                    @if($firstImage)
                                        <img src="{{ $firstImage }}" alt="Room {{ $room->room_number }}">
                                    @else
                                        <span class="placeholder">🏨</span>
                                    @endif
                                </div>
                                <div class="room-content">
                                    <div class="room-header">
                                        <div>
                                            <div class="room-number">Room {{ $room->room_number }}</div>
                                            <div class="room-type">{{ $room->roomType->name ?? 'Standard' }}</div>
                                        </div>
                                        <div class="room-price">
                                            <div class="price-amount">${{ number_format($room->price_per_night, 2) }}</div>
                                            <div class="price-label">per night</div>
                                        </div>
                                    </div>
                                    <div class="room-details">
                                        <div class="detail-item">
                                            <strong>Capacity:</strong> {{ $room->capacity }} guests
                                        </div>
                                        @if($room->floor)
                                            <div class="detail-item">
                                                <strong>Floor:</strong> {{ $room->floor }}
                                            </div>
                                        @endif
                                        @if($room->description)
                                            <div class="detail-item">
                                                <strong>Description:</strong> {{ Str::limit($room->description, 100) }}
                                            </div>
                                        @endif
                                        <div class="detail-item">
                                            <strong>Total:</strong> ${{ number_format($totalPrice, 2) }} for {{ $nights }} night(s)
                                        </div>
                                    </div>
                                    <a href="{{ route('public.booking.show', ['hotel_slug' => $hotel->slug, 'room_id' => $room->id]) }}?check_in={{ $checkIn }}&check_out={{ $checkOut }}" 
                                       class="btn-book">
                                        Book Now
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-results">
                        <div class="no-results-icon">🔍</div>
                        <h3>No Rooms Available</h3>
                        <p>Sorry, there are no available rooms for the selected dates. Please try different dates.</p>
                    </div>
                @endif
            </div>
        @else
            <div class="results-section">
                <div class="no-results">
                    <div class="no-results-icon">📅</div>
                    <h3>Select Your Dates</h3>
                    <p>Please select your check-in and check-out dates to search for available rooms.</p>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Set minimum check-out date based on check-in date
        document.getElementById('check_in')?.addEventListener('change', function() {
            const checkIn = this.value;
            const checkOutInput = document.getElementById('check_out');
            if (checkIn && checkOutInput) {
                const minDate = new Date(checkIn);
                minDate.setDate(minDate.getDate() + 1);
                checkOutInput.min = minDate.toISOString().split('T')[0];
                
                // If check-out is before new minimum, clear it
                if (checkOutInput.value && checkOutInput.value <= checkIn) {
                    checkOutInput.value = '';
                }
            }
        });
    </script>
</body>
</html>

