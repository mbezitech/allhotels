<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancellation Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
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
        <h1>⚠️ Booking Cancelled</h1>
    </div>
    <div class="content">
        <p>Hello Team,</p>
        <p>A booking has been <strong>CANCELLED</strong> for <strong>{{ $booking->hotel->name }}</strong>.</p>

        <div class="booking-details">
            <h3 style="margin-top:0; color:#dc2626;">Cancellation Details</h3>
            <div class="detail-row">
                <span class="detail-label">Reference:</span>
                <span class="detail-value"><strong>{{ $booking->booking_reference }}</strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Guest Name:</span>
                <span class="detail-value">{{ $booking->guest_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Was Scheduled for:</span>
                <span class="detail-value">{{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}</span>
            </div>
            @if($booking->cancellation_reason)
            <div class="detail-row">
                <span class="detail-label">Reason:</span>
                <span class="detail-value">{{ $booking->cancellation_reason }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value">${{ number_format($booking->final_amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value">{{ $booking->paid_amount >= $booking->final_amount ? 'FULLY PAID' : ($booking->paid_amount > 0 ? 'PARTIALLY PAID' : 'UNPAID') }}</span>
            </div>
        </div>

        <p>Please review any refund requirements according to the hotel policy.</p>
    </div>
    <div class="footer">
        <p>Sent via AllHotels Management System</p>
    </div>
</body>
</html>
