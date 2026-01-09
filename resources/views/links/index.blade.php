@extends('layouts.app')

@section('title', 'Link References')
@section('page-title', 'Link References')

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        margin-bottom: 24px;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }
    .card-subtitle {
        font-size: 13px;
        color: #777;
    }
    .link-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    .link-label {
        font-size: 14px;
        font-weight: 500;
        min-width: 140px;
        color: #555;
    }
    .link-input {
        flex: 1;
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 13px;
        font-family: monospace;
        background: #f8f9fa;
        color: #333;
    }
    .btn-copy {
        padding: 6px 10px;
        border-radius: 6px;
        border: none;
        background: #667eea;
        color: white;
        font-size: 12px;
        cursor: pointer;
        white-space: nowrap;
    }
    .btn-copy.copied {
        background: #28a745;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        text-align: left;
    }
    th {
        background: #f8f9fa;
        font-weight: 600;
        color: #555;
    }
    .tag {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        background: #e3f2fd;
        color: #1976d2;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Public Links for {{ $hotel->name }}</div>
            <div class="card-subtitle">Copy and share these links with guests</div>
        </div>
    </div>

    <div class="link-row">
        <span class="link-label">Hotel Search Page</span>
        @php
            $searchUrl = route('public.search', $hotel->slug);
        @endphp
        <input type="text" class="link-input" id="public-search-link" value="{{ $searchUrl }}" readonly onclick="this.select();">
        <button type="button" class="btn-copy" onclick="copyLink('public-search-link', this)">Copy</button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Room Booking Links</div>
            <div class="card-subtitle">Direct booking links for each room (public)</div>
        </div>
        <span class="tag">{{ $rooms->count() }} rooms</span>
    </div>

    @if($rooms->count())
        <table>
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Public Booking URL</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $room)
                    @php
                        $bookingUrl = route('public.booking.show', ['hotel_slug' => $hotel->slug, 'room_id' => $room->id]);
                    @endphp
                    <tr>
                        <td><strong>{{ $room->room_number }}</strong></td>
                        <td>{{ $room->roomType->name ?? 'N/A' }}</td>
                        <td>
                            <input type="text"
                                   class="link-input"
                                   id="room-link-{{ $room->id }}"
                                   value="{{ $bookingUrl }}"
                                   readonly
                                   onclick="this.select();">
                        </td>
                        <td>
                            <button type="button"
                                    class="btn-copy"
                                    onclick="copyLink('room-link-{{ $room->id }}', this)">
                                Copy
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #999; font-size: 14px;">No rooms found for this hotel.</p>
    @endif
</div>
@endsection

@push('scripts')
<script>
function copyLink(inputId, buttonEl) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999);

    const originalText = buttonEl.textContent;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(input.value)
            .then(function () {
                buttonEl.textContent = 'Copied!';
                buttonEl.classList.add('copied');
                setTimeout(function () {
                    buttonEl.textContent = originalText;
                    buttonEl.classList.remove('copied');
                }, 1500);
            })
            .catch(function () {
                document.execCommand('copy');
                buttonEl.textContent = 'Copied!';
                buttonEl.classList.add('copied');
                setTimeout(function () {
                    buttonEl.textContent = originalText;
                    buttonEl.classList.remove('copied');
                }, 1500);
            });
    } else {
        document.execCommand('copy');
        buttonEl.textContent = 'Copied!';
        buttonEl.classList.add('copied');
        setTimeout(function () {
            buttonEl.textContent = originalText;
            buttonEl.classList.remove('copied');
        }, 1500);
    }
}
</script>
@endpush


