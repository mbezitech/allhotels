@extends('layouts.app')

@section('title', 'Expense Reports')
@section('page-title', 'Expense Reports')

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
    .btn-success {
        background: #28a745;
        color: white;
    }
    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .summary-card h3 {
        margin: 0 0 15px 0;
        color: #333;
        font-size: 18px;
    }
    .summary-value {
        font-size: 32px;
        font-weight: 600;
        color: #e74c3c;
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
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Expense Reports</h2>
    <a href="{{ route('expenses.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Expenses</a>
</div>

<div class="summary-card">
    <form method="GET" action="{{ route('expense-reports.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end; margin-bottom: 20px;">
        @if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Hotel:</label>
                <select name="hotel_id" style="width: 100%; padding: 8px; border: 2px solid #667eea; border-radius: 6px;">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ (isset($selectedHotelId) && $selectedHotelId == $h->id) || request('hotel_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Start Date:</label>
            <input type="date" name="start_date" value="{{ $startDate }}" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">End Date:</label>
            <input type="date" name="end_date" value="{{ $endDate }}" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Category:</label>
            <select name="category_id" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Payment Method:</label>
            <select name="payment_method" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
                <option value="">All Methods</option>
                <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                <option value="mobile" {{ request('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Filter</button>
        </div>
        <div>
            <a href="{{ route('expense-reports.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="btn btn-success" style="width: 100%;">Export CSV</a>
        </div>
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="summary-card">
        <h3>Total Expenses</h3>
        <div class="summary-value">${{ number_format($totalAmount, 2) }}</div>
        <div style="color: #666; margin-top: 5px;">{{ $totalCount }} expense(s)</div>
    </div>
</div>

@if($totalsByCategory->count() > 0)
    <div class="summary-card">
        <h3>Expenses by Category</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Count</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($totalsByCategory as $item)
                    <tr>
                        <td>{{ $item['category']->name ?? 'N/A' }}</td>
                        <td>{{ $item['count'] }}</td>
                        <td style="font-weight: 600; color: #e74c3c;">${{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($totalsByPaymentMethod->count() > 0)
    <div class="summary-card">
        <h3>Expenses by Payment Method</h3>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Count</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($totalsByPaymentMethod as $item)
                    <tr>
                        <td>{{ $item['method'] }}</td>
                        <td>{{ $item['count'] }}</td>
                        <td style="font-weight: 600; color: #e74c3c;">${{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($expenses->count() > 0)
    <div class="summary-card">
        <h3>Expense Details</h3>
        <table>
            <thead>
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                        <th>Hotel</th>
                    @endif
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                    <tr>
                        @if(isset($isSuperAdmin) && $isSuperAdmin)
                            <td>{{ $expense->hotel->name ?? 'N/A' }}</td>
                        @endif
                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                        <td>{{ $expense->category->name ?? 'N/A' }}</td>
                        <td>{{ Str::limit($expense->description, 50) }}</td>
                        <td style="font-weight: 600; color: #e74c3c;">${{ number_format($expense->amount, 2) }}</td>
                        <td>{{ ucfirst($expense->payment_method) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="summary-card">
        <p style="color: #999; text-align: center; padding: 40px;">No expenses found for the selected criteria.</p>
    </div>
@endif
@endsection
