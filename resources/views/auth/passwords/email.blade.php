@extends('layouts.guest')

@section('content')
    <h1>Reset Password</h1>
    <p class="subtitle">Enter your email to receive a password reset link</p>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label for="email">{{ __('Email Address') }}</label>
            <input id="email" type="email" class="@error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit">
            {{ __('Send Password Reset Link') }}
        </button>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ route('login') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">Back to Login</a>
        </div>
    </form>
@endsection
