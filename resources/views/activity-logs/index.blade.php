<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Hotel Management</title>
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
        .badge-created { background: #d4edda; color: #155724; }
        .badge-updated { background: #d1ecf1; color: #0c5460; }
        .badge-deleted { background: #f8d7da; color: #721c24; }
        .model-type {
            font-size: 11px;
            color: #666;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Activity Logs</h1>
        <div>
            <a href="{{ route('dashboard') }}" class="btn">Dashboard</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Activity History</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td>{{ $log->user->name }}</td>
                            <td>
                                <span class="badge badge-{{ $log->action }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td>
                                @if($log->model_type)
                                    <span class="model-type">{{ class_basename($log->model_type) }}</span>
                                    @if($log->model_id)
                                        #{{ $log->model_id }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $log->description }}</td>
                            <td>
                                <a href="{{ route('activity-logs.show', $log) }}" class="btn" style="background: #3498db; color: white;">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999;">No activity logs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</body>
</html>

