@extends('layouts.app')

@section('title', 'Housekeeping Reports - All Hotels Summary')
@section('page-title', 'Housekeeping Reports - All Hotels Summary')

@push('styles')
<style>
    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .summary-card h3 {
        color: #333;
        font-size: 20px;
        margin-bottom: 15px;
        border-bottom: 2px solid #667eea;
        padding-bottom: 10px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .stat-item {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 12px;
        color: #666;
    }
    .hotel-link {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 20px;
        background: #667eea;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
    }
    .hotel-link:hover {
        background: #5568d3;
    }
</style>
@endpush

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Hotels Housekeeping Summary</h2>
    <p style="color: #666; margin-top: 5px;">Today's housekeeping overview across all hotels</p>
    <a href="{{ route('housekeeping-reports.index') }}" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: #95a5a6; color: white; border-radius: 6px; text-decoration: none; font-size: 14px;">
        ← Back to Reports
    </a>
</div>

@if(isset($summary) && count($summary) > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
        @foreach($summary as $hotelId => $data)
            <div class="summary-card">
                <h3>{{ $data['hotel']->name }}</h3>
                @if($data['hotel']->address)
                    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">{{ $data['hotel']->address }}</p>
                @endif
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $data['total_tasks'] }}</div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: #4caf50;">{{ $data['completed'] }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: #ff9800;">{{ $data['pending'] }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: #f44336;">{{ $data['issues'] }}</div>
                        <div class="stat-label">Issues</div>
                    </div>
                </div>
                
                <a href="{{ route('housekeeping-reports.index', ['hotel_id' => $hotelId]) }}" class="hotel-link">
                    View Detailed Reports →
                </a>
            </div>
        @endforeach
    </div>
@else
    <div class="summary-card">
        <p style="color: #999; text-align: center; padding: 40px;">No housekeeping data available for today</p>
    </div>
@endif
@endsection
