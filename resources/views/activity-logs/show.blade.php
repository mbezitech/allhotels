<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log Details - Hotel Management</title>
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
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: 500;
            color: #666;
        }
        .info-value {
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
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Activity Log Details</h1>
        <div>
            <a href="{{ route('activity-logs.index') }}" class="btn">Back to Logs</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Activity Information</h2>
            
            <div class="info-row">
                <span class="info-label">Date/Time:</span>
                <span class="info-value">{{ $activityLog->created_at->format('M d, Y H:i:s') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">User:</span>
                <span class="info-value">{{ $activityLog->user->name }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Action:</span>
                <span class="info-value">
                    <span class="badge badge-{{ $activityLog->action }}">
                        {{ ucfirst($activityLog->action) }}
                    </span>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Model:</span>
                <span class="info-value">
                    @if($activityLog->model_type)
                        {{ class_basename($activityLog->model_type) }}
                        @if($activityLog->model_id)
                            #{{ $activityLog->model_id }}
                        @endif
                    @else
                        -
                    @endif
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Description:</span>
                <span class="info-value">{{ $activityLog->description }}</span>
            </div>

            @if($activityLog->ip_address)
                <div class="info-row">
                    <span class="info-label">IP Address:</span>
                    <span class="info-value">{{ $activityLog->ip_address }}</span>
                </div>
            @endif

            @if($activityLog->user_agent)
                <div class="info-row">
                    <span class="info-label">User Agent:</span>
                    <span class="info-value" style="font-size: 12px;">{{ $activityLog->user_agent }}</span>
                </div>
            @endif

            @if($activityLog->properties)
                <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                    <span class="info-label" style="margin-bottom: 10px;">Properties:</span>
                    <pre>{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>
    </div>
</body>
</html>

