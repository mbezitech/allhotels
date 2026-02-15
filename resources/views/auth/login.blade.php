@extends('layouts.guest')

@section('content')
    <h1>Welcome Back</h1>
    <p class="subtitle">Sign in to your hotel management account</p>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autofocus
                placeholder="your@email.com"
            >
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                placeholder="••••••••"
            >
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
            <div style="text-align: right; margin-top: 5px;">
                <a href="{{ route('password.request') }}" style="color: #667eea; text-decoration: none; font-size: 13px;">Forgot Your Password?</a>
            </div>
        </div>

        <div class="form-group">
            <label for="hotel_id">
                Select Hotel 
                <span style="color: #999; font-weight: normal; font-size: 12px;">(Optional for Super Admin)</span>
            </label>
            <select id="hotel_id" name="hotel_id">
                <option value="">-- Choose a hotel (optional for super admin) --</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                        {{ $hotel->name }}
                    </option>
                @endforeach
            </select>
            @error('hotel_id')
                <span class="error">{{ $message }}</span>
            @enderror
            <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                Super admins can login without selecting a hotel
            </small>
        </div>

        <div class="remember-me">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember" style="margin: 0; font-weight: normal;">Remember me</label>
        </div>

        <button type="submit">Sign In</button>
    </form>
@endsection


