@extends('layouts.app')

@section('title', 'OTP Verification')
@section('page-title', 'OTP Verification')

@push('styles')
<style>
    .otp-container {
        max-width: 400px;
        margin: 50px auto;
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    .otp-input {
        width: 100%;
        padding: 15px;
        font-size: 24px;
        text-align: center;
        letter-spacing: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .otp-input:focus {
        outline: none;
        border-color: #667eea;
    }
    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
    }
    .btn-primary {
        background: #667eea;
        color: white;
    }
    .resend-link {
        color: #667eea;
        text-decoration: none;
        font-size: 14px;
        margin-top: 15px;
        display: inline-block;
    }
    .error {
        color: #e74c3c;
        font-size: 13px;
        margin-top: 10px;
    }
    .success {
        color: #27ae60;
        font-size: 13px;
        margin-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="otp-container">
    <h2 style="margin-bottom: 10px;">Verify OTP</h2>
    <p style="color: #666; margin-bottom: 30px;">Enter the 6-digit code sent to your email</p>

    @if(session('status'))
        <div class="success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf

        <input type="text" name="otp" class="otp-input" maxlength="6" 
               placeholder="000000" required autofocus
               oninput="this.value = this.value.replace(/[^0-9]/g, '')">

        @error('otp')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
            Verify & Login
        </button>
    </form>

    <a href="{{ route('otp.resend') }}" class="resend-link">Resend OTP</a>
    <br>
    <a href="{{ route('logout') }}" class="resend-link" style="margin-top: 10px;">Back to Login</a>
</div>
@endsection
