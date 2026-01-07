<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Occupancy Report - Hotel Management</title>
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
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-high { background: #d4edda; color: #155724; }
        .badge-medium { background: #fff3cd; color: #856404; }
        .badge-low { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Occupancy Report</h1>
        <div>
            <a href="{{ route('reports.index') }}" class="btn">Back to Reports</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Filter Options</h2>
            <form method="GET" action="{{ route('reports.occupancy') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
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
            <h2 style="margin-bottom: 20px;">Occupancy for {{ Carbon\Carbon::parse($date)->format('M d, Y') }}</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $totalRooms }}</div>
                    <div class="stat-label">Total Rooms</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $occupiedOnDate }}</div>
                    <div class="stat-label">Occupied</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $totalRooms - $occupiedOnDate }}</div>
                    <div class="stat-label">Available</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($occupancyRate, 1) }}%</div>
                    <div class="stat-label">Occupancy Rate</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Date Range Summary ({{ Carbon\Carbon::parse($startDate)->format('M d') }} - {{ Carbon\Carbon::parse($endDate)->format('M d, Y') }})</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($avgOccupied, 1) }}</div>
                    <div class="stat-label">Average Occupied</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($avgRate, 1) }}%</div>
                    <div class="stat-label">Average Occupancy Rate</div>
                </div>
            </div>
        </div>

        @if(count($occupancyByDate) > 0)
            <div class="card">
                <h2 style="margin-bottom: 20px;">Daily Occupancy Breakdown</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Occupancy Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($occupancyByDate as $day)
                            <tr>
                                <td>{{ Carbon\Carbon::parse($day['date'])->format('M d, Y') }}</td>
                                <td>{{ $day['occupied'] }}</td>
                                <td>{{ $day['available'] }}</td>
                                <td>
                                    {{ number_format($day['rate'], 1) }}%
                                    @if($day['rate'] >= 80)
                                        <span class="badge badge-high">High</span>
                                    @elseif($day['rate'] >= 50)
                                        <span class="badge badge-medium">Medium</span>
                                    @else
                                        <span class="badge badge-low">Low</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>

