@extends('layouts.driver')

@section('page_title', 'My trips')

@section('content')
    @if(request('arrived'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i> Trip {{ request('arrived') }} delivered. Thank you.
        </div>
    @endif

    <div class="d-flex align-items-end justify-content-between mb-3">
        <div>
            <h4 class="mb-0">My trips</h4>
            <div class="text-muted small">{{ auth()->user()->name }}</div>
        </div>
        <span class="badge bg-light text-dark border">{{ $trips->count() }} active</span>
    </div>

    @forelse($trips as $trip)
        <a href="{{ route('driver.trips.show', $trip) }}" class="driver-card driver-trip-link mb-3">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="min-w-0">
                        <div class="fw-bold fs-5">{{ $trip->trip_number }}</div>
                        <div class="text-muted text-truncate">{{ $trip->route_label }}</div>
                    </div>
                    <span class="badge {{ $trip->status === 'in_transit' ? 'bg-success' : 'bg-secondary' }}">
                        {{ $trip->status_label }}
                    </span>
                </div>

                <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
                    <span><i class="bi bi-truck me-1"></i>{{ optional($trip->vehicle)->license_plate ?: 'No vehicle' }}</span>
                    <span>
                        <i class="bi bi-calendar-event me-1"></i>
                        {{ optional($trip->planned_start_at)->format('d M Y H:i') ?: 'No planned date' }}
                    </span>
                </div>

                <div class="mt-3 text-primary fw-semibold">
                    {{ $trip->status === 'planned' ? 'Start trip' : 'Add checkpoint' }}
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    @empty
        <div class="driver-card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-truck fs-1 d-block mb-2"></i>
                <div class="fw-semibold text-dark">No active trips assigned to you</div>
                <div class="small mt-1">New trips appear here as soon as dispatch assigns them.</div>
            </div>
        </div>
    @endforelse
@endsection
