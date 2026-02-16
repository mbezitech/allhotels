<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1e40af; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .booking-details { background-color: white; padding: 20px; margin: 20px 0; border-radius: 5px; border: 1px solid #e5e7eb; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: bold; color: #6b7280; }
        .detail-value { color: #111827; text-align: right; }
        .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔔 New Booking Recieved</h1>
    </div>
    <div class="content">
        <p>Hello Team,</p>
        <p>A new booking has been recorded for <strong>{{ $booking->hotel->name }}</strong>.</p>

        <div class="booking-details">
            <h3 style="margin-top:0; color:#1e40af;">Booking Summary</h3>
            <div class="detail-row">
                <span class="detail-label">Reference:</span>
                <span class="detail-value"><strong>{{ $booking->booking_reference }}</strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Guest Name:</span>
                <span class="detail-value">{{ $booking->guest_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-in:</span>
                <span class="detail-value">{{ $booking->check_in->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-out:</span>
                <span class="detail-value">{{ $booking->check_out->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Room:</span>
                <span class="detail-value">
                    {{ $booking->room->room_number }} 
                    @if($booking->room->roomType)
                        ({{ $booking->room->roomType->name }})
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value">${{ number_format($booking->final_amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value" style="color: #059669; font-weight: bold;">{{ strtoupper($booking->status) }}</span>
            </div>
        </div>

        @if($booking->notes)
        <div class="booking-details">
            <h3 style="margin-top:0; color:#1e40af;">Guest Notes</h3>
            <p style="margin:0; font-size: 14px;">{{ $booking->notes }}</p>
        </div>
        @endif

        <p>Please log in to the admin panel to view full details.</p>
    </div>
    <div class="footer">
        <p>Sent via AllHotels Management System</p>
    </div>
</body>
</html>
