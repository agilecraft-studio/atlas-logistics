@extends('layouts.attex')

@section('page_title', 'Driver · ' . $driver->name)
@section('fleet_subtitle', 'Licences, performance, incidents and driver finances')

@section('content')
@php
    $hub = app(\App\Services\FleetHubService::class);
    $money = fn ($amount, $code = null) => $amount === null || $amount === ''
        ? '—'
        : \App\Support\Money::format(\App\Support\Money::toMinor($amount), $code ?: \App\Services\CurrencyService::base());
    $tabLink = fn ($key) => route('drivers.show', ['id' => $driver->id, 'tab' => $key]);
    $printLink = fn ($type, $id) => route('fleet.records.print', ['type' => $type, 'id' => $id]);
    $pdfLink = fn ($type, $id) => route('fleet.records.pdf', ['type' => $type, 'id' => $id]);
    $initial = strtoupper(substr(trim($driver->name), 0, 1));
    $photo = $driver->getMeta('driver_image');
    $photoUrl = $photo ? (\Illuminate\Support\Str::startsWith($photo, 'http') ? $photo : asset('uploads/' . $photo)) : null;
    $meta = fn ($key) => $driver->getMeta($key);
    $today = now()->toDateString();
    $stars = fn ($rating) => str_repeat('★', (int) $rating) . str_repeat('☆', 5 - (int) $rating);
@endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
<x-fleet.alert type="danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-fleet.alert>
@endif

<x-fleet.panel>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $driver->name }}" class="rounded-circle" style="width:58px;height:58px;object-fit:cover">
            @else
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                     style="width:58px;height:58px;background:var(--atlas-navy);font-size:22px">{{ $initial }}</div>
            @endif
            <div>
                <h4 class="mb-1">
                    {{ $driver->name }}
                    <x-fleet.status-pill class="ms-2" :status="str_replace('_', ' ', $driverStatus)">{{ ucfirst(str_replace('_', ' ', $driverStatus)) }}</x-fleet.status-pill>
                </h4>
                <div class="text-muted small">
                    {{ $driver->email ?: 'No email' }}
                    @if($meta('phone')) · {{ trim($meta('phone_code') . ' ' . $meta('phone')) }} @endif
                    @if($profile?->employee_number) · Employee {{ $profile->employee_number }} @endif
                    @if($profile?->home_depot) · {{ $profile->home_depot }} @endif
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('Drivers edit')
            <a href="{{ route('drivers.edit', $driver->id) }}" class="fleet-btn"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            <a href="{{ $printLink('driver', $driver->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer me-1"></i>Print</a>
            <a href="{{ $pdfLink('driver', $driver->id) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="{{ route('drivers.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Trips" :value="$kpis['trips']" icon="bi-signpost-2" color="bg-blue" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Distance" :value="number_format($kpis['distance'], 0) . ' km'" icon="bi-speedometer2" color="bg-teal" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Average rating" :value="$averageRating !== null ? $averageRating . ' / 5' : '—'" icon="bi-star" color="bg-amber" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Open incidents" :value="$kpis['open_incidents']" icon="bi-exclamation-triangle" color="bg-red" /></div>
    </div>
</x-fleet.panel>

<ul class="nav nav-tabs nav-bordered mb-3 mt-3" role="tablist">
    <li class="nav-item"><a class="nav-link {{ $tab === 'overview' ? 'active' : '' }}" href="{{ $tabLink('overview') }}"><i class="bi bi-person-vcard me-1"></i>Overview</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'compliance' ? 'active' : '' }}" href="{{ $tabLink('compliance') }}"><i class="bi bi-shield-check me-1"></i>Licences &amp; certification <span class="badge bg-light text-dark border ms-1">{{ $docCounts['total'] }}</span></a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'performance' ? 'active' : '' }}" href="{{ $tabLink('performance') }}"><i class="bi bi-graph-up-arrow me-1"></i>Performance &amp; trips</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'incidents' ? 'active' : '' }}" href="{{ $tabLink('incidents') }}"><i class="bi bi-exclamation-triangle me-1"></i>Incidents &amp; fines <span class="badge bg-light text-dark border ms-1">{{ $kpis['open_incidents'] }}</span></a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'financials' ? 'active' : '' }}" href="{{ $tabLink('financials') }}"><i class="bi bi-wallet2 me-1"></i>Financials</a></li>
</ul>

@if($tab === 'overview')
<div class="row g-3">
    <div class="col-lg-7">
        <x-fleet.panel title="Driver details" icon="bi-person-lines-fill">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Full name</dt><dd class="col-7">{{ $driver->name }}</dd>
                <dt class="col-5 text-muted">Email</dt><dd class="col-7">{{ $driver->email ?: '—' }}</dd>
                <dt class="col-5 text-muted">Phone</dt><dd class="col-7">{{ trim(($meta('phone_code') ?: '') . ' ' . ($meta('phone') ?: '')) ?: '—' }}</dd>
                <dt class="col-5 text-muted">Address</dt><dd class="col-7">{{ $meta('address') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Gender</dt><dd class="col-7">{{ $meta('gender') === null ? '—' : ($meta('gender') == 1 ? 'Male' : 'Female') }}</dd>
                <dt class="col-5 text-muted">Employee number</dt><dd class="col-7">{{ $profile?->employee_number ?: ($meta('emp_id') ?: '—') }}</dd>
                <dt class="col-5 text-muted">Contract number</dt><dd class="col-7">{{ $meta('contract_number') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Start date</dt><dd class="col-7">{{ $meta('start_date') ? \Carbon\Carbon::parse($meta('start_date'))->format('d M Y') : '—' }}</dd>
                <dt class="col-5 text-muted">Employment status</dt>
                <dd class="col-7"><x-fleet.status-pill :status="str_replace('_', ' ', $driverStatus)">{{ ucfirst(str_replace('_', ' ', $driverStatus)) }}</x-fleet.status-pill></dd>
                <dt class="col-5 text-muted">Emergency contact</dt>
                <dd class="col-7">{{ trim(($profile?->emergency_contact_name ?: '') . ' ' . ($profile?->emergency_contact_phone ? '· ' . $profile->emergency_contact_phone : '')) ?: ($meta('econtact') ?: '—') }}</dd>
                <dt class="col-5 text-muted">Home depot</dt><dd class="col-7">{{ $profile?->home_depot ?: '—' }}</dd>
            </dl>
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Latest trips" icon="bi-signpost-2">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                    @forelse($trips->take(5) as $trip)
                        <tr>
                            <td class="fw-semibold text-nowrap"><a href="{{ route('logistics.trips.show', $trip->id) }}">{{ $trip->trip_number }}</a></td>
                            <td>{{ $trip->route_label }}</td>
                            <td class="text-muted text-nowrap">{{ $trip->planned_start_at?->format('d M Y') ?: '—' }}</td>
                            <td><x-fleet.status-pill :status="str_replace('_', ' ', $trip->status)">{{ $trip->status_label }}</x-fleet.status-pill></td>
                        </tr>
                    @empty
                        <tr><td class="text-muted">No trips assigned to this driver yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>

    <div class="col-lg-5">
        <x-fleet.panel title="Licence &amp; certification" icon="bi-card-checklist">
            @if($profile)
            <dl class="row mb-0 small">
                <dt class="col-6 text-muted">Licence number</dt><dd class="col-6">{{ $profile->licence_number ?: ($meta('license_number') ?: '—') }}</dd>
                <dt class="col-6 text-muted">Licence class</dt><dd class="col-6">{{ $profile->licence_class ?: '—' }}</dd>
                <dt class="col-6 text-muted">Licence expiry</dt>
                <dd class="col-6">
                    @if($profile->licence_expiry)
                        {{ $profile->licence_expiry->format('d M Y') }}
                        <x-fleet.status-pill class="ms-1" :status="str_replace('_', ' ', $hub->expiryState($profile->licence_expiry))">{{ ucfirst(str_replace('_', ' ', $hub->expiryState($profile->licence_expiry))) }}</x-fleet.status-pill>
                    @else — @endif
                </dd>
                <dt class="col-6 text-muted">Medical expiry</dt>
                <dd class="col-6">
                    @if($profile->medical_expiry)
                        {{ $profile->medical_expiry->format('d M Y') }}
                        <x-fleet.status-pill class="ms-1" :status="str_replace('_', ' ', $hub->expiryState($profile->medical_expiry))">{{ ucfirst(str_replace('_', ' ', $hub->expiryState($profile->medical_expiry))) }}</x-fleet.status-pill>
                    @else — @endif
                </dd>
                <dt class="col-6 text-muted">Passport expiry</dt><dd class="col-6">{{ $profile->passport_expiry?->format('d M Y') ?: '—' }}</dd>
                <dt class="col-6 text-muted">Yellow fever</dt><dd class="col-6">{{ $profile->yellow_fever_expiry?->format('d M Y') ?: '—' }}</dd>
                <dt class="col-6 text-muted">National ID</dt><dd class="col-6">{{ $profile->national_id ?: '—' }}</dd>
                <dt class="col-6 text-muted">Approved countries</dt>
                <dd class="col-6">{{ count((array) $profile->approved_country_ids) ? count((array) $profile->approved_country_ids) . ' country/countries' : 'Unrestricted' }}</dd>
            </dl>
            @else
                <x-fleet.empty icon="bi-card-checklist">No licence profile captured yet.</x-fleet.empty>
            @endif
            <a href="{{ $tabLink('compliance') }}" class="small d-inline-block mt-2">Manage documents →</a>
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Current assignment" icon="bi-truck">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Assigned trucks</dt>
                <dd class="col-7">
                    @forelse($driver->vehicles as $vehicle)
                        <a href="{{ route('vehicles.show', ['id' => $vehicle->id]) }}" class="badge bg-light text-dark border text-decoration-none">{{ $vehicle->license_plate ?: 'Truck #' . $vehicle->id }}</a>
                    @empty
                        <span class="text-muted">Not paired to a truck.</span>
                    @endforelse
                </dd>
                <dt class="col-5 text-muted">Active trip</dt>
                <dd class="col-7">
                    @if($activeTrip)
                        <a href="{{ route('logistics.trips.show', $activeTrip->id) }}">{{ $activeTrip->trip_number }}</a>
                        <div class="text-muted">{{ $activeTrip->route_label }}</div>
                        @if($activeTrip->vehicle) <div class="text-muted">{{ $activeTrip->vehicle->license_plate }}</div> @endif
                    @else
                        <span class="text-muted">No active trip.</span>
                    @endif
                </dd>
            </dl>
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Latest incidents" icon="bi-exclamation-triangle">
            @forelse($incidents->take(4) as $incident)
                <div class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold">{{ $incident->type_label }}</div>
                        <div class="text-muted small">{{ $incident->occurred_at?->format('d M Y') ?: '—' }} {{ $incident->location ? '· ' . $incident->location : '' }}</div>
                    </div>
                    <x-fleet.status-pill :status="str_replace('_', ' ', $incident->status)">{{ $incident->status_label }}</x-fleet.status-pill>
                </div>
            @empty
                <x-fleet.empty icon="bi-shield-check">No incidents recorded.</x-fleet.empty>
            @endforelse
        </x-fleet.panel>
    </div>
</div>
@endif

@if($tab === 'compliance')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Licences &amp; certifications</h5>
        @can('Compliance add')
        <a href="{{ route('logistics.documents.create', ['owner' => 'driver:' . $driver->id]) }}" class="fleet-btn"><i class="bi bi-plus-lg me-1"></i>Add document</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('drivers.show', ['id' => $driver->id]) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="compliance">
        <div class="col-md-3">
            <label class="form-label" for="doc_type">Document type</label>
            <select class="form-select" id="doc_type" name="doc_type">
                <option value="">All types</option>
                @foreach($documentTypes as $type)
                <option value="{{ $type->id }}" @selected((string) request('doc_type') === (string) $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="doc_status">Status</label>
            <select class="form-select" id="doc_status" name="doc_status">
                <option value="">All active</option>
                @foreach(['valid' => 'Valid', 'due_soon' => 'Due soon', 'expired' => 'Expired', 'no_expiry' => 'No expiry', 'archived' => 'Archived'] as $key => $label)
                <option value="{{ $key }}" @selected(request('doc_status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="doc_from">Expiry from</label>
            <input type="date" class="form-control" id="doc_from" name="doc_from" value="{{ request('doc_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="doc_to">Expiry to</label>
            <input type="date" class="form-control" id="doc_to" name="doc_to" value="{{ request('doc_to') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="fleet-btn">Filter</button>
            <a href="{{ $tabLink('compliance') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="driver-compliance-table">
            <thead>
                <tr><th>Document</th><th>Number</th><th>Issued</th><th>Expires</th><th>Status</th><th>File</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($documents as $doc)
                @php $state = $hub->expiryState($doc->expiry_date, $doc->alert_days ?: (optional($doc->type)->default_alert_days ?: 30), (bool) $doc->archived_at); @endphp
                <tr>
                    <td class="fw-semibold">{{ optional($doc->type)->name ?: 'Document' }}</td>
                    <td>{{ $doc->document_number ?: '—' }}</td>
                    <td class="text-nowrap">{{ $doc->issue_date?->format('d M Y') ?: '—' }}</td>
                    <td class="text-nowrap">{{ $doc->expiry_date?->format('d M Y') ?: 'No expiry' }}</td>
                    <td><x-fleet.status-pill :status="str_replace('_', ' ', $state)">{{ ucfirst(str_replace('_', ' ', $state)) }}</x-fleet.status-pill></td>
                    <td>
                        @if($doc->file_path)
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="badge bg-light text-dark border text-decoration-none"><i class="bi bi-paperclip me-1"></i>Open</a>
                        @else — @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ $printLink('compliance', $doc->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        @can('Compliance edit')
                        <a href="{{ route('logistics.documents.edit', ['id' => $doc->id]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                        @endcan
                        <a href="{{ $pdfLink('compliance', $doc->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="{{ $printLink('compliance', $doc->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                        @can('Compliance delete')
                        @if(!$doc->archived_at)
                        <form method="POST" action="{{ route('fleet.records.archive', ['type' => 'compliance', 'id' => $doc->id]) }}" class="d-inline" data-fleet-confirm="Archive this document?">
                            @csrf
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Archive"><i class="bi bi-archive"></i></button>
                        </form>
                        @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-shield-check d-block mb-2" style="font-size:24px"></i>No documents match this filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>
@endif

@if($tab === 'performance')
<div class="row g-3">
    <div class="col-lg-8">
        <x-fleet.panel title="Trip history" icon="bi-signpost-2">
            <form method="GET" action="{{ route('drivers.show', ['id' => $driver->id]) }}" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="performance">
                <div class="col-md-3">
                    <label class="form-label" for="trip_status">Status</label>
                    <select class="form-select" id="trip_status" name="trip_status">
                        <option value="">All statuses</option>
                        @foreach($tripStatuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('trip_status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="trip_from">From</label>
                    <input type="date" class="form-control" id="trip_from" name="trip_from" value="{{ request('trip_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="trip_to">To</label>
                    <input type="date" class="form-control" id="trip_to" name="trip_to" value="{{ request('trip_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="fleet-btn">Filter</button>
                    <a href="{{ $tabLink('performance') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="driver-trips-table">
                    <thead><tr><th>Trip</th><th>Route</th><th>Truck / trailer</th><th>Customer</th><th>Date</th><th>Distance</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @forelse($trips as $trip)
                        <tr>
                            <td class="fw-semibold text-nowrap"><a href="{{ route('logistics.trips.show', $trip->id) }}">{{ $trip->trip_number }}</a></td>
                            <td>{{ $trip->route_label }}</td>
                            <td class="small">
                                {{ optional($trip->vehicle)->license_plate ?: '—' }}
                                @if($trip->trailer)<div class="text-muted">{{ $trip->trailer->registration_number }}</div>@endif
                            </td>
                            <td>{{ optional($trip->customer)->name ?: '—' }}</td>
                            <td class="text-nowrap small">{{ $trip->planned_start_at?->format('d M Y') ?: '—' }}</td>
                            <td>{{ $trip->distance_km ? number_format((float) $trip->distance_km, 1) . ' km' : '—' }}</td>
                            <td><x-fleet.status-pill :status="str_replace('_', ' ', $trip->status)">{{ $trip->status_label }}</x-fleet.status-pill></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('logistics.trips.show', $trip->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                                @can('Trips edit')
                                <a href="{{ route('logistics.trips.edit', ['id' => $trip->id]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                                @endcan
                                <a href="{{ $pdfLink('trip', $trip->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                <a href="{{ $printLink('trip', $trip->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-signpost-2 d-block mb-2" style="font-size:24px"></i>No trips match this filter.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>

    <div class="col-lg-4">
        <x-fleet.panel title="Rating summary" icon="bi-star">
            <div class="text-center py-3">
                <div class="display-6 fw-semibold">{{ $averageRating !== null ? $averageRating : '—' }}</div>
                <div class="text-warning fs-5">{{ $averageRating !== null ? $stars(round($averageRating)) : '☆☆☆☆☆' }}</div>
                <div class="text-muted small">{{ $ratings->count() }} rating{{ $ratings->count() === 1 ? '' : 's' }}</div>
                @can('Drivers edit')
                <button type="button" class="fleet-btn fleet-btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#ratingModal"><i class="bi bi-plus-lg me-1"></i>Record rating</button>
                @endcan
            </div>
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Recent feedback" icon="bi-chat-left-text">
            @forelse($ratings->take(6) as $rating)
                <div class="py-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="text-warning">{{ $stars($rating->rating) }}</div>
                        <div class="text-muted small">{{ $rating->rated_at?->format('d M Y') ?: '—' }}</div>
                    </div>
                    <div class="text-muted small">
                        {{ optional($rating->customer)->name ?: 'Customer' }}
                        @if($rating->trip) · {{ $rating->trip->trip_number }} @endif
                    </div>
                    @if($rating->feedback)<div class="small mt-1">{{ \Illuminate\Support\Str::limit($rating->feedback, 120) }}</div>@endif
                    <div class="mt-1">
                        <a href="{{ $printLink('rating', $rating->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ $pdfLink('rating', $rating->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                    </div>
                </div>
            @empty
                <x-fleet.empty icon="bi-star">No ratings recorded yet.</x-fleet.empty>
            @endforelse
        </x-fleet.panel>
    </div>
</div>

@can('Drivers edit')
<x-fleet.modal id="ratingModal" title="Record driver rating">
    <form method="POST" action="{{ route('fleet.drivers.ratings.store', ['id' => $driver->id]) }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label" for="rating_trip_id">Trip</label>
                <select class="form-select" id="rating_trip_id" name="trip_id">
                    <option value="">No specific trip</option>
                    @foreach($tripOptions as $option)
                    <option value="{{ $option->id }}" @selected((string) old('trip_id') === (string) $option->id)>{{ $option->trip_number }} · {{ $option->route_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="rating_customer_id">Customer</label>
                <select class="form-select" id="rating_customer_id" name="customer_id">
                    <option value="">Not linked</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="rating_value">Rating</label>
                <select class="form-select" id="rating_value" name="rating" required>
                    @foreach([5 => '5 — Excellent', 4 => '4 — Good', 3 => '3 — Fair', 2 => '2 — Poor', 1 => '1 — Very poor'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) old('rating', 5) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="rating_date">Rated on</label>
                <input type="date" class="form-control" id="rating_date" name="rated_at" value="{{ old('rated_at', $today) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="rating_feedback">Feedback</label>
                <textarea class="form-control" id="rating_feedback" name="feedback" rows="3" maxlength="4000">{{ old('feedback') }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button class="fleet-btn">Save rating</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
</x-fleet.modal>
@endcan
@endif

@if($tab === 'incidents')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Incidents &amp; fines</h5>
        @can('Drivers edit')
        <button type="button" class="fleet-btn" data-bs-toggle="modal" data-bs-target="#incidentCreateModal"><i class="bi bi-plus-lg me-1"></i>Log incident</button>
        @endcan
    </div>

    <form method="GET" action="{{ route('drivers.show', ['id' => $driver->id]) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="incidents">
        <div class="col-md-3">
            <label class="form-label" for="inc_type">Type</label>
            <select class="form-select" id="inc_type" name="inc_type">
                <option value="">All types</option>
                @foreach($incidentTypes as $key => $label)
                <option value="{{ $key }}" @selected(request('inc_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="inc_status">Status</label>
            <select class="form-select" id="inc_status" name="inc_status">
                <option value="">All active</option>
                @foreach($incidentStatuses as $key => $label)
                <option value="{{ $key }}" @selected(request('inc_status') === $key)>{{ $label }}</option>
                @endforeach
                <option value="archived" @selected(request('inc_status') === 'archived')>Archived</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="inc_from">From</label>
            <input type="date" class="form-control" id="inc_from" name="inc_from" value="{{ request('inc_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="inc_to">To</label>
            <input type="date" class="form-control" id="inc_to" name="inc_to" value="{{ request('inc_to') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="fleet-btn">Filter</button>
            <a href="{{ $tabLink('incidents') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="driver-incidents-table">
            <thead><tr><th>Date</th><th>Type</th><th>Trip</th><th>Location</th><th>Fine</th><th>Points</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($incidents as $incident)
                <tr>
                    <td class="text-nowrap">{{ $incident->occurred_at?->format('d M Y') ?: '—' }}</td>
                    <td class="fw-semibold">{{ $incident->type_label }}</td>
                    <td>{{ optional($incident->trip)->trip_number ?: '—' }}</td>
                    <td>{{ $incident->location ?: '—' }}</td>
                    <td class="text-nowrap">{{ $money($incident->fine_amount, $incident->currency_code) }}</td>
                    <td>{{ $incident->points ?? '—' }}</td>
                    <td><x-fleet.status-pill :status="str_replace('_', ' ', $incident->status)">{{ $incident->status_label }}</x-fleet.status-pill></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ $printLink('incident', $incident->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        @can('Drivers edit')
                        <button type="button" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"
                                data-bs-toggle="modal" data-bs-target="#incidentEditModal"
                                data-url="{{ route('fleet.drivers.incidents.update', ['id' => $driver->id, 'incident' => $incident->id]) }}"
                                data-f-trip_id="{{ $incident->trip_id }}"
                                data-f-type="{{ $incident->type }}"
                                data-f-occurred_at="{{ $incident->occurred_at?->format('Y-m-d\TH:i') }}"
                                data-f-location="{{ $incident->location }}"
                                data-f-description="{{ $incident->description }}"
                                data-f-fine_amount="{{ $incident->fine_amount }}"
                                data-f-currency_code="{{ $incident->currency_code }}"
                                data-f-exchange_rate="{{ $incident->exchange_rate }}"
                                data-f-points="{{ $incident->points }}"
                                data-f-status="{{ $incident->status }}"
                                data-f-action_taken="{{ $incident->action_taken }}"
                                data-f-resolved_at="{{ $incident->resolved_at?->format('Y-m-d\TH:i') }}"><i class="bi bi-pencil"></i></button>
                        @endcan
                        <a href="{{ $pdfLink('incident', $incident->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="{{ $printLink('incident', $incident->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                        @can('Drivers edit')
                        @if(!$incident->archived_at)
                        <form method="POST" action="{{ route('fleet.drivers.incidents.archive', ['id' => $driver->id, 'incident' => $incident->id]) }}" class="d-inline" data-fleet-confirm="Archive this incident?">
                            @csrf
                            @method('DELETE')
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Archive"><i class="bi bi-archive"></i></button>
                        </form>
                        @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-shield-check d-block mb-2" style="font-size:24px"></i>No incidents match this filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>

@can('Drivers edit')
<x-fleet.modal id="incidentCreateModal" title="Log incident">
    <form method="POST" action="{{ route('fleet.drivers.incidents.store', ['id' => $driver->id]) }}">
        @csrf
        @include('drivers.partials.incident-fields', ['incident' => null])
        <div class="d-flex gap-2 mt-3">
            <button class="fleet-btn">Save incident</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
</x-fleet.modal>

<x-fleet.modal id="incidentEditModal" title="Edit incident">
    <form method="POST" action="" id="incidentEditForm">
        @csrf
        @method('PUT')
        @include('drivers.partials.incident-fields', ['incident' => null])
        <div class="d-flex gap-2 mt-3">
            <button class="fleet-btn">Update incident</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
</x-fleet.modal>
@endcan
@endif

@if($tab === 'financials')
<div class="row g-3">
    <div class="col-lg-5">
        <x-fleet.panel title="Payroll profile" icon="bi-cash-coin">
            <div class="fleet-toolbar">
                <h6 class="mb-0 text-muted">Salary &amp; allowances</h6>
                @can('Drivers edit')
                <button type="button" class="fleet-btn fleet-btn-sm" data-bs-toggle="modal" data-bs-target="#payrollModal"><i class="bi bi-pencil me-1"></i>Edit</button>
                @endcan
            </div>
            @if($payroll)
            <dl class="row mb-0 small">
                <dt class="col-6 text-muted">Salary</dt><dd class="col-6">{{ $money($payroll->salary_amount, $payroll->currency_code) }}</dd>
                <dt class="col-6 text-muted">Pay frequency</dt><dd class="col-6">{{ ucfirst(str_replace('_', ' ', $payroll->pay_frequency)) }}</dd>
                <dt class="col-6 text-muted">Daily allowance</dt><dd class="col-6">{{ $money($payroll->daily_allowance, $payroll->currency_code) }}</dd>
                <dt class="col-6 text-muted">Fuel allowance</dt><dd class="col-6">{{ $money($payroll->fuel_allowance, $payroll->currency_code) }}</dd>
                <dt class="col-6 text-muted">Bank</dt><dd class="col-6">{{ $payroll->bank_name ?: '—' }}</dd>
                <dt class="col-6 text-muted">Account</dt><dd class="col-6">{{ $payroll->bank_account ?: '—' }}</dd>
            </dl>
            @if($payroll->notes)<div class="text-muted small mt-2">{{ $payroll->notes }}</div>@endif
            @else
                <x-fleet.empty icon="bi-cash-coin">No payroll profile captured yet.</x-fleet.empty>
            @endif
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Financial summary" icon="bi-bar-chart">
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Advances issued (base)</span>
                <span class="fw-semibold">{{ $money($kpis['advances_total']) }}</span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Active deductions (base)</span>
                <span class="fw-semibold">{{ $money($kpis['deductions_total']) }}</span>
            </div>
            <div class="d-flex justify-content-between py-2">
                <span class="text-muted">Open incidents</span>
                <span class="fw-semibold">{{ $kpis['open_incidents'] }}</span>
            </div>
        </x-fleet.panel>
    </div>

    <div class="col-lg-7">
        <x-fleet.panel title="Trip advances &amp; allowances" icon="bi-wallet2">
            <form method="GET" action="{{ route('drivers.show', ['id' => $driver->id]) }}" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="financials">
                <div class="col-md-3">
                    <label class="form-label" for="fin_from">From</label>
                    <input type="date" class="form-control" id="fin_from" name="fin_from" value="{{ request('fin_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="fin_to">To</label>
                    <input type="date" class="form-control" id="fin_to" name="fin_to" value="{{ request('fin_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="ded_type">Deduction type</label>
                    <select class="form-select" id="ded_type" name="ded_type">
                        <option value="">All types</option>
                        @foreach($deductionTypes as $key => $label)
                        <option value="{{ $key }}" @selected(request('ded_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="ded_status">Deduction status</label>
                    <select class="form-select" id="ded_status" name="ded_status">
                        <option value="">All active</option>
                        @foreach($deductionStatuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('ded_status') === $key)>{{ $label }}</option>
                        @endforeach
                        <option value="archived" @selected(request('ded_status') === 'archived')>Archived</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="fleet-btn">Filter</button>
                    <a href="{{ $tabLink('financials') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="driver-advances-table">
                    <thead><tr><th>Date</th><th>Trip</th><th>Purpose</th><th>Amount</th><th>Base</th><th>Method / reference</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @forelse($advances as $advance)
                        <tr>
                            <td class="text-nowrap">{{ $advance->issued_at?->format('d M Y') ?: '—' }}</td>
                            <td>
                                @if($advance->trip)
                                <a href="{{ route('logistics.trips.show', $advance->trip->id) }}">{{ $advance->trip->trip_number }}</a>
                                @else — @endif
                            </td>
                            <td>{{ ucfirst($advance->purpose ?: '—') }}</td>
                            <td class="text-nowrap">{{ $money($advance->amount, $advance->currency) }}</td>
                            <td class="text-nowrap">{{ $money($advance->amount_base) }}</td>
                            <td class="small">{{ trim(($advance->method ? ucfirst($advance->method) : '') . ($advance->reference ? ' · ' . $advance->reference : '')) ?: '—' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ $printLink('advance', $advance->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ $pdfLink('advance', $advance->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                <a href="{{ $printLink('advance', $advance->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                                @can('Advances delete')
                                <form method="POST" action="{{ route('fleet.records.archive', ['type' => 'advance', 'id' => $advance->id]) }}" class="d-inline" data-fleet-confirm="Archive this advance?">
                                    @csrf
                                    <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Archive"><i class="bi bi-archive"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No advances in this period.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Deductions" icon="bi-dash-circle">
            <div class="fleet-toolbar">
                <h6 class="mb-0 text-muted">Loans, recoveries and penalties</h6>
                @can('Drivers edit')
                <button type="button" class="fleet-btn fleet-btn-sm" data-bs-toggle="modal" data-bs-target="#deductionCreateModal"><i class="bi bi-plus-lg me-1"></i>Add deduction</button>
                @endcan
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="driver-deductions-table">
                    <thead><tr><th>Incurred</th><th>Type</th><th>Description</th><th>Amount</th><th>Balance</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @forelse($deductions as $deduction)
                        <tr>
                            <td class="text-nowrap">{{ $deduction->incurred_on?->format('d M Y') ?: '—' }}</td>
                            <td>{{ $deduction->type_label }}</td>
                            <td>{{ $deduction->description }}</td>
                            <td class="text-nowrap">{{ $money($deduction->amount, $deduction->currency_code) }}</td>
                            <td class="text-nowrap">{{ $money($deduction->balance, $deduction->currency_code) }}</td>
                            <td><x-fleet.status-pill :status="str_replace('_', ' ', $deduction->status)">{{ ucfirst($deduction->status) }}</x-fleet.status-pill></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ $printLink('deduction', $deduction->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                                @can('Drivers edit')
                                <button type="button" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#deductionEditModal"
                                        data-url="{{ route('fleet.drivers.deductions.update', ['id' => $driver->id, 'deduction' => $deduction->id]) }}"
                                        data-f-type="{{ $deduction->type }}"
                                        data-f-description="{{ $deduction->description }}"
                                        data-f-amount="{{ $deduction->amount }}"
                                        data-f-currency_code="{{ $deduction->currency_code }}"
                                        data-f-exchange_rate="{{ $deduction->exchange_rate }}"
                                        data-f-balance="{{ $deduction->balance }}"
                                        data-f-incurred_on="{{ $deduction->incurred_on?->format('Y-m-d') }}"
                                        data-f-status="{{ $deduction->status }}"><i class="bi bi-pencil"></i></button>
                                @endcan
                                <a href="{{ $pdfLink('deduction', $deduction->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                <a href="{{ $printLink('deduction', $deduction->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                                @can('Drivers edit')
                                @if(!$deduction->archived_at)
                                <form method="POST" action="{{ route('fleet.drivers.deductions.archive', ['id' => $driver->id, 'deduction' => $deduction->id]) }}" class="d-inline" data-fleet-confirm="Archive this deduction?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Archive"><i class="bi bi-archive"></i></button>
                                </form>
                                @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No deductions recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>
</div>

@can('Drivers edit')
<x-fleet.modal id="payrollModal" title="Payroll profile">
    <form method="POST" action="{{ route('fleet.drivers.payroll.update', ['id' => $driver->id]) }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label" for="payroll_salary">Salary</label>
                <input type="number" step="0.01" min="0" class="form-control" id="payroll_salary" name="salary_amount" value="{{ old('salary_amount', $payroll?->salary_amount) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="payroll_currency">Currency</label>
                <select class="form-select" id="payroll_currency" name="currency_code" required>
                    @foreach($currencies as $currency)
                    <option value="{{ $currency->code }}" @selected(old('currency_code', $payroll?->currency_code ?: \App\Services\CurrencyService::base()) === $currency->code)>{{ $currency->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="payroll_frequency">Frequency</label>
                <select class="form-select" id="payroll_frequency" name="pay_frequency" required>
                    @foreach(['monthly' => 'Monthly', 'fortnightly' => 'Fortnightly', 'weekly' => 'Weekly', 'per_trip' => 'Per trip'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('pay_frequency', $payroll?->pay_frequency ?: 'monthly') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="payroll_daily">Daily allowance</label>
                <input type="number" step="0.01" min="0" class="form-control" id="payroll_daily" name="daily_allowance" value="{{ old('daily_allowance', $payroll?->daily_allowance) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="payroll_fuel">Fuel allowance</label>
                <input type="number" step="0.01" min="0" class="form-control" id="payroll_fuel" name="fuel_allowance" value="{{ old('fuel_allowance', $payroll?->fuel_allowance) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="payroll_bank">Bank name</label>
                <input type="text" class="form-control" id="payroll_bank" name="bank_name" maxlength="100" value="{{ old('bank_name', $payroll?->bank_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="payroll_account">Bank account</label>
                <input type="text" class="form-control" id="payroll_account" name="bank_account" maxlength="100" value="{{ old('bank_account', $payroll?->bank_account) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="payroll_notes">Notes</label>
                <textarea class="form-control" id="payroll_notes" name="notes" rows="2" maxlength="2000">{{ old('notes', $payroll?->notes) }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button class="fleet-btn">Save payroll profile</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
</x-fleet.modal>

<x-fleet.modal id="deductionCreateModal" title="Add deduction">
    <form method="POST" action="{{ route('fleet.drivers.deductions.store', ['id' => $driver->id]) }}">
        @csrf
        @include('drivers.partials.deduction-fields', ['deduction' => null])
        <div class="d-flex gap-2 mt-3">
            <button class="fleet-btn">Save deduction</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
</x-fleet.modal>

<x-fleet.modal id="deductionEditModal" title="Edit deduction">
    <form method="POST" action="" id="deductionEditForm">
        @csrf
        @method('PUT')
        @include('drivers.partials.deduction-fields', ['deduction' => null])
        <div class="d-flex gap-2 mt-3">
            <button class="fleet-btn">Update deduction</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
</x-fleet.modal>
@endcan
@endif
@endsection

@section('script')
<script>
    $(function () {
        if ($.fn.DataTable) {
            function init(id, actionIndex, label) {
                if (!document.getElementById(id)) return;
                $('#' + id).DataTable({
                    order: [],
                    pageLength: 10,
                    columnDefs: [{ orderable: false, targets: [actionIndex] }],
                    language: {
                        search: '',
                        searchPlaceholder: 'Search ' + label + '…',
                        lengthMenu: '_MENU_ per page',
                        info: 'Showing _START_ to _END_ of _TOTAL_ ' + label,
                        emptyTable: 'No records found.'
                    }
                });
            }

            init('driver-compliance-table', 6, 'documents');
            init('driver-trips-table', 7, 'trips');
            init('driver-incidents-table', 7, 'incidents');
            init('driver-advances-table', 6, 'advances');
            init('driver-deductions-table', 6, 'deductions');
        }

        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form[data-fleet-confirm]');
            if (form && !window.confirm(form.getAttribute('data-fleet-confirm'))) {
                event.preventDefault();
            }
        });

        function fillForm(form, trigger, names) {
            if (!form || !trigger) return;
            form.action = trigger.getAttribute('data-url') || form.action;
            names.forEach(function (name) {
                var field = form.querySelector('[name="' + name + '"]');
                if (!field) return;
                var value = trigger.getAttribute('data-f-' + name);
                field.value = value === null ? '' : value;
            });
        }

        var incidentModal = document.getElementById('incidentEditModal');
        if (incidentModal) {
            incidentModal.addEventListener('show.bs.modal', function (event) {
                fillForm(document.getElementById('incidentEditForm'), event.relatedTarget, [
                    'trip_id', 'type', 'occurred_at', 'location', 'description', 'fine_amount',
                    'currency_code', 'exchange_rate', 'points', 'status', 'action_taken', 'resolved_at'
                ]);
            });
        }

        var deductionModal = document.getElementById('deductionEditModal');
        if (deductionModal) {
            deductionModal.addEventListener('show.bs.modal', function (event) {
                fillForm(document.getElementById('deductionEditForm'), event.relatedTarget, [
                    'type', 'description', 'amount', 'currency_code', 'exchange_rate',
                    'balance', 'incurred_on', 'status'
                ]);
            });
        }
    });
</script>
@endsection
