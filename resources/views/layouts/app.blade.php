<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hotel Management') - {{ config('app.name', 'Hotel Management') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar-header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .hotel-badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
        }
        .nav-menu {
            list-style: none;
        }
        .nav-item {
            margin: 5px 0;
        }
        .nav-link {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .nav-link.active {
            background: #667eea;
            color: white;
        }
        .nav-section {
            padding: 10px 20px;
            font-size: 11px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            letter-spacing: 1px;
        }
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }
        .top-bar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-name {
            font-weight: 500;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
        .content-area {
            padding: 30px;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>Hotel Management</h1>
            @if(session('hotel_id'))
                @php
                    $currentHotel = \App\Models\Hotel::find(session('hotel_id'));
                @endphp
                @if($currentHotel)
                    <span class="hotel-badge">{{ $currentHotel->name }}</span>
                @endif
            @elseif(auth()->check() && auth()->user()->isSuperAdmin())
                <span class="hotel-badge" style="background: rgba(255, 193, 7, 0.3);">Super Admin Mode</span>
            @endif
        </div>
        <nav>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                </li>
                
                @if(auth()->user()->hasPermission('rooms.view') || auth()->user()->isSuperAdmin())
                    <li class="nav-section">Rooms & Bookings</li>
                    <li class="nav-item">
                        <a href="{{ route('links.index') }}" class="nav-link {{ request()->routeIs('links.*') ? 'active' : '' }}">
                            Link References
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                            Rooms
                        </a>
                    </li>
                    @if(auth()->user()->hasPermission('rooms.manage') || auth()->user()->isSuperAdmin())
                        <li class="nav-item">
                            <a href="{{ route('room-types.index') }}" class="nav-link {{ request()->routeIs('room-types.*') ? 'active' : '' }}">
                                Room Types
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('bookings.index') }}" class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                            Bookings
                        </a>
                    </li>
                @endif

                @if(auth()->user()->hasPermission('housekeeping.view') || auth()->user()->isSuperAdmin())
                    <li class="nav-section">Housekeeping</li>
                    <li class="nav-item">
                        <a href="{{ route('housekeeping-records.index') }}" class="nav-link {{ request()->routeIs('housekeeping-records.*') ? 'active' : '' }}">
                            Cleaning Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('hotel-areas.index') }}" class="nav-link {{ request()->routeIs('hotel-areas.*') ? 'active' : '' }}">
                            Hotel Areas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('housekeeping-reports.index') }}" class="nav-link {{ request()->routeIs('housekeeping-reports.*') ? 'active' : '' }}">
                            Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                            Tasks
                        </a>
                    </li>
                @endif

                @if(auth()->user()->hasPermission('pos.view') || auth()->user()->isSuperAdmin())
                    <li class="nav-section">Products & POS</li>
                    <li class="nav-item">
                        <a href="{{ route('extras.index') }}" class="nav-link {{ request()->routeIs('extras.*') ? 'active' : '' }}">
                            Products
                        </a>
                    </li>
                    @if(auth()->user()->hasPermission('stock.manage') || auth()->user()->isSuperAdmin())
                        <li class="nav-item">
                            <a href="{{ route('extra-categories.index') }}" class="nav-link {{ request()->routeIs('extra-categories.*') ? 'active' : '' }}">
                                Product Categories
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('pos-sales.index') }}" class="nav-link {{ request()->routeIs('pos-sales.*') ? 'active' : '' }}">
                            POS Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('stock-movements.index') }}" class="nav-link {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}">
                            Stock Movements
                        </a>
                    </li>
                @endif

                @if(auth()->user()->hasPermission('payments.view') || auth()->user()->isSuperAdmin())
                    <li class="nav-section">Financial</li>
                    <li class="nav-item">
                        <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                            Payments
                        </a>
                    </li>
                @endif

                @if(auth()->user()->hasPermission('reports.view') || auth()->user()->isSuperAdmin())
                    <li class="nav-section">Reports</li>
                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            Reports
                        </a>
                    </li>
                @endif

                @if(auth()->user()->isSuperAdmin())
                    <li class="nav-section">Administration</li>
                    <li class="nav-item">
                        <a href="{{ route('hotels.index') }}" class="nav-link {{ request()->routeIs('hotels.*') ? 'active' : '' }}">
                            Hotels
                        </a>
                    </li>
                @endif

                @if(auth()->user()->hasPermission('roles.manage') || auth()->user()->isSuperAdmin())
                    <li class="nav-section">User Management</li>
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            Roles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('user-roles.create') }}" class="nav-link {{ request()->routeIs('user-roles.*') ? 'active' : '' }}">
                            User Roles
                        </a>
                    </li>
                @endif

                @if(auth()->user()->hasPermission('activity_logs.view') || auth()->user()->isSuperAdmin())
                    <li class="nav-section">System</li>
                    <li class="nav-item">
                        <a href="{{ route('activity-logs.index') }}" class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                            Activity Logs
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h2>@yield('page-title', 'Dashboard')</h2>
            <div class="user-menu">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
        <div class="content-area">
            @if(session('success'))
                <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>

