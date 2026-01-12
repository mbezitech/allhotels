@extends('layouts.app')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
    <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Edit Expense</h2>
    
    @if($errors->any())
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div style="margin-bottom: 20px;">
            <label for="expense_date" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Expense Date *</label>
            <input type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="expense_category_id" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Category *</label>
            <select id="expense_category_id" name="expense_category_id" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Description *</label>
            <textarea id="description" name="description" rows="4" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; resize: vertical;">{{ old('description', $expense->description) }}</textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label for="amount" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Amount *</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount', $expense->amount) }}" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
            </div>
            
            <div>
                <label for="payment_method" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Payment Method *</label>
                <select id="payment_method" name="payment_method" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                    <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ old('payment_method', $expense->payment_method) == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="mobile" {{ old('payment_method', $expense->payment_method) == 'mobile' ? 'selected' : '' }}>Mobile</option>
                </select>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="attachment" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Receipt (Optional)</label>
            @if($expense->attachment)
                <div style="margin-bottom: 10px;">
                    <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" style="color: #667eea; text-decoration: none; display: inline-block; padding: 8px 12px; background: #f0f4ff; border-radius: 6px;">
                        📎 Current Receipt
                    </a>
                </div>
            @endif
            <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
            <small style="color: #666; display: block; margin-top: 5px;">Accepted formats: PDF, JPG, PNG (Max: 10MB). Leave empty to keep current receipt.</small>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" style="background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500;">Update Expense</button>
            <a href="{{ route('expenses.index') }}" style="background: #95a5a6; color: white; padding: 12px 24px; border: none; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; display: inline-block;">Cancel</a>
        </div>
    </form>
</div>
@endsection
