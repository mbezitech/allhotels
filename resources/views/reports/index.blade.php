@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .card h2 {
        margin-bottom: 10px;
        color: #333;
    }
    .card p {
        color: #666;
        margin: 0;
    }
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
</style>
@endpush

@section('content')
<div>
        <div class="grid">
            <a href="{{ route('reports.daily-sales') }}" class="card">
                <h2>Daily Sales Report</h2>
                <p>View POS sales by date, daily totals, and sales trends</p>
            </a>

            <a href="{{ route('reports.occupancy') }}" class="card">
                <h2>Occupancy Report</h2>
                <p>Track room occupancy rates and availability</p>
            </a>

            <a href="{{ route('reports.stock') }}" class="card">
                <h2>Stock Reports</h2>
                <p>Low stock alerts, fast-moving items, and inventory analysis</p>
            </a>
        </div>
</div>
@endsection

