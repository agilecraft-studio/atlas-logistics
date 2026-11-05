@extends('layouts.attex')

@section('page_title', 'Truck · ' . $vehicle->license_plate)
@section('fleet_subtitle', 'Specs, compliance, maintenance, fuel and trip history')

@section('content')
@php
    $hub = app(\App\Services\FleetHubService::class);
    $money = fn ($amount, $code = null) => $amount === null || $amount === ''
        ? '—'
        : \App\Support\Money::format(\App\Support\Money::toMinor($amount), $code ?: \App\Services\CurrencyService::base());
    $tabLink = fn ($key) => route('vehicles.show', ['id' => $vehicle->id, 'tab' => $key]);
    $printLink = fn ($type, $id) => route('fleet.records.print', ['type' => $type, 'id' => $id]);
    $pdfLink = fn ($type, $id) => route('fleet.records.pdf', ['type' => $type, 'id' => $id]);
    $image = $vehicle->vehicle_image ? asset('uploads/' . $vehicle->vehicle_image) : null;
@endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
<x-fleet.alert type="danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-fleet.alert>
@endif

<x-fleet.panel>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            @if($image)
                <img src="{{ $image }}" alt="{{ $vehicle->license_plate }}" class="rounded" style="width:58px;height:58px;object-fit:cover">
            @else
                <div class="rounded d-flex align-items-center justify-content-center text-white" style="width:58px;height:58px;background:var(--atlas-navy)">
                    <i class="bi bi-truck" style="font-size:26px"></i>
                </div>
            @endif
            <div>
                <h4 class="mb-1">
                    {{ $vehicle->license_plate ?: 'Truck #' . $vehicle->id }}
                    <x-fleet.status-pill class="ms-2" :status="str_replace('_', ' ', $vehicleStatus)">{{ ucfirst(str_replace('_', ' ', $vehicleStatus)) }}</x-fleet.status-pill>
                </h4>
                <div class="text-muted small">
                    {{ trim(($vehicle->make_name ?? '') . ' ' . ($vehicle->model_name ?? '') . ' ' . ($vehicle->year ?? '')) ?: 'Make and model not recorded' }}
                    @if($vehicle->vin) · VIN {{ $vehicle->vin }} @endif
                    @if($vehicle->group) · {{ $vehicle->group->name }} @endif
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('Vehicles edit')
            <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="fleet-btn"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            <a href="{{ $printLink('vehicle', $vehicle->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer me-1"></i>Print</a>
            <a href="{{ $pdfLink('vehicle', $vehicle->id) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="{{ route('vehicles.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Trips" :value="$kpis['trips']" icon="bi-signpost-2" color="bg-blue" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Distance" :value="number_format($kpis['distance'], 0) . ' km'" icon="bi-speedometer2" color="bg-teal" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Odometer" :value="number_format((float) $kpis['odometer'], 0) . ' km'" icon="bi-gauge" color="bg-amber" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Compliance alerts" :value="$kpis['compliance_alerts']" icon="bi-shield-exclamation" color="bg-red" /></div>
    </div>
</x-fleet.panel>

<ul class="nav nav-tabs nav-bordered mb-3 mt-3" role="tablist">
    <li class="nav-item"><a class="nav-link {{ $tab === 'overview' ? 'active' : '' }}" href="{{ $tabLink('overview') }}"><i class="bi bi-truck me-1"></i>Overview</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'compliance' ? 'active' : '' }}" href="{{ $tabLink('compliance') }}"><i class="bi bi-shield-check me-1"></i>Compliance <span class="badge bg-light text-dark border ms-1">{{ $docCounts['total'] }}</span></a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'maintenance' ? 'active' : '' }}" href="{{ $tabLink('maintenance') }}"><i class="bi bi-tools me-1"></i>Maintenance</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'fuel' ? 'active' : '' }}" href="{{ $tabLink('fuel') }}"><i class="bi bi-fuel-pump me-1"></i>Fuel &amp; Telematics</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'trips' ? 'active' : '' }}" href="{{ $tabLink('trips') }}"><i class="bi bi-signpost-split me-1"></i>Trip history <span class="badge bg-light text-dark border ms-1">{{ $kpis['trips'] }}</span></a></li>
</ul>

@if($tab === 'overview')
<div class="row g-3">
    <div class="col-lg-7">
        <x-fleet.panel title="Specifications" icon="bi-clipboard-data">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Make</dt><dd class="col-7">{{ $vehicle->make_name ?: '—' }}</dd>
                <dt class="col-5 text-muted">Model</dt><dd class="col-7">{{ $vehicle->model_name ?: '—' }}</dd>
                <dt class="col-5 text-muted">Year</dt><dd class="col-7">{{ $vehicle->year ?: '—' }}</dd>
                <dt class="col-5 text-muted">VIN / chassis</dt><dd class="col-7">{{ $vehicle->vin ?: ($profile?->chassis_number ?: '—') }}</dd>
                <dt class="col-5 text-muted">Registration number</dt><dd class="col-7">{{ $vehicle->license_plate ?: '—' }}</dd>
                <dt class="col-5 text-muted">Fuel / engine type</dt><dd class="col-7">{{ $vehicle->engine_type ?: '—' }}</dd>
                <dt class="col-5 text-muted">Engine number</dt><dd class="col-7">{{ $profile?->engine_number ?: '—' }}</dd>
                <dt class="col-5 text-muted">Odometer</dt><dd class="col-7">{{ number_format((float) $kpis['odometer'], 0) }} km</dd>
                <dt class="col-5 text-muted">Horse power</dt><dd class="col-7">{{ $vehicle->horse_power ?: '—' }}</dd>
                <dt class="col-5 text-muted">Colour</dt><dd class="col-7">{{ $vehicle->color_name ?: '—' }}</dd>
                <dt class="col-5 text-muted">Group / type</dt>
                <dd class="col-7">{{ trim((optional($vehicle->group)->name ?? '') . ' · ' . (optional($vehicle->types)->displayname ?? ''), ' ·') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Depot / location</dt><dd class="col-7">{{ $profile?->depot ?: '—' }}</dd>
                <dt class="col-5 text-muted">Title holder</dt><dd class="col-7">{{ $profile?->title_holder ?: '—' }}</dd>
                <dt class="col-5 text-muted">Fuel tank</dt><dd class="col-7">{{ $profile?->fuel_tank_capacity_l ? $profile->fuel_tank_capacity_l . ' L' : '—' }}</dd>
                <dt class="col-5 text-muted">Ownership</dt><dd class="col-7">{{ $profile?->ownership_type ? ucfirst(str_replace('_', ' ', $profile->ownership_type)) : '—' }}</dd>
            </dl>
        </x-fleet.panel>
    </div>
    <div class="col-lg-5">
        <x-fleet.panel title="Current deployment" icon="bi-broadcast">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Status</dt>
                <dd class="col-7"><x-fleet.status-pill :status="str_replace('_', ' ', $vehicleStatus)">{{ ucfirst(str_replace('_', ' ', $vehicleStatus)) }}</x-fleet.status-pill></dd>
                <dt class="col-5 text-muted">Assigned drivers</dt>
                <dd class="col-7">{{ $vehicle->drivers->pluck('name')->implode(', ') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Active trip</dt>
                <dd class="col-7">
                    @if($activeTrip)
                        <a href="{{ route('logistics.trips.show', $activeTrip->id) }}">{{ $activeTrip->trip_number }}</a>
                        <div class="text-muted">{{ $activeTrip->route_label }}</div>
                    @else — @endif
                </dd>
                <dt class="col-5 text-muted">Trailer attached</dt>
                <dd class="col-7">{{ optional(optional($currentTrailer)->trailer)->registration_number ?: '—' }}</dd>
                <dt class="col-5 text-muted">Next service</dt>
                <dd class="col-7">{{ $profile?->next_service_date?->format('d M Y') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Next service odometer</dt>
                <dd class="col-7">{{ $profile?->next_service_odometer ? number_format((float) $profile->next_service_odometer, 0) . ' km' : '—' }}</dd>
            </dl>
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Compliance snapshot" icon="bi-shield-check">
            @forelse($documents->take(4) as $doc)
                @php $state = $hub->expiryState($doc->expiry_date, $doc->alert_days ?: (optional($doc->type)->default_alert_days ?: $hub->alertDays()), (bool) $doc->archived_at); @endphp
                <div class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold">{{ optional($doc->type)->name ?: 'Document' }}</div>
                        <div class="text-muted small">{{ $doc->document_number ?: 'No number' }} {{ $doc->expiry_date ? '· expires ' . $doc->expiry_date->format('d M Y') : '' }}</div>
                    </div>
                    <x-fleet.status-pill :status="str_replace('_', ' ', $state)">{{ ucfirst(str_replace('_', ' ', $state)) }}</x-fleet.status-pill>
                </div>
            @empty
                <x-fleet.empty icon="bi-shield-check">No compliance documents on file.</x-fleet.empty>
            @endforelse
            <a href="{{ $tabLink('compliance') }}" class="small d-inline-block mt-2">Manage compliance →</a>
        </x-fleet.panel>
    </div>

    <div class="col-lg-7">
        <x-fleet.panel title="Latest maintenance" icon="bi-tools">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                    @forelse($maintenance->take(5) as $order)
                        <tr>
                            <td class="text-nowrap fw-semibold">{{ $order->created_on?->format('d M Y') ?: '—' }}</td>
                            <td>{{ $order->maintenance_type_label }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Str::limit($order->description ?: '—', 60) }}</td>
                            <td><x-fleet.status-pill :status="$order->status ?: 'pending'">{{ ucfirst(str_replace('_', ' ', $order->status ?: 'pending')) }}</x-fleet.status-pill></td>
                            <td class="text-end"><a href="{{ route('work_order.edit', $order->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost">View</a></td>
                        </tr>
                    @empty
                        <tr><td class="text-muted">No maintenance recorded for this truck.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>
    <div class="col-lg-5">
        <x-fleet.panel title="Latest trips" icon="bi-signpost-2">
            @forelse($trips->take(5) as $trip)
                <div class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom">
                    <div>
                        <a href="{{ route('logistics.trips.show', $trip->id) }}" class="fw-semibold">{{ $trip->trip_number }}</a>
                        <div class="text-muted small">{{ $trip->route_label }} {{ $trip->distance_km ? '· ' . number_format((float) $trip->distance_km, 0) . ' km' : '' }}</div>
                    </div>
                    <x-fleet.status-pill :status="str_replace('_', ' ', $trip->status)">{{ $trip->status_label }}</x-fleet.status-pill>
                </div>
            @empty
                <x-fleet.empty icon="bi-signpost-2">No trips assigned to this truck.</x-fleet.empty>
            @endforelse
        </x-fleet.panel>
    </div>
</div>
@endif

@if($tab === 'compliance')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Compliance &amp; documents</h5>
        @can('Compliance add')
        <a href="{{ route('logistics.documents.create', ['owner' => 'vehicle:' . $vehicle->id]) }}" class="fleet-btn"><i class="bi bi-plus-lg me-1"></i>Add document</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('vehicles.show', ['id' => $vehicle->id]) }}" class="row g-2 align-items-end mb-3">
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
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="vehicle-compliance-table">
            <thead>
                <tr><th>Document</th><th>Number</th><th>Issued</th><th>Expires</th><th>Status</th><th>File</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($documents as $doc)
                @php $state = $hub->expiryState($doc->expiry_date, $doc->alert_days ?: (optional($doc->type)->default_alert_days ?: $hub->alertDays()), (bool) $doc->archived_at); @endphp
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
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-shield-check d-block mb-2" style="font-size:24px"></i>No compliance documents match this filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>
@endif

@if($tab === 'maintenance')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-tools me-2"></i>Maintenance &amp; repairs</h5>
        @can('Vehicles edit')
        <a href="{{ route('work_order.create') }}" class="fleet-btn"><i class="bi bi-plus-lg me-1"></i>Log maintenance</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('vehicles.show', ['id' => $vehicle->id]) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="maintenance">
        <div class="col-md-3">
            <label class="form-label" for="mnt_type">Type</label>
            <select class="form-select" id="mnt_type" name="mnt_type">
                <option value="">All types</option>
                @foreach($maintenanceTypes as $key => $label)
                <option value="{{ $key }}" @selected(request('mnt_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="mnt_status">Status</label>
            <select class="form-select" id="mnt_status" name="mnt_status">
                <option value="">All statuses</option>
                @foreach($maintenanceStatuses as $key => $label)
                <option value="{{ $key }}" @selected(request('mnt_status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="mnt_from">From</label>
            <input type="date" class="form-control" id="mnt_from" name="mnt_from" value="{{ request('mnt_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="mnt_to">To</label>
            <input type="date" class="form-control" id="mnt_to" name="mnt_to" value="{{ request('mnt_to') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="fleet-btn">Filter</button>
            <a href="{{ $tabLink('maintenance') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="vehicle-maintenance-table">
            <thead>
                <tr><th>Date</th><th>Type</th><th>Description</th><th>Vendor / mechanic</th><th>Odometer</th><th>Parts</th><th>Cost</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($maintenance as $order)
                <tr>
                    <td class="text-nowrap">{{ $order->created_on?->format('d M Y') ?: '—' }}</td>
                    <td>{{ $order->maintenance_type_label }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($order->description ?: '—', 70) }}</td>
                    <td>
                        {{ optional($order->vendor)->name ?: '—' }}
                        @if($order->mechanic)<div class="text-muted small">{{ $order->mechanic->name }}</div>@endif
                    </td>
                    <td>{{ $order->meter ? number_format((float) $order->meter, 0) . ' km' : '—' }}</td>
                    <td>{{ $order->parts->map(fn ($p) => optional($p->part)->title ?: optional($p->part)->description)->filter()->implode(', ') ?: '—' }}</td>
                    <td class="text-nowrap">{{ $money($order->price) }}</td>
                    <td><x-fleet.status-pill :status="$order->status ?: 'pending'">{{ ucfirst(str_replace('_', ' ', $order->status ?: 'pending')) }}</x-fleet.status-pill></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('work_order.edit', $order->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View / edit"><i class="bi bi-eye"></i></a>
                        <a href="{{ $pdfLink('work_order', $order->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="{{ $printLink('work_order', $order->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                        @can('Vehicles edit')
                        <form method="POST" action="{{ route('fleet.records.archive', ['type' => 'work_order', 'id' => $order->id]) }}" class="d-inline" data-fleet-confirm="Archive this maintenance record?">
                            @csrf
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Archive"><i class="bi bi-archive"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-tools d-block mb-2" style="font-size:24px"></i>No maintenance records for this truck.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>
@endif

@if($tab === 'fuel')
<div class="row g-3">
    <div class="col-lg-7">
        <x-fleet.panel title="Fuel consumption" icon="bi-fuel-pump">
            <form method="GET" action="{{ route('vehicles.show', ['id' => $vehicle->id]) }}" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="fuel">
                <div class="col-md-4">
                    <label class="form-label" for="fuel_from">From</label>
                    <input type="date" class="form-control" id="fuel_from" name="fuel_from" value="{{ request('fuel_from') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="fuel_to">To</label>
                    <input type="date" class="form-control" id="fuel_to" name="fuel_to" value="{{ request('fuel_to') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="fleet-btn">Filter</button>
                    <a href="{{ $tabLink('fuel') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="vehicle-fuel-table">
                    <thead><tr><th>Date</th><th>Quantity</th><th>Consumption</th><th>Full tank</th><th>Note</th><th>Receipt</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @forelse($fuelLogs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->date ? \Carbon\Carbon::parse($log->date)->format('d M Y') : '—' }}</td>
                            <td>{{ $log->qty !== null ? $log->qty . ' L' : '—' }}</td>
                            <td>{{ $log->consumption !== null ? $log->consumption . ' km/L' : '—' }}</td>
                            <td>{{ $log->complete ? 'Yes' : 'No' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($log->note ?: '—', 50) }}</td>
                            <td>
                                @if($log->image)
                                <a href="{{ asset('uploads/' . $log->image) }}" target="_blank" rel="noopener" class="badge bg-light text-dark border text-decoration-none">Open</a>
                                @else — @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('fuel.edit', $log->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View / edit"><i class="bi bi-eye"></i></a>
                                <a href="{{ $pdfLink('fuel', $log->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                <a href="{{ $printLink('fuel', $log->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                                @can('Vehicles edit')
                                <form method="POST" action="{{ route('fleet.records.archive', ['type' => 'fuel', 'id' => $log->id]) }}" class="d-inline" data-fleet-confirm="Archive this fuel log?">
                                    @csrf
                                    <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Archive"><i class="bi bi-archive"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-fuel-pump d-block mb-2" style="font-size:24px"></i>No fuel logs for this truck.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>

    <div class="col-lg-5">
        <x-fleet.panel title="Latest telematics position" icon="bi-broadcast-pin">
            @if($lastPing)
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Recorded</dt><dd class="col-7">{{ $lastPing->recorded_at?->format('d M Y H:i:s') ?: '—' }}</dd>
                    <dt class="col-5 text-muted">Position</dt><dd class="col-7">{{ $lastPing->latitude !== null ? $lastPing->latitude . ', ' . $lastPing->longitude : '—' }}</dd>
                    <dt class="col-5 text-muted">Speed</dt><dd class="col-7">{{ $lastPing->speed_kph !== null ? $lastPing->speed_kph . ' km/h' : '—' }}</dd>
                    <dt class="col-5 text-muted">Odometer</dt><dd class="col-7">{{ $lastPing->odometer !== null ? number_format((float) $lastPing->odometer, 1) . ' km' : '—' }}</dd>
                    <dt class="col-5 text-muted">Fuel level</dt><dd class="col-7">{{ $lastPing->fuel_level !== null ? $lastPing->fuel_level . '%' : '—' }}</dd>
                    <dt class="col-5 text-muted">Ignition</dt><dd class="col-7">{{ $lastPing->ignition === null ? '—' : ($lastPing->ignition ? 'On' : 'Off') }}</dd>
                </dl>
            @else
                <x-fleet.empty icon="bi-broadcast">No telematics pings received yet.</x-fleet.empty>
            @endif
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Recent pings" icon="bi-geo-alt">
            <form method="GET" action="{{ route('vehicles.show', ['id' => $vehicle->id]) }}" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="fuel">
                <div class="col-5">
                    <label class="form-label" for="tel_from">From</label>
                    <input type="date" class="form-control" id="tel_from" name="tel_from" value="{{ request('tel_from') }}">
                </div>
                <div class="col-5">
                    <label class="form-label" for="tel_to">To</label>
                    <input type="date" class="form-control" id="tel_to" name="tel_to" value="{{ request('tel_to') }}">
                </div>
                <div class="col-2"><button class="fleet-btn w-100"><i class="bi bi-search"></i></button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-sm table-bordered fleet-table align-middle mb-0" id="vehicle-telematics-table">
                    <thead><tr><th>Recorded</th><th>Position</th><th>Speed</th><th>Odometer</th><th>Fuel</th><th>Ignition</th><th>Source</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @forelse($telematics as $ping)
                        <tr>
                            <td class="text-nowrap">{{ $ping->recorded_at?->format('d M H:i') ?: '—' }}</td>
                            <td class="text-nowrap">{{ $ping->latitude !== null ? $ping->latitude . ', ' . $ping->longitude : '—' }}</td>
                            <td>{{ $ping->speed_kph !== null ? $ping->speed_kph . ' km/h' : '—' }}</td>
                            <td>{{ $ping->odometer !== null ? number_format((float) $ping->odometer, 0) . ' km' : '—' }}</td>
                            <td>{{ $ping->fuel_level !== null ? $ping->fuel_level . '%' : '—' }}</td>
                            <td>{{ $ping->ignition === null ? '—' : ($ping->ignition ? 'On' : 'Off') }}</td>
                            <td>{{ $ping->source ?: '—' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ $printLink('telematics', $ping->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ $pdfLink('telematics', $ping->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                <a href="{{ $printLink('telematics', $ping->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No pings in this period.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>
</div>
@endif

@if($tab === 'trips')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-signpost-2 me-2"></i>Trip history</h5>
        <div class="text-muted small">{{ $kpis['completed_trips'] }} completed · {{ number_format($kpis['distance'], 0) }} km</div>
    </div>

    <form method="GET" action="{{ route('vehicles.show', ['id' => $vehicle->id]) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="trips">
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
            <a href="{{ $tabLink('trips') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="vehicle-trips-table">
            <thead>
                <tr><th>Trip</th><th>Route</th><th>Driver</th><th>Customer</th><th>Dates</th><th>Distance</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($trips as $trip)
                <tr>
                    <td class="fw-semibold text-nowrap"><a href="{{ route('logistics.trips.show', $trip->id) }}">{{ $trip->trip_number }}</a></td>
                    <td>{{ $trip->route_label }}</td>
                    <td>{{ optional($trip->driver)->name ?: '—' }}</td>
                    <td>{{ optional($trip->customer)->name ?: '—' }}</td>
                    <td class="text-nowrap small">
                        {{ $trip->planned_start_at?->format('d M Y') ?: '—' }}
                        @if($trip->actual_end_at) <div class="text-muted">ended {{ $trip->actual_end_at->format('d M Y') }}</div>@endif
                    </td>
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
@endif
@endsection

@section('script')
<script>
    $(function () {
        if (!$.fn.DataTable) return;

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

        init('vehicle-compliance-table', 6, 'documents');
        init('vehicle-maintenance-table', 8, 'maintenance records');
        init('vehicle-fuel-table', 6, 'fuel logs');
        init('vehicle-telematics-table', 7, 'telematics pings');
        init('vehicle-trips-table', 7, 'trips');

        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form[data-fleet-confirm]');
            if (form && !window.confirm(form.getAttribute('data-fleet-confirm'))) {
                event.preventDefault();
            }
        });
    });
</script>
@endsection
