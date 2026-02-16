@extends('layouts.app')

@section('title', 'Email Settings')
@section('page-title', 'Email Settings')

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    input, select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    input:focus, select:focus {
        outline: none;
        border-color: #667eea;
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
    .btn-success {
        background: #28a745;
        color: white;
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #667eea;
    }
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    .info-box {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .warning-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
@if(isset($isSuperAdmin) && $isSuperAdmin && isset($allHotels) && $allHotels->count() > 0)
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>Current Hotel:</strong> {{ $hotel->name }}
            </div>
            <select onchange="if(this.value) window.location.href='{{ route('email-settings.index') }}?hotel_id='+this.value" 
                    style="padding: 8px 16px; border: 2px solid #667eea; border-radius: 6px; background: white; cursor: pointer;">
                <option value="">Switch Hotel...</option>
                @foreach($allHotels as $h)
                    <option value="{{ $h->id }}" {{ $h->id == $hotel->id ? 'selected' : '' }}>{{ $h->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Email Settings - {{ $hotel->name }}</h2>
    
    @if(!$emailSettings->isConfigured() && !$emailSettings->enabled)
        <div class="info-box">
            <strong>ℹ️ Email Not Configured</strong>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Enable email and configure SMTP settings to send emails for this hotel.</p>
        </div>
    @elseif($emailSettings->enabled && !$emailSettings->isConfigured())
        <div class="warning-box">
            <strong>⚠️ Incomplete Configuration</strong>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Email is enabled but some required fields are missing. Please complete the configuration.</p>
        </div>
    @elseif($emailSettings->isConfigured())
        <div style="background: #d4edda; border-left: 4px solid #28a745; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <strong style="color: #155724;">✓ Email Configured</strong>
            <p style="margin: 5px 0 0 0; font-size: 14px; color: #155724;">Email settings are configured and enabled for this hotel.</p>
        </div>
    @endif
    
    <form method="POST" action="{{ route('email-settings.store') }}">
        @csrf
        @if(isset($isSuperAdmin) && $isSuperAdmin)
            <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
        @endif

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px;">
                <span>Enable Email</span>
                <label class="toggle-switch">
                    <input type="checkbox" name="enabled" value="1" {{ old('enabled', $emailSettings->enabled) ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </label>
            <small style="color: #666; display: block; margin-top: 5px;">Enable or disable email sending for this hotel</small>
        </div>

        <div style="border-top: 2px solid #e0e0e0; padding-top: 20px; margin-top: 20px;">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 15px;">SMTP Configuration</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="smtp_host">SMTP Host *</label>
                    <input type="text" id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $emailSettings->smtp_host) }}" placeholder="smtp.gmail.com">
                    @error('smtp_host')
                        <span style="color: #e74c3c; font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="smtp_port">SMTP Port *</label>
                    <input type="number" id="smtp_port" name="smtp_port" value="{{ old('smtp_port', $emailSettings->smtp_port) }}" placeholder="587" min="1" max="65535">
                    @error('smtp_port')
                        <span style="color: #e74c3c; font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="encryption">Encryption Type</label>
                <select id="encryption" name="encryption">
                    <option value="tls" {{ old('encryption', $emailSettings->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ old('encryption', $emailSettings->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ old('encryption', $emailSettings->encryption) === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="smtp_username">SMTP Username *</label>
                    <input type="text" id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $emailSettings->smtp_username) }}" placeholder="your-email@gmail.com">
                    @error('smtp_username')
                        <span style="color: #e74c3c; font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="smtp_password">SMTP Password *</label>
                    <input type="password" id="smtp_password" name="smtp_password" value="" placeholder="Leave blank to keep current password">
                    <small style="color: #666; display: block; margin-top: 5px;">Leave blank if you don't want to change the password</small>
                    @error('smtp_password')
                        <span style="color: #e74c3c; font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div style="border-top: 2px solid #e0e0e0; padding-top: 20px; margin-top: 20px;">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 15px;">Email Details</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="from_email">From Email *</label>
                    <input type="email" id="from_email" name="from_email" value="{{ old('from_email', $emailSettings->from_email) }}" placeholder="noreply@hotel.com">
                    @error('from_email')
                        <span style="color: #e74c3c; font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="from_name">From Name</label>
                    <input type="text" id="from_name" name="from_name" value="{{ old('from_name', $emailSettings->from_name) }}" placeholder="{{ $hotel->name }}">
                    <small style="color: #666; display: block; margin-top: 5px;">Leave blank to use hotel name</small>
                </div>
            </div>

            <div class="form-group">
                <label for="notification_email">System Admin Notification Email</label>
                <input type="email" id="notification_email" name="notification_email" value="{{ old('notification_email', $emailSettings->notification_email) }}" placeholder="notifications@hotel.com">
                <small style="color: #666; display: block; margin-top: 5px;">Primary email address to receive general system notifications</small>
            </div>

            <div style="margin-top: 30px; border-top: 2px solid #f0f0f0; padding-top: 20px;">
                <h3 style="color: #333; font-size: 18px; margin-bottom: 20px;">Hotel Notification Settings</h3>
                <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Configure which notifications staff should receive and where. Separate multiple emails with commas.</p>

                <!-- Booking Notifications -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e9ecef;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h4 style="margin: 0; color: #2c3e50; display: flex; align-items: center; gap: 8px;">
                                <span>📧 Booking Notifications</span>
                            </h4>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #6c757d;">Received when a new booking is created.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="hidden" name="notify_booking" value="0">
                            <input type="checkbox" name="notify_booking" value="1" {{ old('notify_booking', $emailSettings->notify_booking ?? true) ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="booking_notification_email" style="font-size: 13px;">Recipient Emails</label>
                        <input type="text" id="booking_notification_email" name="booking_notification_email" value="{{ old('booking_notification_email', $emailSettings->booking_notification_email) }}" placeholder="staff1@hotel.com, manager@hotel.com">
                        <small style="color: #6c757d; font-size: 11px;">Leave blank to use System Admin Notification email.</small>
                    </div>
                </div>

                <!-- Cancellation Notifications -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e9ecef;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h4 style="margin: 0; color: #2c3e50; display: flex; align-items: center; gap: 8px;">
                                <span>📧 Cancellation Notifications</span>
                            </h4>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #6c757d;">Received when a booking is cancelled.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="hidden" name="notify_cancellation" value="0">
                            <input type="checkbox" name="notify_cancellation" value="1" {{ old('notify_cancellation', $emailSettings->notify_cancellation ?? true) ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="cancellation_notification_email" style="font-size: 13px;">Recipient Emails</label>
                        <input type="text" id="cancellation_notification_email" name="cancellation_notification_email" value="{{ old('cancellation_notification_email', $emailSettings->cancellation_notification_email) }}" placeholder="staff1@hotel.com, manager@hotel.com">
                        <small style="color: #6c757d; font-size: 11px;">Leave blank to use System Admin Notification email.</small>
                    </div>
                </div>

                <!-- Payment Notifications -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h4 style="margin: 0; color: #2c3e50; display: flex; align-items: center; gap: 8px;">
                                <span>📧 Payment Notifications</span>
                            </h4>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #6c757d;">Received when a payment is recorded.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="hidden" name="notify_payment" value="0">
                            <input type="checkbox" name="notify_payment" value="1" {{ old('notify_payment', $emailSettings->notify_payment ?? true) ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="payment_notification_email" style="font-size: 13px;">Recipient Emails</label>
                        <input type="text" id="payment_notification_email" name="payment_notification_email" value="{{ old('payment_notification_email', $emailSettings->payment_notification_email) }}" placeholder="accountant@hotel.com">
                        <small style="color: #6c757d; font-size: 11px;">Leave blank to use System Admin Notification email.</small>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Save Settings</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@if($emailSettings->isConfigured())
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Send Test Email</h3>
    
    <form method="POST" action="{{ route('email-settings.test-email') }}">
        @csrf
        @if(isset($isSuperAdmin) && $isSuperAdmin)
            <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
        @endif
        
        <div class="form-group">
            <label for="test_email">Test Email Address *</label>
            <div style="display: flex; gap: 10px;">
                <input type="email" id="test_email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" required style="flex: 1;">
                <button type="submit" class="btn btn-success">Send Test Email</button>
            </div>
            <small style="color: #666; display: block; margin-top: 5px;">Enter an email address to send a test email</small>
        </div>
    </form>
</div>
@endif
@endsection
