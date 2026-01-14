@extends('layouts.app')

@section('title', 'Create Extra Category')
@section('page-title', 'Create Extra Category')

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    input, textarea {
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
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .checkbox-group input {
        width: auto;
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
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if($errors->any())
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <strong>Please fix the following errors:</strong>
            <ul style="margin: 10px 0 0 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('extra-categories.store') }}" id="createCategoryForm">
        @csrf

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
            @error('description')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active" style="margin: 0; font-weight: normal;">Active</label>
            </div>
            @error('is_active')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Create Category</button>
            <a href="{{ route('extra-categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('createCategoryForm').addEventListener('submit', function(e) {
        // Ensure checkbox value is set
        const checkbox = document.getElementById('is_active');
        if (!checkbox.checked) {
            // Remove the checkbox from form if unchecked (so it doesn't send value)
            checkbox.removeAttribute('name');
        }
    });
</script>
@endpush
@endsection

