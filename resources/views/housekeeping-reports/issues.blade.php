@extends('layouts.app')

@section('title', 'Issues & Damages Report')
@section('page-title', 'Issues & Damages Report')

@push('styles')
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .issues-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
        margin-top: 10px;
    }
    .filter-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Issues & Damages Report</h2>
    <a href="{{ route('housekeeping-reports.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Reports</a>
</div>

@if($isSuperAdmin)
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.issues') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">Hotel</label>
                <select name="hotel_id" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}" {{ $selectedHotelId == $hotel->id ? 'selected' : '' }}>
                            {{ $hotel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">Status</label>
                <select name="resolution_status" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    <option value="all" {{ $resolutionFilter == 'all' ? 'selected' : '' }}>All Issues</option>
                    <option value="unresolved" {{ $resolutionFilter == 'unresolved' ? 'selected' : '' }}>Unresolved</option>
                    <option value="resolved" {{ $resolutionFilter == 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>
            <button type="submit" style="padding: 8px 16px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer;">Apply</button>
        </form>
    </div>
@else
    <div class="filter-box">
        <form method="GET" action="{{ route('housekeeping-reports.issues') }}" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div style="display: inline-block; margin-right: 15px;">
                <label style="display: block; font-size: 12px; color: #666; margin-bottom: 5px;">Status</label>
                <select name="resolution_status" style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    <option value="all" {{ $resolutionFilter == 'all' ? 'selected' : '' }}>All Issues</option>
                    <option value="unresolved" {{ $resolutionFilter == 'unresolved' ? 'selected' : '' }}>Unresolved</option>
                    <option value="resolved" {{ $resolutionFilter == 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>
            <button type="submit" style="padding: 8px 16px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer;">Apply</button>
        </form>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Issues & Damages ({{ $issues->count() }} found)</h3>
    
    @forelse($issues as $issue)
        <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <strong style="color: #333;">
                            @if($issue->room)
                                Room {{ $issue->room->room_number }}
                            @elseif($issue->area)
                                {{ $issue->area->name }}
                            @endif
                        </strong>
                        @if($issue->issue_resolved)
                            <span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                ✓ Resolved
                            </span>
                        @else
                            <span style="background: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                ⚠️ Unresolved
                            </span>
                        @endif
                    </div>
                    <span style="color: #666; margin-left: 10px; display: block; margin-top: 5px;">
                        Reported by {{ $issue->assignedTo->name ?? 'Unknown' }} on {{ $issue->created_at->format('M d, Y H:i') }}
                        @if($issue->issue_resolved && $issue->issueResolvedBy)
                            • Resolved by {{ $issue->issueResolvedBy->name }} on {{ $issue->issue_resolved_at->format('M d, Y H:i') }}
                        @endif
                    </span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('housekeeping-records.show', $issue) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View Details</a>
                    @if(!$issue->issue_resolved && (auth()->user()->hasPermission('housekeeping_records.resolve', session('hotel_id')) || auth()->user()->isSuperAdmin()))
                        <button onclick="showResolveModal({{ $issue->id }})" class="btn" style="background: #28a745; color: white; padding: 6px 12px; font-size: 12px;">Resolve Issue</button>
                    @endif
                </div>
            </div>
            <div class="issues-box" style="{{ $issue->issue_resolved ? 'background: #d4edda; border-left-color: #28a745;' : '' }}">
                <strong>⚠️ Issues Found:</strong>
                <p style="margin-top: 10px; margin-bottom: 0;">{{ $issue->issues_found }}</p>
            </div>
            @if($issue->issue_resolved && $issue->issue_resolution_notes)
                <div style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; border-radius: 8px; margin-top: 10px;">
                    <strong>✓ Resolution Notes:</strong>
                    <p style="margin-top: 10px; margin-bottom: 0;">{{ $issue->issue_resolution_notes }}</p>
                </div>
            @endif
        </div>
    @empty
        <div style="text-align: center; color: #999; padding: 40px;">
            No issues or damages reported for this period
        </div>
    @endforelse
</div>

<!-- Resolve Issue Modal -->
<div id="resolveModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Resolve Issue</h3>
        <form id="resolveForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Resolution Notes *</label>
                <textarea 
                    name="issue_resolution_notes" 
                    rows="5" 
                    required 
                    minlength="10"
                    placeholder="Describe how the issue was resolved, what actions were taken, etc. (minimum 10 characters)"
                    style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; font-family: inherit;"
                ></textarea>
                <small style="color: #666; font-size: 12px;">Please provide detailed notes about how the issue was resolved.</small>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeResolveModal()" style="padding: 10px 20px; background: #95a5a6; color: white; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Resolve Issue</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showResolveModal(recordId) {
        const modal = document.getElementById('resolveModal');
        const form = document.getElementById('resolveForm');
        form.action = `/housekeeping-records/${recordId}/resolve`;
        modal.style.display = 'flex';
    }

    function closeResolveModal() {
        document.getElementById('resolveModal').style.display = 'none';
        document.getElementById('resolveForm').reset();
    }

    // Close modal when clicking outside
    document.getElementById('resolveModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeResolveModal();
        }
    });
</script>
@endsection

