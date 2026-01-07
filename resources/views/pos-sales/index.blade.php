<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Sales - Hotel Management</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-partial { background: #d1ecf1; color: #0c5460; }
        .badge-paid { background: #d4edda; color: #155724; }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>POS Sales</h1>
        <div>
            <a href="{{ route('dashboard') }}" class="btn" style="background: #95a5a6; color: white; margin-right: 10px;">Dashboard</a>
            <a href="{{ route('pos-sales.create') }}" class="btn btn-primary">New Sale</a>
        </div>
    </div>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <h2 style="margin-bottom: 20px;">All POS Sales</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Room</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                            <td>{{ $sale->room ? $sale->room->room_number : '-' }}</td>
                            <td>{{ $sale->items->count() }} item(s)</td>
                            <td>${{ number_format($sale->final_amount, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $sale->payment_status }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('pos-sales.show', $sale) }}" class="btn" style="background: #3498db; color: white;">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999;">No sales found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</body>
</html>

