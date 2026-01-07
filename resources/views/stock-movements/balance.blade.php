<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Balance - Hotel Management</title>
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
        .badge-low {
            background: #fff3cd;
            color: #856404;
        }
        .badge-ok {
            background: #d4edda;
            color: #155724;
        }
        .badge-out {
            background: #f8d7da;
            color: #721c24;
        }
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
        <h1>Stock Balance</h1>
        <div>
            <a href="{{ route('dashboard') }}" class="btn" style="margin-right: 10px;">Dashboard</a>
            <a href="{{ route('stock-movements.index') }}" class="btn" style="margin-right: 10px;">Movements</a>
            <a href="{{ route('stock-movements.create') }}" class="btn" style="background: #667eea;">Add Movement</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Current Stock Levels</h2>
            
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
                    @forelse($products as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td><span class="category-badge">{{ ucfirst($product->category) }}</span></td>
                            <td><strong>{{ $product->current_stock }}</strong></td>
                            <td>{{ $product->min_stock ?? '-' }}</td>
                            <td>
                                @if($product->current_stock <= 0)
                                    <span class="badge badge-out">Out of Stock</span>
                                @elseif($product->is_low)
                                    <span class="badge badge-low">Low Stock</span>
                                @else
                                    <span class="badge badge-ok">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999;">No products with stock tracking enabled</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

