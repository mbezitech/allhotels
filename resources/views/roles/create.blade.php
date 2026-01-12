<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Role - Hotel Management</title>
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
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
        }
        .checkbox-item input {
            width: auto;
            margin-right: 8px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        .error {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Create New Role</h1>
    </div>

    <div class="container">
        <div class="card">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Role Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="slug">Slug *</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required>
                    <small style="color: #666;">e.g., manager, receptionist, staff</small>
                    @error('slug')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Permissions</label>
                    @php
                        $groupedPermissions = $permissions->groupBy(function($permission) {
                            $parts = explode('.', $permission->slug);
                            $module = $parts[0];
                            // Map module names to display names
                            $moduleNames = [
                                'dashboard' => 'Dashboard',
                                'rooms' => 'Rooms',
                                'room_types' => 'Room Types',
                                'bookings' => 'Bookings',
                                'pos' => 'POS Sales',
                                'stock' => 'Stock Management',
                                'extras' => 'Products/Extras',
                                'extra_categories' => 'Product Categories',
                                'payments' => 'Payments',
                                'housekeeping' => 'Housekeeping',
                                'housekeeping_records' => 'Housekeeping Records',
                                'housekeeping_reports' => 'Housekeeping Reports',
                                'hotel_areas' => 'Hotel Areas',
                                'tasks' => 'Tasks',
                                'reports' => 'Reports',
                                'users' => 'Users',
                                'roles' => 'Roles',
                                'activity_logs' => 'Activity Logs',
                            ];
                            return $moduleNames[$module] ?? ucfirst(str_replace('_', ' ', $module));
                        });
                    @endphp
                    
                    @foreach($groupedPermissions as $moduleName => $modulePermissions)
                        <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
                            <h3 style="margin: 0 0 15px 0; color: #333; font-size: 16px; font-weight: 600;">{{ $moduleName }}</h3>
                            <div class="checkbox-group">
                                @foreach($modulePermissions as $permission)
                                    <div class="checkbox-item">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}"
                                               id="perm_{{ $permission->id }}"
                                               {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                        <label for="perm_{{ $permission->id }}" style="margin: 0; font-weight: normal;">
                                            <strong>{{ $permission->name }}</strong><br>
                                            <small style="color: #666;">{{ $permission->slug }}</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Create Role</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

