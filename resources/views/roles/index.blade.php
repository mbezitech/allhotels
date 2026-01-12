<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles - Hotel Management</title>
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
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-edit {
            background: #3498db;
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
            background: #e0e0e0;
            margin: 2px;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Role Management</h1>
        <div>
            <a href="{{ route('dashboard') }}" class="btn" style="background: #95a5a6; color: white; margin-right: 10px;">Dashboard</a>
            <a href="{{ route('roles.create') }}" class="btn btn-primary">Create Role</a>
        </div>
    </div>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="card">
            <h2 style="margin-bottom: 20px;">All Roles</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td><strong>{{ $role->name }}</strong></td>
                            <td><code>{{ $role->slug }}</code></td>
                            <td>{{ $role->description ?? '-' }}</td>
                            <td>
                                @php
                                    $groupedPerms = $role->permissions->groupBy(function($permission) {
                                        $parts = explode('.', $permission->slug);
                                        return $parts[0];
                                    });
                                @endphp
                                @foreach($groupedPerms as $module => $modulePerms)
                                    <div style="margin-bottom: 8px;">
                                        <strong style="color: #667eea; font-size: 11px; text-transform: uppercase;">{{ ucfirst(str_replace('_', ' ', $module)) }}:</strong>
                                        @foreach($modulePerms as $permission)
                                            <span class="badge" style="font-size: 11px;">{{ $permission->slug }}</span>
                                        @endforeach
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-edit">Edit</a>
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999;">No roles found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

