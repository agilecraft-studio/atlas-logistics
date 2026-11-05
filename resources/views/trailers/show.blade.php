@extends('layouts.attex')

@section('page_title', 'Trailer · ' . $trailer->registration_number)
@section('fleet_subtitle', 'Specifications, compliance, maintenance, pairings and utilisation')

@section('content')
@php
    $hub = app(\App\Services\FleetHubService::class);
    $money = fn ($amount, $code = null) => $amount === null || $amount === ''
        ? '—'
        : \App\Support\Money::format(\App\Support\Money::toMinor($amount), $code ?: \App\Services\CurrencyService::base());
    $tabLink = fn ($key) => route('logistics.trailers.show', ['id' => $trailer->id, 'tab' => $key]);
    $printLink = fn ($type, $id) => route('fleet.records.print', ['type' => $type, 'id' => $id]);
    $pdfLink = fn ($type, $id) => route('fleet.records.pdf', ['type' => $type, 'id' => $id]);
    $statusLabel = ucfirst(str_replace('_', ' ', $trailer->status ?: 'available'));
    $today = now()->toDateString();
@endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
<x-fleet.alert type="danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-fleet.alert>
@endif

<x-fleet.panel>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded d-flex align-items-center justify-content-center text-white" style="width:58px;height:58px;background:var(--atlas-navy)">
                <i class="bi bi-train-front" style="font-size:26px"></i>
            </div>
            <div>
                <h4 class="mb-1">
                    {{ $trailer->registration_number }}
                    <x-fleet.status-pill class="ms-2" :status="str_replace('_', ' ', $trailer->status ?: 'available')">{{ $statusLabel }}</x-fleet.status-pill>
                </h4>
                <div class="text-muted small">
                    {{ trim(($trailer->trailer_type ?? '') . ' ' . ($trailer->make ?? '') . ' ' . ($trailer->model ?? '')) ?: 'Type and make not recorded' }}
                    @if($trailer->chassis_number) · VIN {{ $trailer->chassis_number }} @endif
                    @if($trailer->depot) · {{ $trailer->depot }} @endif
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('Trailers edit')
            <a href="{{ route('logistics.trailers.edit', ['id' => $trailer->id]) }}" class="fleet-btn"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            <a href="{{ $printLink('trailer', $trailer->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer me-1"></i>Print</a>
            <a href="{{ $pdfLink('trailer', $trailer->id) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="{{ route('logistics.trailers.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Trips" :value="$kpis['trips']" icon="bi-signpost-2" color="bg-blue" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Distance" :value="number_format($kpis['distance'], 0) . ' km'" icon="bi-speedometer2" color="bg-teal" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Utilisation ({{ $utilization['period_days'] }}d)" :value="$kpis['utilization'] . '%'" icon="bi-activity" color="bg-amber" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Compliance alerts" :value="$kpis['compliance_alerts']" icon="bi-shield-exclamation" color="bg-red" /></div>
    </div>
</x-fleet.panel>

<ul class="nav nav-tabs nav-bordered mb-3 mt-3" role="tablist">
    <li class="nav-item"><a class="nav-link {{ $tab === 'overview' ? 'active' : '' }}" href="{{ $tabLink('overview') }}"><i class="bi bi-truck me-1"></i>Overview</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'compliance' ? 'active' : '' }}" href="{{ $tabLink('compliance') }}"><i class="bi bi-shield-check me-1"></i>Compliance <span class="badge bg-light text-dark border ms-1">{{ $docCounts['total'] }}</span></a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'maintenance' ? 'active' : '' }}" href="{{ $tabLink('maintenance') }}"><i class="bi bi-tools me-1"></i>Maintenance &amp; repairs</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'assignments' ? 'active' : '' }}" href="{{ $tabLink('assignments') }}"><i class="bi bi-link-45deg me-1"></i>Assignment history</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'utilization' ? 'active' : '' }}" href="{{ $tabLink('utilization') }}"><i class="bi bi-graph-up me-1"></i>Utilisation</a></li>
</ul>

@if($tab === 'overview')
<div class="row g-3">
    <div class="col-lg-7">
        <x-fleet.panel title="Specifications" icon="bi-clipboard-data">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Trailer ID</dt><dd class="col-7">{{ $trailer->registration_number }}</dd>
                <dt class="col-5 text-muted">Type</dt><dd class="col-7">{{ $trailer->trailer_type ?: '—' }}</dd>
                <dt class="col-5 text-muted">Make</dt><dd class="col-7">{{ $trailer->make ?: '—' }}</dd>
                <dt class="col-5 text-muted">Model</dt><dd class="col-7">{{ $trailer->model ?: '—' }}</dd>
                <dt class="col-5 text-muted">Year</dt><dd class="col-7">{{ $trailer->year ?: '—' }}</dd>
                <dt class="col-5 text-muted">VIN / chassis</dt><dd class="col-7">{{ $trailer->chassis_number ?: '—' }}</dd>
                <dt class="col-5 text-muted">Axle configuration</dt><dd class="col-7">{{ $trailer->axle_configuration ?: '—' }}</dd>
                <dt class="col-5 text-muted">Payload capacity</dt><dd class="col-7">{{ $trailer->payload_capacity_kg ? number_format($trailer->payload_capacity_kg) . ' kg' : '—' }}</dd>
                <dt class="col-5 text-muted">Tare</dt><dd class="col-7">{{ $trailer->tare_kg ? number_format($trailer->tare_kg) . ' kg' : '—' }}</dd>
                <dt class="col-5 text-muted">GVM</dt><dd class="col-7">{{ $trailer->gvm_kg ? number_format($trailer->gvm_kg) . ' kg' : '—' }}</dd>
                <dt class="col-5 text-muted">First registration</dt><dd class="col-7">{{ $trailer->first_registration?->format('d M Y') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Title holder</dt><dd class="col-7">{{ $trailer->title_holder ?: '—' }}</dd>
                <dt class="col-5 text-muted">Vehicle register no.</dt><dd class="col-7">{{ $trailer->register_number ?: '—' }}</dd>
                <dt class="col-5 text-muted">Depot / location</dt><dd class="col-7">{{ $trailer->depot ?: '—' }}</dd>
                <dt class="col-5 text-muted">Status</dt>
                <dd class="col-7"><x-fleet.status-pill :status="str_replace('_', ' ', $trailer->status ?: 'available')">{{ $statusLabel }}</x-fleet.status-pill></dd>
            </dl>
            @if($trailer->notes)<div class="text-muted small mt-2">{{ $trailer->notes }}</div>@endif
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Latest maintenance" icon="bi-tools">
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
                        <tr><td class="text-muted">No maintenance recorded for this trailer.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>

    <div class="col-lg-5">
        <x-fleet.panel title="Current deployment" icon="bi-broadcast">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Attached truck</dt>
                <dd class="col-7">
                    @if($currentAssignment?->vehicle)
                        <a href="{{ route('vehicles.show', ['id' => $currentAssignment->vehicle->id]) }}">{{ $currentAssignment->vehicle->license_plate }}</a>
                    @else — @endif
                </dd>
                <dt class="col-5 text-muted">Paired driver</dt>
                <dd class="col-7">{{ $currentAssignment?->driver?->name ?: '—' }}</dd>
                <dt class="col-5 text-muted">Paired since</dt>
                <dd class="col-7">{{ $currentAssignment?->assigned_at?->format('d M Y') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Odometer out</dt>
                <dd class="col-7">{{ $currentAssignment?->odometer_out !== null ? number_format((float) $currentAssignment->odometer_out, 0) . ' km' : '—' }}</dd>
                <dt class="col-5 text-muted">Open pairings</dt>
                <dd class="col-7">{{ $currentAssignment ? '1' : '0' }}</dd>
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

        <x-fleet.panel class="mt-3" title="Latest trips" icon="bi-signpost-2">
            @forelse($trips->take(5) as $trip)
                <div class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom">
                    <div>
                        <a href="{{ route('logistics.trips.show', $trip->id) }}" class="fw-semibold">{{ $trip->trip_number }}</a>
                        <div class="text-muted small">{{ $trip->route_label }} {{ $trip->distance_km ? '· ' . number_format((float) $trip->distance_km, 0) . ' km' : '' }}</div>
                    </div>
                    <x-fleet.status-pill :status="str_replace('_', ' ', $trip->status)">{{ $trip->status_label }}</x-fleet.status-pill>
                </div>
            @empty
                <x-fleet.empty icon="bi-signpost-2">No trips recorded for this trailer.</x-fleet.empty>
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
        <a href="{{ route('logistics.documents.create', ['owner' => 'trailer:' . $trailer->id]) }}" class="fleet-btn"><i class="bi bi-plus-lg me-1"></i>Add document</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('logistics.trailers.show', ['id' => $trailer->id]) }}" class="row g-2 align-items-end mb-3">
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
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="trailer-compliance-table">
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
        @can('WorkOrders add')
        <a href="{{ route('work_order.create') }}" class="fleet-btn"><i class="bi bi-plus-lg me-1"></i>Log maintenance</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('logistics.trailers.show', ['id' => $trailer->id]) }}" class="row g-2 align-items-end mb-3">
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
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="trailer-maintenance-table">
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
                        @can('Trailers edit')
                        <form method="POST" action="{{ route('fleet.records.archive', ['type' => 'work_order', 'id' => $order->id]) }}" class="d-inline" data-fleet-confirm="Archive this maintenance record?">
                            @csrf
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Archive"><i class="bi bi-archive"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-tools d-block mb-2" style="font-size:24px"></i>No maintenance records for this trailer.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>

<x-fleet.panel class="mt-3">
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-circle-square me-2"></i>Tyres, brakes &amp; fitted parts</h5>
        @can('FleetRegister edit')
        <a href="{{ route('logistics.components.create', ['owner' => 'trailer:' . $trailer->id]) }}" class="fleet-btn"><i class="bi bi-plus-lg me-1"></i>Add component</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="trailer-components-table">
            <thead><tr><th>Component</th><th>Position</th><th>Serial</th><th>Brand</th><th>Status</th><th>Fitted</th><th>Removed</th><th>Cost</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($components as $component)
                <tr>
                    <td class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $component->component_type)) }}</td>
                    <td>{{ $component->position ?: '—' }}</td>
                    <td>{{ $component->serial_number ?: '—' }}</td>
                    <td>{{ $component->brand ?: '—' }}</td>
                    <td><x-fleet.status-pill :status="$component->status">{{ ucfirst($component->status) }}</x-fleet.status-pill></td>
                    <td class="text-nowrap small">
                        {{ $component->fitted_date?->format('d M Y') ?: '—' }}
                        @if($component->fitted_odometer !== null)<div class="text-muted">{{ number_format((float) $component->fitted_odometer, 0) }} km</div>@endif
                    </td>
                    <td class="text-nowrap">{{ $component->removed_date?->format('d M Y') ?: '—' }}</td>
                    <td class="text-nowrap">{{ $money($component->cost) }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ $printLink('component', $component->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        @can('FleetRegister edit')
                        <a href="{{ route('logistics.components.edit', ['id' => $component->id]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                        @endcan
                        <a href="{{ $pdfLink('component', $component->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="{{ $printLink('component', $component->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No tyres or parts recorded for this trailer.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>
@endif

@if($tab === 'assignments')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Assignment history</h5>
        @can('Trailers edit')
        <button type="button" class="fleet-btn" data-bs-toggle="modal" data-bs-target="#assignmentCreateModal"><i class="bi bi-plus-lg me-1"></i>Assign trailer</button>
        @endcan
    </div>

    @if($currentAssignment)
    <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <i class="bi bi-truck me-1"></i>
            Currently paired with
            <strong>{{ $currentAssignment->vehicle?->license_plate ?: 'no truck' }}</strong>
            @if($currentAssignment->driver) · driver {{ $currentAssignment->driver->name }} @endif
            @if($currentAssignment->assigned_at) · since {{ $currentAssignment->assigned_at->format('d M Y') }} @endif
        </div>
        @can('Trailers edit')
        <button type="button" class="fleet-btn fleet-btn-sm fleet-btn-ghost" data-bs-toggle="modal" data-bs-target="#assignmentReleaseModal"
                data-url="{{ route('logistics.trailers.assignments.release', ['id' => $trailer->id, 'assignment' => $currentAssignment->id]) }}"
                data-f-odometer_in="{{ $currentAssignment->odometer_in }}"
                data-f-notes="">Release pairing</button>
        @endcan
    </div>
    @endif

    <form method="GET" action="{{ route('logistics.trailers.show', ['id' => $trailer->id]) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="assignments">
        <div class="col-md-3">
            <label class="form-label" for="asg_status">Status</label>
            <select class="form-select" id="asg_status" name="asg_status">
                <option value="">All pairings</option>
                <option value="current" @selected(request('asg_status') === 'current')>Currently attached</option>
                <option value="released" @selected(request('asg_status') === 'released')>Released</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="asg_from">Assigned from</label>
            <input type="date" class="form-control" id="asg_from" name="asg_from" value="{{ request('asg_from') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="asg_to">Assigned to</label>
            <input type="date" class="form-control" id="asg_to" name="asg_to" value="{{ request('asg_to') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="fleet-btn">Filter</button>
            <a href="{{ $tabLink('assignments') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="trailer-assignments-table">
            <thead><tr><th>Truck</th><th>Driver</th><th>Assigned</th><th>Released</th><th>Odometer out / in</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td class="fw-semibold">
                        @if($assignment->vehicle)
                        <a href="{{ route('vehicles.show', ['id' => $assignment->vehicle->id]) }}">{{ $assignment->vehicle->license_plate }}</a>
                        @else — @endif
                    </td>
                    <td>{{ $assignment->driver?->name ?: '—' }}</td>
                    <td class="text-nowrap">{{ $assignment->assigned_at?->format('d M Y') ?: '—' }}</td>
                    <td class="text-nowrap">{{ $assignment->released_at?->format('d M Y') ?: 'Still attached' }}</td>
                    <td class="text-nowrap small">
                        {{ $assignment->odometer_out !== null ? number_format((float) $assignment->odometer_out, 0) . ' km' : '—' }}
                        /
                        {{ $assignment->odometer_in !== null ? number_format((float) $assignment->odometer_in, 0) . ' km' : '—' }}
                    </td>
                    <td>
                        <x-fleet.status-pill :status="$assignment->is_current ? 'current' : 'released'">{{ $assignment->is_current ? 'Current' : 'Released' }}</x-fleet.status-pill>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ $printLink('assignment', $assignment->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ $pdfLink('assignment', $assignment->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="{{ $printLink('assignment', $assignment->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                        @can('Trailers edit')
                        @if($assignment->is_current)
                        <button type="button" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Release pairing"
                                data-bs-toggle="modal" data-bs-target="#assignmentReleaseModal"
                                data-url="{{ route('logistics.trailers.assignments.release', ['id' => $trailer->id, 'assignment' => $assignment->id]) }}"
                                data-f-odometer_in="{{ $assignment->odometer_in }}"
                                data-f-notes=""><i class="bi bi-box-arrow-right"></i></button>
                        @else
                        <form method="POST" action="{{ route('fleet.records.archive', ['type' => 'assignment', 'id' => $assignment->id]) }}" class="d-inline" data-fleet-confirm="Archive this pairing? Released pairings remain on the trailer history.">
                            @csrf
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Archive"><i class="bi bi-archive"></i></button>
                        </form>
                        @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-link-45deg d-block mb-2" style="font-size:24px"></i>No pairings match this filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>

@can('Trailers edit')
<x-fleet.modal id="assignmentCreateModal" title="Assign trailer">
    <form method="POST" action="{{ route('logistics.trailers.assignments.store', ['id' => $trailer->id]) }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label" for="assignment_vehicle">Truck</label>
                <select class="form-select" id="assignment_vehicle" name="vehicle_id">
                    <option value="">No truck</option>
                    @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" @selected((string) old('vehicle_id') === (string) $vehicle->id)>{{ $vehicle->license_plate }} · {{ trim(($vehicle->make_name ?? '') . ' ' . ($vehicle->model_name ?? '')) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="assignment_driver">Driver</label>
                <select class="form-select" id="assignment_driver" name="driver_id">
                    <option value="">No driver</option>
                    @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" @selected((string) old('driver_id') === (string) $driver->id)>{{ $driver->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="assignment_assigned_at">Assigned on</label>
                <input type="date" class="form-control" id="assignment_assigned_at" name="assigned_at" value="{{ old('assigned_at', $today) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="assignment_odometer_out">Odometer out</label>
                <input type="number" step="0.1" min="0" class="form-control" id="assignment_odometer_out" name="odometer_out" value="{{ old('odometer_out') }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="assignment_notes">Notes</label>
                <textarea class="form-control" id="assignment_notes" name="notes" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
            </div>
        </div>
        <p class="text-muted small mt-2 mb-0">Assigning releases the trailer's open pairing and any pairing the chosen truck already has.</p>
        <div class="d-flex gap-2 mt-3">
            <button class="fleet-btn">Save pairing</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
</x-fleet.modal>

<x-fleet.modal id="assignmentReleaseModal" title="Release pairing">
    <form method="POST" action="" id="assignmentReleaseForm">
        @csrf
        @method('PUT')
        <div class="row g-2">
            <div class="col-md-5">
                <label class="form-label" for="release_released_at">Released on</label>
                <input type="date" class="form-control" id="release_released_at" name="released_at" value="{{ $today }}">
            </div>
            <div class="col-md-5">
                <label class="form-label" for="release_odometer_in">Odometer in</label>
                <input type="number" step="0.1" min="0" class="form-control" id="release_odometer_in" name="odometer_in">
            </div>
            <div class="col-12">
                <label class="form-label" for="release_notes">Notes</label>
                <textarea class="form-control" id="release_notes" name="notes" rows="2" maxlength="2000"></textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button class="fleet-btn">Release pairing</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
</x-fleet.modal>
@endcan
@endif

@if($tab === 'utilization')
<div class="row g-3">
    <div class="col-lg-5">
        <x-fleet.panel title="Utilisation (last {{ $utilization['period_days'] }} days)" icon="bi-activity">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="display-6 fw-semibold">{{ $utilization['percent'] }}%</span>
                <span class="text-muted small">{{ $utilization['active_days'] }} active · {{ $utilization['idle_days'] }} idle days</span>
            </div>
            <div class="progress" style="height:10px">
                <div class="progress-bar bg-teal" role="progressbar" style="width: {{ min(100, max(0, $utilization['percent'])) }}%" aria-valuenow="{{ $utilization['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <p class="text-muted small mt-3 mb-0">Active time comes from trailer pairings; when no pairings exist the actual trip dates are used.</p>
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Cargo carried" icon="bi-box-seam">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <thead><tr><th>Cargo type</th><th class="text-end">Trips</th><th class="text-end">Weight</th></tr></thead>
                    <tbody>
                    @forelse($cargoSummary as $cargo => $summary)
                        <tr>
                            <td>{{ $cargo }}</td>
                            <td class="text-end">{{ $summary['trips'] }}</td>
                            <td class="text-end text-nowrap">{{ $summary['weight'] > 0 ? number_format($summary['weight'], 0) . ' kg' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No cargo descriptions recorded yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>

    <div class="col-lg-7">
        <x-fleet.panel title="Trips &amp; utilisation detail" icon="bi-signpost-2">
            <form method="GET" action="{{ route('logistics.trailers.show', ['id' => $trailer->id]) }}" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="utilization">
                <div class="col-md-4">
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
                <div class="col-md-2 d-flex gap-2">
                    <button class="fleet-btn">Filter</button>
                    <a href="{{ $tabLink('utilization') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="trailer-trips-table">
                    <thead><tr><th>Trip</th><th>Route</th><th>Truck</th><th>Cargo</th><th>Dates</th><th>Distance</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @forelse($trips as $trip)
                        <tr>
                            <td class="fw-semibold text-nowrap"><a href="{{ route('logistics.trips.show', $trip->id) }}">{{ $trip->trip_number }}</a></td>
                            <td>{{ $trip->route_label }}</td>
                            <td>{{ optional($trip->vehicle)->license_plate ?: '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($trip->cargo_description ?: '—', 40) }}</td>
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
</div>
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

            init('trailer-compliance-table', 6, 'documents');
            init('trailer-maintenance-table', 8, 'maintenance records');
            init('trailer-components-table', 8, 'components');
            init('trailer-assignments-table', 6, 'pairings');
            init('trailer-trips-table', 7, 'trips');
        }

        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form[data-fleet-confirm]');
            if (form && !window.confirm(form.getAttribute('data-fleet-confirm'))) {
                event.preventDefault();
            }
        });

        var releaseModal = document.getElementById('assignmentReleaseModal');
        if (releaseModal) {
            releaseModal.addEventListener('show.bs.modal', function (event) {
                var trigger = event.relatedTarget;
                var form = document.getElementById('assignmentReleaseForm');
                if (!trigger || !form) return;
                form.action = trigger.getAttribute('data-url') || form.action;
                var odometer = form.querySelector('[name="odometer_in"]');
                var notes = form.querySelector('[name="notes"]');
                if (odometer) odometer.value = trigger.getAttribute('data-f-odometer_in') || '';
                if (notes) notes.value = trigger.getAttribute('data-f-notes') || '';
            });
        }
    });
</script>
@endsection
