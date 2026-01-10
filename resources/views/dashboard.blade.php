@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@if(!$hotel && auth()->user()->isSuperAdmin())
    <!-- Super Admin Dashboard -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 30px; margin-bottom: 30px; color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 style="color: white; margin: 0 0 5px 0; font-size: 28px;">Super Admin Dashboard</h2>
                <p style="color: rgba(255,255,255,0.9); margin: 0;">System-wide overview and statistics</p>
            </div>
            <a href="{{ route('hotels.create') }}" style="padding: 12px 24px; background: white; color: #667eea; border-radius: 8px; text-decoration: none; font-weight: 500; white-space: nowrap;">
                + Add New Hotel
            </a>
        </div>
    </div>

    <!-- Key Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="color: #666; font-size: 14px; font-weight: 500;">Total Hotels</h3>
                <div style="width: 40px; height: 40px; background: #e3f2fd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 20px;">🏨</span>
                </div>
            </div>
            <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $totalHotels ?? 0 }}</div>
            <div style="font-size: 12px; color: #999; margin-top: 5px;">Active properties</div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="color: #666; font-size: 14px; font-weight: 500;">Total Users</h3>
                <div style="width: 40px; height: 40px; background: #f3e5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 20px;">👥</span>
                </div>
            </div>
            <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $totalUsers ?? 0 }}</div>
            <div style="font-size: 12px; color: #999; margin-top: 5px;">{{ $totalRegularUsers ?? 0 }} regular, {{ $totalSuperAdmins ?? 0 }} admin</div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="color: #666; font-size: 14px; font-weight: 500;">Total Rooms</h3>
                <div style="width: 40px; height: 40px; background: #e8f5e9; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 20px;">🛏️</span>
                </div>
            </div>
            <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $totalRooms ?? 0 }}</div>
            <div style="font-size: 12px; color: #999; margin-top: 5px;">{{ $occupiedRooms ?? 0 }} occupied, {{ $availableRooms ?? 0 }} available</div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="color: #666; font-size: 14px; font-weight: 500;">Occupancy Rate</h3>
                <div style="width: 40px; height: 40px; background: #fff3cd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 20px;">📊</span>
                </div>
            </div>
            <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $occupancyRate ?? 0 }}%</div>
            <div style="font-size: 12px; color: #999; margin-top: 5px;">System-wide average</div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="color: #666; font-size: 14px; font-weight: 500;">Total Bookings</h3>
                <div style="width: 40px; height: 40px; background: #e1f5fe; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 20px;">📅</span>
                </div>
            </div>
            <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $allBookings ?? 0 }}</div>
            <div style="font-size: 12px; color: #999; margin-top: 5px;">{{ $pendingBookings ?? 0 }} pending</div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="color: #666; font-size: 14px; font-weight: 500;">Total Sales</h3>
                <div style="width: 40px; height: 40px; background: #d4edda; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 20px;">💰</span>
                </div>
            </div>
            <div style="font-size: 32px; font-weight: bold; color: #333;">${{ number_format($totalSales ?? 0, 2) }}</div>
            <div style="font-size: 12px; color: #999; margin-top: 5px;">{{ $totalSalesCount ?? 0 }} transactions</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Sales Chart -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Sales Trend (Last 7 Days)</h3>
            <canvas id="salesChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Bookings Chart -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Bookings Trend (Last 7 Days)</h3>
            <canvas id="bookingsChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Today's Activity & Top Hotels -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Today's Activity -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Today's Activity</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                    <div>
                        <div style="font-weight: 500; color: #333;">Check-ins</div>
                        <div style="font-size: 12px; color: #999;">Guests arriving</div>
                    </div>
                    <div style="font-size: 24px; font-weight: bold; color: #4caf50;">{{ $todayCheckIns ?? 0 }}</div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                    <div>
                        <div style="font-weight: 500; color: #333;">Check-outs</div>
                        <div style="font-size: 12px; color: #999;">Guests leaving</div>
                    </div>
                    <div style="font-size: 24px; font-weight: bold; color: #ff9800;">{{ $todayCheckOuts ?? 0 }}</div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                    <div>
                        <div style="font-weight: 500; color: #333;">Today's Sales</div>
                        <div style="font-size: 12px; color: #999;">Revenue</div>
                    </div>
                    <div style="font-size: 24px; font-weight: bold; color: #2196f3;">${{ number_format($todaySales ?? 0, 2) }}</div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                    <div>
                        <div style="font-weight: 500; color: #333;">Today's Payments</div>
                        <div style="font-size: 12px; color: #999;">Received</div>
                    </div>
                    <div style="font-size: 24px; font-weight: bold; color: #9c27b0;">${{ number_format($todayPayments ?? 0, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Top Hotels by Bookings -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Top Hotels by Bookings</h3>
            @if(isset($topHotels) && $topHotels->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($topHotels as $index => $h)
                        <div style="padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; color: #333;">#{{ $index + 1 }} {{ $h->name }}</div>
                                    <div style="font-size: 12px; color: #666;">{{ $h->address ?? 'No address' }}</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 20px; font-weight: bold; color: #667eea;">{{ $h->bookings_count }}</div>
                                    <div style="font-size: 11px; color: #999;">bookings</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #999; text-align: center; padding: 20px;">No data available</p>
            @endif
        </div>

        <!-- Hotels by Rooms -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Hotels by Room Count</h3>
            @if(isset($hotelsByRooms) && $hotelsByRooms->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($hotelsByRooms as $index => $h)
                        <div style="padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; color: #333;">#{{ $index + 1 }} {{ $h->name }}</div>
                                    <div style="font-size: 12px; color: #666;">{{ $h->address ?? 'No address' }}</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 20px; font-weight: bold; color: #4caf50;">{{ $h->rooms_count }}</div>
                                    <div style="font-size: 11px; color: #999;">rooms</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #999; text-align: center; padding: 20px;">No data available</p>
            @endif
        </div>
    </div>

    <!-- Recent Bookings & Hotel List -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Recent Bookings -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #333; font-size: 18px;">Recent Bookings</h3>
                <a href="{{ route('activity-logs.index') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">View All</a>
            </div>
            @if(isset($recentBookings) && $recentBookings->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($recentBookings as $booking)
                        <div style="padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid {{ $booking->status === 'cancelled' ? '#dc3545' : ($booking->status === 'pending' ? '#ffc107' : '#667eea') }};">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">
                                <div>
                                    <div style="font-weight: 500; color: #333;">{{ $booking->guest_name }}</div>
                                    <div style="font-size: 12px; color: #999;">{{ $booking->hotel->name ?? 'Unknown Hotel' }} • Room {{ $booking->room->room_number ?? 'N/A' }}</div>
                                    <div style="font-size: 11px; color: #999; margin-top: 2px;">
                                        {{ $booking->created_at->format('M d, Y h:i A') }}
                                    </div>
                                </div>
                                <span style="font-size: 12px; padding: 4px 8px; background: {{ $booking->status === 'cancelled' ? '#f8d7da' : ($booking->status === 'pending' ? '#fff3cd' : '#e3f2fd') }}; color: {{ $booking->status === 'cancelled' ? '#721c24' : ($booking->status === 'pending' ? '#856404' : '#1976d2') }}; border-radius: 4px;">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </div>
                            <div style="font-size: 12px; color: #666;">
                                {{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #999; text-align: center; padding: 20px;">No recent bookings</p>
            @endif
        </div>

        <!-- All Hotels List -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #333; font-size: 18px;">All Hotels</h3>
                <a href="{{ route('hotels.index') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">Manage</a>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px; max-height: 400px; overflow-y: auto;">
                @foreach(\App\Models\Hotel::orderBy('name')->get() as $h)
                    <a href="{{ route('login') }}?hotel_id={{ $h->id }}" style="display: block; padding: 15px; background: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: #333; transition: all 0.3s; hover:border-color: #667eea;">
                        <div style="font-weight: 600; color: #333; margin-bottom: 5px;">{{ $h->name }}</div>
                        @if($h->address)
                            <div style="font-size: 12px; color: #666;">{{ $h->address }}</div>
                        @endif
                        @if($h->owner)
                            <div style="font-size: 11px; color: #999; margin-top: 3px;">Owner: {{ $h->owner->name }}</div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: @json($salesChartLabels ?? []),
                    datasets: [{
                        label: 'Sales ($)',
                        data: @json($salesChartData ?? []),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Bookings Chart
        const bookingsCtx = document.getElementById('bookingsChart');
        if (bookingsCtx) {
            new Chart(bookingsCtx, {
                type: 'bar',
                data: {
                    labels: @json($bookingsChartLabels ?? []),
                    datasets: [{
                        label: 'Bookings',
                        data: @json($bookingsChartData ?? []),
                        backgroundColor: '#4caf50',
                        borderColor: '#4caf50',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    </script>
    @endpush
@endif

@if($hotel)
<!-- Quick Actions -->
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
    <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Quick Actions</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        @if($hotel && (auth()->user()->hasPermission('bookings.create') || auth()->user()->isSuperAdmin()))
            <a href="{{ route('bookings.create') }}" style="display: block; padding: 15px; background: #667eea; color: white; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 500;">
                New Booking
            </a>
        @endif
        @if($hotel && (auth()->user()->hasPermission('pos.sell') || auth()->user()->isSuperAdmin()))
            <a href="{{ route('pos-sales.create') }}" style="display: block; padding: 15px; background: #4caf50; color: white; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 500;">
                New POS Sale
            </a>
        @endif
        @if($hotel && (auth()->user()->hasPermission('payments.create') || auth()->user()->isSuperAdmin()))
            <a href="{{ route('payments.create') }}" style="display: block; padding: 15px; background: #ff9800; color: white; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 500;">
                Record Payment
            </a>
        @endif
        @if($hotel && (auth()->user()->hasPermission('reports.view') || auth()->user()->isSuperAdmin()))
            <a href="{{ route('reports.index') }}" style="display: block; padding: 15px; background: #2196f3; color: white; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 500;">
                View Reports
            </a>
        @endif
        @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('hotels.index') }}" style="display: block; padding: 15px; background: #9c27b0; color: white; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 500;">
                Manage Hotels
            </a>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <!-- Rooms Card -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #666; font-size: 14px; font-weight: 500;">Total Rooms</h3>
            <div style="width: 40px; height: 40px; background: #e3f2fd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 20px;">🏨</span>
            </div>
        </div>
        <div style="font-size: 32px; font-weight: bold; color: #333; margin-bottom: 10px;">{{ $totalRooms }}</div>
        <div style="font-size: 12px; color: #999;">
            <span style="color: #4caf50;">{{ $occupiedRooms }} occupied</span> • 
            <span style="color: #2196f3;">{{ $availableRooms }} available</span>
        </div>
    </div>

    <!-- Occupancy Card -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #666; font-size: 14px; font-weight: 500;">Occupancy Rate</h3>
            <div style="width: 40px; height: 40px; background: #fff3cd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 20px;">📊</span>
            </div>
        </div>
        <div style="font-size: 32px; font-weight: bold; color: #333; margin-bottom: 10px;">{{ $occupancyRate }}%</div>
        <div style="font-size: 12px; color: #999;">{{ $occupiedRooms }} of {{ $totalRooms }} rooms</div>
    </div>

    <!-- Today's Sales Card -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #666; font-size: 14px; font-weight: 500;">Today's Sales</h3>
            <div style="width: 40px; height: 40px; background: #d4edda; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 20px;">💰</span>
            </div>
        </div>
        <div style="font-size: 32px; font-weight: bold; color: #333; margin-bottom: 10px;">${{ number_format($todaySales, 2) }}</div>
        <div style="font-size: 12px; color: #999;">{{ $todaySalesCount }} transactions</div>
    </div>

    <!-- Today's Payments Card -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #666; font-size: 14px; font-weight: 500;">Today's Payments</h3>
            <div style="width: 40px; height: 40px; background: #d1ecf1; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 20px;">💳</span>
            </div>
        </div>
        <div style="font-size: 32px; font-weight: bold; color: #333; margin-bottom: 10px;">${{ number_format($todayPayments, 2) }}</div>
        <div style="font-size: 12px; color: #999;">Received today</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <!-- Today's Activity -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Today's Activity</h3>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                <div>
                    <div style="font-weight: 500; color: #333;">Check-ins</div>
                    <div style="font-size: 12px; color: #999;">Guests arriving today</div>
                </div>
                <div style="font-size: 24px; font-weight: bold; color: #4caf50;">{{ $todayCheckIns }}</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                <div>
                    <div style="font-weight: 500; color: #333;">Check-outs</div>
                    <div style="font-size: 12px; color: #999;">Guests leaving today</div>
                </div>
                <div style="font-size: 24px; font-weight: bold; color: #ff9800;">{{ $todayCheckOuts }}</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                <div>
                    <div style="font-weight: 500; color: #333;">Pending Bookings</div>
                    <div style="font-size: 12px; color: #999;">Require attention</div>
                </div>
                <div style="font-size: 24px; font-weight: bold; color: #f44336;">{{ $pendingBookings }}</div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: #333; font-size: 18px;">Bookings</h3>
            <a href="{{ route('bookings.index') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">View All</a>
        </div>
        
        <!-- Booking Filters -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="{{ route('dashboard', ['booking_filter' => 'all']) }}" 
               style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; {{ ($bookingFilter ?? 'all') === 'all' ? 'background: #667eea; color: white;' : 'background: #f8f9fa; color: #333;' }}">
                All ({{ $allBookings ?? 0 }})
            </a>
            <a href="{{ route('dashboard', ['booking_filter' => 'pending']) }}" 
               style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; {{ ($bookingFilter ?? 'all') === 'pending' ? 'background: #ffc107; color: #333;' : 'background: #f8f9fa; color: #333;' }}">
                Pending ({{ $pendingBookings ?? 0 }})
            </a>
            <a href="{{ route('dashboard', ['booking_filter' => 'cancelled']) }}" 
               style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; {{ ($bookingFilter ?? 'all') === 'cancelled' ? 'background: #dc3545; color: white;' : 'background: #f8f9fa; color: #333;' }}">
                Cancelled ({{ $cancelledBookings ?? 0 }})
            </a>
        </div>
        @if($recentBookings->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($recentBookings as $booking)
                    <div style="padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid {{ $booking->status === 'cancelled' ? '#dc3545' : ($booking->status === 'pending' ? '#ffc107' : '#667eea') }};">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">
                            <div>
                                <div style="font-weight: 500; color: #333;">{{ $booking->guest_name }}</div>
                                <div style="font-size: 12px; color: #999;">Room {{ $booking->room->room_number }}</div>
                                <div style="font-size: 11px; color: #999; margin-top: 2px;">
                                    Booked: {{ $booking->created_at->format('M d, Y h:i A') }}
                                </div>
                                @if($booking->status === 'cancelled' && $booking->cancellation_reason)
                                    <div style="font-size: 11px; color: #dc3545; margin-top: 4px;">
                                        <strong>Reason:</strong> {{ $booking->cancellation_reason }}
                                    </div>
                                @endif
                            </div>
                            <span style="font-size: 12px; padding: 4px 8px; background: {{ $booking->status === 'cancelled' ? '#f8d7da' : ($booking->status === 'pending' ? '#fff3cd' : '#e3f2fd') }}; color: {{ $booking->status === 'cancelled' ? '#721c24' : ($booking->status === 'pending' ? '#856404' : '#1976d2') }}; border-radius: 4px;">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </div>
                        <div style="font-size: 12px; color: #666;">
                            {{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: #999; text-align: center; padding: 20px;">No recent bookings</p>
        @endif
    </div>
</div>

<!-- Booking Calendar -->
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: #333; font-size: 18px;">Booking Calendar - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h3>
        <a href="{{ route('bookings.calendar') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">View Full Calendar →</a>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #e0e0e0; margin-bottom: 1px;">
        <div style="background: #667eea; color: white; padding: 10px; text-align: center; font-weight: 600; font-size: 12px;">Sun</div>
        <div style="background: #667eea; color: white; padding: 10px; text-align: center; font-weight: 600; font-size: 12px;">Mon</div>
        <div style="background: #667eea; color: white; padding: 10px; text-align: center; font-weight: 600; font-size: 12px;">Tue</div>
        <div style="background: #667eea; color: white; padding: 10px; text-align: center; font-weight: 600; font-size: 12px;">Wed</div>
        <div style="background: #667eea; color: white; padding: 10px; text-align: center; font-weight: 600; font-size: 12px;">Thu</div>
        <div style="background: #667eea; color: white; padding: 10px; text-align: center; font-weight: 600; font-size: 12px;">Fri</div>
        <div style="background: #667eea; color: white; padding: 10px; text-align: center; font-weight: 600; font-size: 12px;">Sat</div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #e0e0e0;">
        @foreach($calendar as $day)
            <div style="background: {{ $day['isToday'] ? '#e3f2fd' : ($day['isCurrentMonth'] ? 'white' : '#f8f9fa') }}; min-height: 80px; padding: 8px; position: relative; {{ !$day['isCurrentMonth'] ? 'color: #999;' : '' }}">
                <div style="font-size: 12px; margin-bottom: 4px; font-weight: {{ $day['isToday'] ? 'bold' : 'normal' }};">{{ $day['date']->day }}</div>
                @foreach($day['bookings']->take(2) as $booking)
                    <div style="font-size: 10px; padding: 2px 4px; margin: 2px 0; border-radius: 3px; background: #667eea; color: white; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $booking->guest_name }} - Room {{ $booking->room->room_number }}">
                        {{ $booking->guest_name }}
                    </div>
                @endforeach
                @if($day['bookings']->count() > 2)
                    <div style="font-size: 10px; color: #666; margin-top: 2px;">+{{ $day['bookings']->count() - 2 }} more</div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<!-- Upcoming Available Rooms -->
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: #333; font-size: 18px;">Upcoming Available Rooms</h3>
        <a href="{{ route('rooms.index') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">View All Rooms →</a>
    </div>
    
    @if(count($upcomingAvailable) > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            @foreach($upcomingAvailable as $item)
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid {{ $item['available_date']->isToday() ? '#4caf50' : '#2196f3' }};">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <div>
                            <div style="font-weight: 600; color: #333; font-size: 16px;">Room {{ $item['room']->room_number }}</div>
                            <div style="font-size: 12px; color: #666;">{{ $item['room']->roomType->name ?? 'N/A' }}</div>
                        </div>
                        <span style="font-size: 11px; padding: 4px 8px; background: {{ $item['available_date']->isToday() ? '#d4edda' : '#d1ecf1' }}; color: {{ $item['available_date']->isToday() ? '#155724' : '#0c5460' }}; border-radius: 4px;">
                            {{ $item['available_date']->isToday() ? 'Available Now' : 'Available ' . $item['available_date']->diffForHumans() }}
                        </span>
                    </div>
                    @if($item['current_booking'])
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
                            <span style="color: #999;">Current guest:</span> {{ $item['current_booking']->guest_name }}
                        </div>
                        <div style="font-size: 11px; color: #999; margin-top: 3px;">
                            Check-out: {{ $item['available_date']->format('M d, Y') }}
                        </div>
                    @else
                        <div style="font-size: 12px; color: #4caf50; margin-top: 5px;">
                            ✓ Currently available
                        </div>
                    @endif
                    <div style="margin-top: 10px;">
                        <a href="{{ route('bookings.create') }}?room_id={{ $item['room']->id }}" style="display: inline-block; padding: 6px 12px; background: #667eea; color: white; border-radius: 4px; text-decoration: none; font-size: 12px;">
                            Book Now
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p style="color: #999; text-align: center; padding: 20px;">No upcoming available rooms</p>
    @endif
</div>
@endif
@endsection
