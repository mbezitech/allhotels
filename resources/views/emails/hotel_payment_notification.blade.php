<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #059669; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
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
        <h1>💰 Payment Received</h1>
    </div>
    <div class="content">
        <p>Hello Team,</p>
        <p>A new payment has been recorded for <strong>{{ $payment->hotel->name }}</strong>.</p>

        <div class="booking-details">
            <h3 style="margin-top:0; color:#059669;">Payment Details</h3>
            <div class="detail-row">
                <span class="detail-label">Amount:</span>
                <span class="detail-value" style="font-size: 18px; font-weight: bold; color: #059669;">${{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Method:</span>
                <span class="detail-value">{{ ucfirst($payment->payment_method) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ $payment->paid_at->format('M d, Y H:i') }}</span>
            </div>
            @if($payment->reference_number)
            <div class="detail-row">
                <span class="detail-label">Reference:</span>
                <span class="detail-value">{{ $payment->reference_number }}</span>
            </div>
            @endif
            
            @if($payment->booking)
            <div class="detail-row" style="border-top: 2px solid #e5e7eb; margin-top: 10px; padding-top: 10px;">
                <span class="detail-label">Booking:</span>
                <span class="detail-value">{{ $payment->booking->booking_reference }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Guest:</span>
                <span class="detail-value">{{ $payment->booking->guest_name }}</span>
            </div>
            @elseif($payment->posSale)
            <div class="detail-row" style="border-top: 2px solid #e5e7eb; margin-top: 10px; padding-top: 10px;">
                <span class="detail-label">POS Sale:</span>
                <span class="detail-value">{{ $payment->posSale->sale_reference }}</span>
            </div>
            @endif
        </div>

        <p>Recorded by: {{ $payment->receivedBy->name }}</p>
    </div>
    <div class="footer">
        <p>Sent via AllHotels Management System</p>
    </div>
</body>
</html>
