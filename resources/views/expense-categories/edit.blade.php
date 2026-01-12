@extends('layouts.app')

@section('title', 'Edit Expense Category')
@section('page-title', 'Edit Expense Category')

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto;">
    <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Edit Expense Category</h2>
    
    @if($errors->any())
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('expense-categories.update', $expenseCategory) }}">
        @csrf
        @method('PUT')
        
        <div style="margin-bottom: 20px;">
            <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Category Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $expenseCategory->name) }}" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Description</label>
            <textarea id="description" name="description" rows="4" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; resize: vertical;">{{ old('description', $expenseCategory->description) }}</textarea>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $expenseCategory->is_active) ? 'checked' : '' }} style="width: auto;">
                <span style="font-weight: 500; color: #333;">Active</span>
            </label>
            <small style="color: #666; display: block; margin-top: 5px;">Inactive categories won't appear in expense forms</small>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" style="background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500;">Update Category</button>
            <a href="{{ route('expense-categories.index') }}" style="background: #95a5a6; color: white; padding: 12px 24px; border: none; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; display: inline-block;">Cancel</a>
        </div>
    </form>
</div>
@endsection
