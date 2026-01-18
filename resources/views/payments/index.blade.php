@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payments')

@push('styles')
<style>
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
    .badge-booking { background: #d1ecf1; color: #0c5460; }
    .badge-pos { background: #d4edda; color: #155724; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Payments</h2>
    <div>
        @if(isset($deletedCount) && $deletedCount > 0 && !($showDeleted ?? false))
            <a href="{{ route('payments.index', ['show_deleted' => 1] + request()->except('show_deleted')) }}" 
               class="btn" 
               style="background: #ff9800; color: white; margin-right: 10px;">
                View Deleted ({{ $deletedCount }})
            </a>
        @endif
        @if($showDeleted ?? false)
            <a href="{{ route('payments.index', request()->except('show_deleted')) }}" 
               class="btn" 
               style="background: #95a5a6; color: white; margin-right: 10px;">
                View Active Payments
            </a>
        @endif
        @if(auth()->user()->hasPermission('payments.create') || auth()->user()->isSuperAdmin())
            <a href="{{ route('payments.create') }}" class="btn btn-primary">Record Payment</a>
        @endif
    </div>
</div>

@if($showDeleted ?? false)
    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <strong style="color: #856404;">⚠️ Viewing Deleted Payments</strong>
        <p style="color: #856404; margin: 5px 0 0 0; font-size: 14px;">These payments have been soft-deleted and can be restored or permanently deleted.</p>
    </div>
@endif

<!-- Filters Section -->
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Filters</h3>
    <form method="GET" action="{{ route('payments.index') }}" id="filterForm">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
            @if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Hotel:</label>
                <select name="hotel_id" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ request('hotel_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Payment Type:</label>
                <select name="payment_type" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All Types</option>
                    <option value="booking" {{ request('payment_type') == 'booking' ? 'selected' : '' }}>Booking Payments</option>
                    <option value="pos" {{ request('payment_type') == 'pos' ? 'selected' : '' }}>POS Sale Payments</option>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">Payment Method:</label>
                <select name="payment_method" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">From Date:</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px; color: #666;">To Date:</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Apply Filters</button>
            @if(request()->hasAny(['hotel_id', 'payment_type', 'payment_method', 'from_date', 'to_date', 'booking_id', 'pos_sale_id']))
                <a href="{{ route('payments.index') }}" class="btn" style="background: #95a5a6; color: white; padding: 10px 20px; text-decoration: none;">Clear All Filters</a>
            @endif
        </div>
    </form>
</div>

@if(request()->hasAny(['hotel_id', 'payment_type', 'payment_method', 'from_date', 'to_date', 'booking_id', 'pos_sale_id']))
    <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="color: #1976d2;">Filters Active:</strong>
                <span style="color: #666; font-size: 13px; margin-left: 10px;">
                    @if(request('payment_type'))
                        Type: {{ ucfirst(request('payment_type')) }},
                    @endif
                    @if(request('payment_method'))
                        Method: {{ ucfirst(request('payment_method')) }},
                    @endif
                    @if(request('from_date') || request('to_date'))
                        Date: {{ request('from_date') ? request('from_date') : 'Any' }} - {{ request('to_date') ? request('to_date') : 'Any' }}
                    @endif
                </span>
            </div>
            <div style="font-size: 12px; color: #666;">
                Showing {{ $payments->total() }} payment(s)
            </div>
        </div>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                <th>Hotel</th>
                @endif
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Reference #</th>
                <th>Received By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr style="{{ ($showDeleted ?? false) && $payment->trashed() ? 'opacity: 0.7; background-color: #f8f9fa;' : '' }}">
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <td>
                        <strong style="color: #667eea;">{{ $payment->hotel->name ?? 'Unknown Hotel' }}</strong>
                        @if($payment->hotel && $payment->hotel->address)
                            <div style="font-size: 11px; color: #999; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($payment->hotel->address, 30) }}</div>
                        @endif
                    </td>
                    @endif
                    <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                    <td>
                        @if($payment->booking_id)
                            <span class="badge badge-booking">Booking</span>
                        @else
                            <span class="badge badge-pos">POS Sale</span>
                        @endif
                    </td>
                    <td>
                        @if($payment->booking_id)
                            Booking #{{ $payment->booking_id }} - {{ $payment->booking->guest_name }}
                        @else
                            POS Sale #{{ $payment->pos_sale_id }}
                        @endif
                    </td>
                    <td><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td>{{ $payment->reference_number ?? '-' }}</td>
                    <td>
                        @if($payment->receivedBy)
                            {{ $payment->receivedBy->name }}
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <a href="{{ route('payments.show', $payment) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View</a>
                            @if($showDeleted ?? false)
                                {{-- Show restore and force delete for deleted payments --}}
                                @if(auth()->user()->hasPermission('payments.delete', session('hotel_id')) || auth()->user()->isSuperAdmin())
                                    <form action="{{ route('payments.restore', $payment->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Restore this payment?')">
                                        @csrf
                                        <button type="submit" class="btn" style="background: #28a745; color: white; padding: 6px 12px; font-size: 12px;">Restore</button>
                                    </form>
                                    <form action="{{ route('payments.forceDelete', $payment->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('⚠️ WARNING: This will permanently delete this payment. This action cannot be undone. Are you absolutely sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn" style="background: #e74c3c; color: white; padding: 6px 12px; font-size: 12px;">Permanently Delete</button>
                                    </form>
                                @endif
                            @else
                                {{-- Show regular delete for active payments --}}
                                @if(auth()->user()->hasPermission('payments.delete', session('hotel_id')) || auth()->user()->isSuperAdmin())
                                    <form action="{{ route('payments.destroy', $payment) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this payment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn" style="background: #e74c3c; color: white; padding: 6px 12px; font-size: 12px;">Delete</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($isSuperAdmin) && $isSuperAdmin) ? '9' : '8' }}" style="text-align: center; color: #999; padding: 40px;">No payments found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $payments->links() }}
    </div>
</div>
@endsection
