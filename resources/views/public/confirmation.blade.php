<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - {{ $hotel->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .confirmation-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4caf50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
        }
        .confirmation-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        .confirmation-subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .booking-reference {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .reference-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .reference-number {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        .booking-details {
            text-align: left;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #666;
        }
        .detail-value {
            font-weight: 600;
            color: #333;
        }
        .total-amount {
            font-size: 24px;
            color: #667eea;
            font-weight: bold;
            margin-top: 15px;
        }
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .info-box strong {
            display: block;
            margin-bottom: 5px;
            color: #1976d2;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">✓</div>
        <h1 class="confirmation-title">Booking Confirmed!</h1>
        <p class="confirmation-subtitle">Thank you for your booking at {{ $hotel->name }}</p>

        <div class="booking-reference">
            <div class="reference-label">Booking Reference</div>
            <div class="reference-number">{{ $booking->booking_reference }}</div>
        </div>

        <div class="booking-details">
            <div class="detail-row">
                <span class="detail-label">Guest Name:</span>
                <span class="detail-value">{{ $booking->guest_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Room:</span>
                <span class="detail-value">Room {{ $booking->room->room_number }}</span>
            </div>
            @if($booking->room->roomType)
                <div class="detail-row">
                    <span class="detail-label">Room Type:</span>
                    <span class="detail-value">{{ $booking->room->roomType->name }}</span>
                </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Check In:</span>
                <span class="detail-value">{{ $booking->check_in->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check Out:</span>
                <span class="detail-value">{{ $booking->check_out->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Nights:</span>
                <span class="detail-value">{{ $booking->nights }} night(s)</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Guests:</span>
                <span class="detail-value">{{ $booking->adults }} adult(s){{ $booking->children > 0 ? ', ' . $booking->children . ' child(ren)' : '' }}</span>
            </div>
            <div class="total-amount">
                Total: ${{ number_format($booking->total_amount, 2) }}
            </div>
        </div>

        <div class="info-box">
            <strong>Important Information</strong>
            <p style="margin: 0; font-size: 14px; color: #333;">
                Your booking is confirmed! A confirmation email has been sent to <strong>{{ $booking->guest_email }}</strong>. 
                Please keep your booking reference number for your records. You can contact the hotel directly at 
                <strong>{{ $hotel->phone }}</strong> or <strong>{{ $hotel->email }}</strong> if you have any questions.
            </p>
        </div>

        <a href="/" class="btn">Return to Home</a>
    </div>
</body>
</html>

