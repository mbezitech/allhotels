<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Reports - Hotel Management</title>
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
        .badge-critical { background: #f8d7da; color: #721c24; }
        .badge-low { background: #fff3cd; color: #856404; }
        .badge-ok { background: #d4edda; color: #155724; }
        .category-badge {
            background: #e0e0e0;
            color: #333;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Stock Reports</h1>
        <div>
            <a href="{{ route('reports.index') }}" class="btn">Back to Reports</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Low Stock Alerts</h2>
            @if($lowStockItems->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Min Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                            <tr>
                                <td><strong>{{ $item->name }}</strong></td>
                                <td><span class="category-badge">{{ ucfirst($item->category) }}</span></td>
                                <td><strong>{{ $item->current_stock }}</strong></td>
                                <td>{{ $item->min_stock ?? '-' }}</td>
                                <td>
                                    @if($item->current_stock <= 0)
                                        <span class="badge badge-critical">Out of Stock</span>
                                    @else
                                        <span class="badge badge-low">Low Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #999; text-align: center;">No low stock items</p>
            @endif
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Fast-Moving Items (Last 30 Days)</h2>
            @if($fastMoving->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Quantity Sold</th>
                            <th>Current Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fastMoving as $item)
                            <tr>
                                <td><strong>{{ $item->name }}</strong></td>
                                <td><span class="category-badge">{{ ucfirst($item->category) }}</span></td>
                                <td><strong>{{ $item->total_out }}</strong></td>
                                <td>
                                    {{ $item->current_stock }}
                                    @if($item->isLowStock())
                                        <span class="badge badge-low" style="margin-left: 5px;">Low</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #999; text-align: center;">No fast-moving items in the last 30 days</p>
            @endif
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Slow-Moving Items (No Sales in Last 90 Days)</h2>
            @if($slowMoving->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slowMoving as $item)
                            <tr>
                                <td><strong>{{ $item->name }}</strong></td>
                                <td><span class="category-badge">{{ ucfirst($item->category) }}</span></td>
                                <td><strong>{{ $item->current_stock }}</strong></td>
                                <td>
                                    <span class="badge badge-ok">In Stock</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #999; text-align: center;">No slow-moving items</p>
            @endif
        </div>
    </div>
</body>
</html>

