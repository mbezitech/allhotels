<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Sale Details - Hotel Management</title>
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
            max-width: 800px;
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
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            background: #95a5a6;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .total-row.final {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>POS Sale Details</h1>
        <div>
            <a href="{{ route('pos-sales.index') }}" class="btn">Back to Sales</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Sale Information</h2>
            <div style="margin-bottom: 15px;">
                <strong>Date:</strong> {{ $posSale->sale_date->format('M d, Y') }}
            </div>
            @if($posSale->room)
                <div style="margin-bottom: 15px;">
                    <strong>Room:</strong> {{ $posSale->room->room_number }}
                </div>
            @endif
            <div style="margin-bottom: 15px;">
                <strong>Payment Status:</strong> {{ ucfirst($posSale->payment_status) }}
            </div>
            <div style="margin-bottom: 15px;">
                <strong>Total Paid:</strong> ${{ number_format($posSale->total_paid, 2) }}
            </div>
            <div style="margin-bottom: 15px;">
                <strong>Outstanding Balance:</strong> ${{ number_format($posSale->outstanding_balance, 2) }}
            </div>
            @if($posSale->notes)
                <div style="margin-bottom: 15px;">
                    <strong>Notes:</strong> {{ $posSale->notes }}
                </div>
            @endif
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posSale->items as $item)
                        <tr>
                            <td>{{ $item->extra->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->unit_price, 2) }}</td>
                            <td>${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>${{ number_format($posSale->total_amount, 2) }}</span>
                </div>
                @if($posSale->discount > 0)
                    <div class="total-row">
                        <span>Discount:</span>
                        <span>-${{ number_format($posSale->discount, 2) }}</span>
                    </div>
                @endif
                <div class="total-row final">
                    <span>Total:</span>
                    <span>${{ number_format($posSale->final_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Payments</h2>
                @if($posSale->outstanding_balance > 0)
                    <a href="{{ route('payments.create', ['pos_sale_id' => $posSale->id]) }}" class="btn" style="background: #667eea; color: white;">Add Payment</a>
                @endif
            </div>
            
            @php
                $posSale->load('payments');
            @endphp

            @if($posSale->payments->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posSale->payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                                <td>${{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>{{ $payment->reference_number ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('payments.show', $payment) }}" class="btn" style="background: #3498db; color: white;">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #999; text-align: center;">No payments recorded</p>
            @endif
        </div>
    </div>
</body>
</html>

