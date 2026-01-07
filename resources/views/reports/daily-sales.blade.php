<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report - Hotel Management</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input {
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Sales Report</h1>
        <div>
            <a href="{{ route('reports.index') }}" class="btn">Back to Reports</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Filter Options</h2>
            <form method="GET" action="{{ route('reports.daily-sales') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <div class="form-group">
                    <label for="date">Single Day</label>
                    <input type="date" id="date" name="date" value="{{ $date }}">
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date (Range)</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date (Range)</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Daily Summary - {{ Carbon\Carbon::parse($date)->format('M d, Y') }}</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">${{ number_format($dailyTotal, 2) }}</div>
                    <div class="stat-label">Total Sales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $dailyCount }}</div>
                    <div class="stat-label">Number of Sales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${{ $dailyCount > 0 ? number_format($dailyTotal / $dailyCount, 2) : '0.00' }}</div>
                    <div class="stat-label">Average Sale</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Date Range Summary ({{ Carbon\Carbon::parse($startDate)->format('M d') }} - {{ Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">${{ number_format($rangeTotal, 2) }}</div>
                    <div class="stat-label">Total Sales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $rangeCount }}</div>
                    <div class="stat-label">Total Transactions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${{ number_format($averageDaily, 2) }}</div>
                    <div class="stat-label">Average Daily</div>
                </div>
            </div>
        </div>

        @if($dailySales->count() > 0)
            <div class="card">
                <h2 style="margin-bottom: 20px;">Sales for {{ Carbon\Carbon::parse($date)->format('M d, Y') }}</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Date</th>
                            <th>Room</th>
                            <th>Items</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailySales as $sale)
                            <tr>
                                <td>#{{ $sale->id }}</td>
                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                <td>{{ $sale->room ? $sale->room->room_number : '-' }}</td>
                                <td>{{ $sale->items->count() }} item(s)</td>
                                <td><strong>${{ number_format($sale->final_amount, 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($salesByDate->count() > 0)
            <div class="card">
                <h2 style="margin-bottom: 20px;">Sales by Date</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Sales Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesByDate as $sale)
                            <tr>
                                <td>{{ Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</td>
                                <td>{{ $sale->count }}</td>
                                <td><strong>${{ number_format($sale->total, 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>

