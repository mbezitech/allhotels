@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ route('settings.store') }}">
        @csrf

        @foreach($settings as $group => $groupSettings)
            <div style="margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; text-transform: capitalize;">{{ $group }} Settings</h3>
                
                @foreach($groupSettings as $setting)
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="{{ $setting->key }}" style="display: block; margin-bottom: 8px; font-weight: 500;">
                            {{ $setting->description ?? str_replace('_', ' ', ucfirst($setting->key)) }}
                        </label>
                        
                        @if($setting->type == 'integer' || $setting->type == 'number')
                            <input type="number" 
                                   id="{{ $setting->key }}" 
                                   name="{{ $setting->key }}" 
                                   value="{{ old($setting->key, $setting->value) }}" 
                                   class="form-control" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                        @elseif($setting->type == 'boolean')
                            <select name="{{ $setting->key }}" 
                                    id="{{ $setting->key }}" 
                                    class="form-control"
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                                <option value="1" {{ old($setting->key, $setting->value) == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old($setting->key, $setting->value) == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        @else
                            <input type="text" 
                                   id="{{ $setting->key }}" 
                                   name="{{ $setting->key }}" 
                                   value="{{ old($setting->key, $setting->value) }}" 
                                   class="form-control" 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                        @endif
                        
                        <small style="color: #666; display: block; margin-top: 5px;">Key: {{ $setting->key }}</small>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div style="margin-top: 30px;">
            <button type="submit" style="background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px;">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
