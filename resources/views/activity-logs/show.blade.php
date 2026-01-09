@extends('layouts.app')

@section('title', 'Activity Log Details')
@section('page-title', 'Activity Log Details')

@push('styles')
<style>
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 500;
        color: #666;
    }
    .info-value {
        font-weight: 600;
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-created { background: #d4edda; color: #155724; }
    .badge-updated { background: #d1ecf1; color: #0c5460; }
    .badge-deleted { background: #f8d7da; color: #721c24; }
    .badge-checked_in { background: #cce5ff; color: #004085; }
    .badge-checked_out { background: #fff3cd; color: #856404; }
    .badge-system { background: #6c757d; color: white; }
    .system-badge {
        background: #6c757d;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: bold;
    }
    .values-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
    }
    .values-box h4 {
        margin: 0 0 10px 0;
        font-size: 14px;
        color: #333;
    }
    .values-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    .value-item {
        padding: 8px;
        background: white;
        border-radius: 4px;
        border-left: 3px solid #667eea;
    }
    .value-item strong {
        display: block;
        font-size: 11px;
        color: #666;
        margin-bottom: 5px;
    }
    .value-item span {
        font-size: 13px;
        color: #333;
    }
    @media (max-width: 768px) {
        .values-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Activity Log Details</h2>
    <a href="{{ route('activity-logs.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Logs</a>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div class="info-row">
        <span class="info-label">Date/Time:</span>
        <span class="info-value">{{ $activityLog->created_at->format('M d, Y H:i:s') }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Actor:</span>
        <span class="info-value">
            @if($activityLog->isSystemLog())
                <span class="system-badge">SYSTEM</span>
            @else
                {{ $activityLog->user->name ?? 'Unknown' }}
            @endif
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Action:</span>
        <span class="info-value">
            <span class="badge badge-{{ $activityLog->action }}">
                {{ ucfirst(str_replace('_', ' ', $activityLog->action)) }}
            </span>
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Hotel:</span>
        <span class="info-value">{{ $activityLog->hotel->name ?? 'Unknown' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Subject Type:</span>
        <span class="info-value">
            @if($activityLog->model_type)
                {{ class_basename($activityLog->model_type) }}
                @if($activityLog->model_id)
                    (ID: {{ $activityLog->model_id }})
                @endif
            @else
                <span style="color: #999;">-</span>
            @endif
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Description:</span>
        <span class="info-value">{{ $activityLog->description }}</span>
    </div>

    @if($activityLog->properties && count($activityLog->properties) > 0)
        <div class="info-row">
            <span class="info-label">Properties:</span>
            <span class="info-value">
                <div class="values-box">
                    @foreach($activityLog->properties as $key => $value)
                        <div class="value-item">
                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                            <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                        </div>
                    @endforeach
                </div>
            </span>
        </div>
    @endif

    @if($activityLog->old_values && count($activityLog->old_values) > 0)
        <div class="info-row">
            <span class="info-label">Old Values:</span>
            <span class="info-value">
                <div class="values-box">
                    @foreach($activityLog->old_values as $key => $value)
                        <div class="value-item">
                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                            <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                        </div>
                    @endforeach
                </div>
            </span>
        </div>
    @endif

    @if($activityLog->new_values && count($activityLog->new_values) > 0)
        <div class="info-row">
            <span class="info-label">New Values:</span>
            <span class="info-value">
                <div class="values-box">
                    @foreach($activityLog->new_values as $key => $value)
                        <div class="value-item">
                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                            <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                        </div>
                    @endforeach
                </div>
            </span>
        </div>
    @endif

    @if($activityLog->old_values && $activityLog->new_values)
        <div class="info-row">
            <span class="info-label">Changes:</span>
            <span class="info-value">
                <div class="values-box">
                    <div class="values-grid">
                        <div>
                            <h4 style="color: #e74c3c; margin-bottom: 10px;">Old Values</h4>
                            @foreach($activityLog->old_values as $key => $value)
                                <div class="value-item" style="border-left-color: #e74c3c; margin-bottom: 8px;">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                    <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <h4 style="color: #27ae60; margin-bottom: 10px;">New Values</h4>
                            @foreach($activityLog->new_values as $key => $value)
                                <div class="value-item" style="border-left-color: #27ae60; margin-bottom: 8px;">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                    <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </span>
        </div>
    @endif

    <div class="info-row">
        <span class="info-label">IP Address:</span>
        <span class="info-value">{{ $activityLog->ip_address ?? '-' }}</span>
    </div>

    @if($activityLog->user_agent)
        <div class="info-row">
            <span class="info-label">User Agent:</span>
            <span class="info-value" style="font-size: 12px; color: #666;">{{ $activityLog->user_agent }}</span>
        </div>
    @endif
</div>
@endsection
