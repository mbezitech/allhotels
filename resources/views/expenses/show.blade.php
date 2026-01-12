@extends('layouts.app')

@section('title', 'Expense Details')
@section('page-title', 'Expense Details')

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: #333; font-size: 24px;">Expense Details</h2>
        <div style="display: flex; gap: 10px;">
            @if(auth()->user()->hasPermission('expenses.edit', session('hotel_id')) || auth()->user()->isSuperAdmin())
                <a href="{{ route('expenses.edit', $expense) }}" class="btn" style="background: #3498db; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Edit</a>
            @endif
            @if(auth()->user()->hasPermission('expenses.delete', session('hotel_id')) || auth()->user()->isSuperAdmin())
                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this expense?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn" style="background: #e74c3c; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer;">Delete</button>
                </form>
            @endif
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #666; font-size: 13px;">Date</label>
            <div style="font-size: 16px; color: #333;">{{ $expense->expense_date->format('M d, Y') }}</div>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #666; font-size: 13px;">Category</label>
            <div style="font-size: 16px; color: #333;">{{ $expense->category->name ?? 'N/A' }}</div>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #666; font-size: 13px;">Amount</label>
            <div style="font-size: 24px; color: #e74c3c; font-weight: 600;">${{ number_format($expense->amount, 2) }}</div>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #666; font-size: 13px;">Payment Method</label>
            <div>
                <span style="display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 14px; font-weight: 500; background: #e0e0e0; color: #333;">
                    {{ ucfirst($expense->payment_method) }}
                </span>
            </div>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #666; font-size: 13px;">Added By</label>
            <div style="font-size: 16px; color: #333;">{{ $expense->addedBy->name ?? 'N/A' }}</div>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #666; font-size: 13px;">Added On</label>
            <div style="font-size: 16px; color: #333;">{{ $expense->created_at->format('M d, Y H:i') }}</div>
        </div>
    </div>
    
    <div style="margin-bottom: 20px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #666; font-size: 13px;">Description</label>
        <div style="font-size: 16px; color: #333; padding: 15px; background: #f8f9fa; border-radius: 8px; white-space: pre-wrap;">{{ $expense->description }}</div>
    </div>
    
    @if($expense->attachment)
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #666; font-size: 13px;">Receipt</label>
            <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" style="display: inline-block; padding: 12px 20px; background: #667eea; color: white; border-radius: 8px; text-decoration: none;">
                📎 View Receipt
            </a>
        </div>
    @endif
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
        <a href="{{ route('expenses.index') }}" style="color: #667eea; text-decoration: none;">← Back to Expenses</a>
    </div>
</div>
@endsection
