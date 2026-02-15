<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancellation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .booking-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #6b7280;
        }
        .detail-value {
            color: #111827;
        }
        .reference {
            background-color: #fee2e2;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .reference-number {
            font-size: 24px;
            font-weight: bold;
            color: #991b1b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .cancellation-notice {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Booking Cancellation</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $booking->guest_name }},</p>
        
        <p>We regret to inform you that your booking has been cancelled.</p>
        
        <div class="reference">
            <div style="font-size:14px; color:#6b7280; margin-bottom:5px;">Booking Reference</div>
            <div class="reference-number">{{ $booking->booking_reference }}</div>
        </div>
        
        <div class="cancellation-notice">
            <h3 style="margin-top:0; color:#dc2626;">Cancellation Details</h3>
            @if($booking->cancellation_reason)
                <p style="margin:0;"><strong>Reason:</strong> {{ $booking->cancellation_reason }}</p>
            @else
                <p style="margin:0;">Your booking has been cancelled.</p>
            @endif
        </div>
        
        <div class="booking-details">
            <h3 style="margin-top:0; color:#dc2626;">Original Booking Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Hotel:</span>
                <span class="detail-value">{{ $booking->hotel->name }}</span>
            </div>
            
            @if($booking->hotel->phone)
            <div class="detail-row">
                <span class="detail-label">Hotel Phone:</span>
                <span class="detail-value">{{ $booking->hotel->phone }}</span>
            </div>
            @endif
            
            @if($booking->hotel->email)
            <div class="detail-row">
                <span class="detail-label">Hotel Email:</span>
                <span class="detail-value">{{ $booking->hotel->email }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Room:</span>
                <span class="detail-value">
                    @if($booking->room->name)
                        {{ $booking->room->name }} ({{ $booking->room->room_number }})
                    @else
                        Room {{ $booking->room->room_number }}
                    @endif
                    @if($booking->room->roomType)
                        - {{ $booking->room->roomType->name }}
                    @endif
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Check-in:</span>
                <span class="detail-value">{{ $booking->check_in->format('l, F j, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Check-out:</span>
                <span class="detail-value">{{ $booking->check_out->format('l, F j, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Number of Nights:</span>
                <span class="detail-value">{{ $booking->nights }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Guests:</span>
                <span class="detail-value">
                    {{ $booking->adults }} {{ Str::plural('Adult', $booking->adults) }}
                    @if($booking->children)
                        , {{ $booking->children }} {{ Str::plural('Child', $booking->children) }}
                    @endif
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                    <span class="status-badge status-cancelled">CANCELLED</span>
                </span>
            </div>
        </div>
        
        <div class="booking-details">
            <h3 style="margin-top:0; color:#dc2626;">Payment Information</h3>
            
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value">${{ number_format($booking->total_amount, 2) }}</span>
            </div>
            
            @if($booking->discount > 0)
            <div class="detail-row">
                <span class="detail-label">Discount:</span>
                <span class="detail-value">-${{ number_format($booking->discount, 2) }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Final Amount:</span>
                <span class="detail-value">${{ number_format($booking->final_amount, 2) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value">${{ number_format($booking->total_paid, 2) }}</span>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background-color: #fef3c7; border-radius: 5px; border-left: 4px solid #f59e0b;">
            <p style="margin:0;"><strong>Note:</strong> If you have made any payments, please contact us regarding refund processing. Our team will assist you with the refund procedure according to our cancellation policy.</p>
        </div>
        
        <div style="margin-top: 20px;">
            <h4 style="color:#dc2626;">Need Assistance?</h4>
            <p>If you have any questions or would like to make a new booking, please contact us:</p>
            <p>
                <strong>{{ $booking->hotel->name }}</strong><br>
                @if($booking->hotel->address)
                    {{ $booking->hotel->address }}<br>
                @endif
                @if($booking->hotel->phone)
                    Phone: {{ $booking->hotel->phone }}<br>
                @endif
                @if($booking->hotel->email)
                    Email: {{ $booking->hotel->email }}
                @endif
            </p>
        </div>
        
        <p>We apologize for any inconvenience this may have caused. We hope to serve you in the future.</p>
        
        <p>Best regards,<br>
        <strong>{{ $booking->hotel->name }} Team</strong></p>
    </div>
    
    <div class="footer">
        <p>This is an automated notification email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} {{ $booking->hotel->name }}. All rights reserved.</p>
    </div>
</body>
</html>
