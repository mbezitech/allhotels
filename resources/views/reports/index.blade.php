<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Hotel Management</title>
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
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .card h2 {
            margin-bottom: 10px;
            color: #333;
        }
        .card p {
            color: #666;
            margin: 0;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Reports</h1>
        <div>
            <a href="{{ route('dashboard') }}" class="btn">Dashboard</a>
        </div>
    </div>

    <div class="container">
        <div class="grid">
            <a href="{{ route('reports.daily-sales') }}" class="card">
                <h2>Daily Sales Report</h2>
                <p>View POS sales by date, daily totals, and sales trends</p>
            </a>

            <a href="{{ route('reports.occupancy') }}" class="card">
                <h2>Occupancy Report</h2>
                <p>Track room occupancy rates and availability</p>
            </a>

            <a href="{{ route('reports.stock') }}" class="card">
                <h2>Stock Reports</h2>
                <p>Low stock alerts, fast-moving items, and inventory analysis</p>
            </a>
        </div>
    </div>
</body>
</html>

