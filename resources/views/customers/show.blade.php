@extends('layouts.attex')

@section('page_title', __('fleet.customer') . ' · ' . $customer->name)
@section('fleet_subtitle', 'Shipments, invoices and transport documents')

@section('content')
@php
    $money = fn ($amount, $code = null, $symbol = null) => \App\Support\Money::format(\App\Support\Money::toMinor($amount), $code ?: \App\Services\CurrencyService::base(), $symbol);
    $initial = strtoupper(substr(trim($customer->name), 0, 1));
    $tabLink = fn ($key) => route('customers.show', ['customer' => $customer->id, 'tab' => $key]);
    $canDocs = auth()->user()->can('Customer documents add');
@endphp

<x-fleet.panel>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                 style="width:52px;height:52px;background:var(--atlas-navy)">{{ $initial }}</div>
            <div>
                <h4 class="mb-1">{{ $customer->name }}</h4>
                <div class="text-muted small">
                    {{ $customer->email ?: 'No email' }}
                    @if($customer->mobno) · {{ $customer->mobno }} @endif
                    @if($customer->address) · {{ $customer->address }} @endif
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('Invoices add')
            <div class="dropdown">
                <button class="fleet-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-receipt me-1"></i>New invoice</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($invoiceTypes as $key => $config)
                    <li><a class="dropdown-item" href="{{ route('logistics.invoices.create', ['type' => $key, 'client_id' => $customer->id]) }}"><i class="bi {{ $config['icon'] }} me-2"></i>{{ $config['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endcan
            @if($canDocs)
            <div class="dropdown">
                <button class="fleet-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-file-earmark-plus me-1"></i>New document</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($documentTypes as $key => $config)
                    <li><a class="dropdown-item" href="{{ route('customers.documents.create', ['customer' => $customer->id, 'type' => $key]) }}"><i class="bi {{ $config['icon'] }} me-2"></i>{{ $config['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif
            <a href="{{ route('customers.edit', $customer->id) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="{{ route('customers.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <x-fleet.kpi label="Total invoiced" :value="$money($kpis['total_invoiced'])" icon="bi-cash-stack" color="bg-teal" />
        </div>
        <div class="col-6 col-lg-3">
            <x-fleet.kpi label="Outstanding" :value="$money($kpis['outstanding'])" icon="bi-exclamation-octagon" color="bg-red" />
        </div>
        <div class="col-6 col-lg-3">
            <x-fleet.kpi label="Shipments" :value="$kpis['shipments']" icon="bi-truck" color="bg-blue" />
        </div>
        <div class="col-6 col-lg-3">
            <x-fleet.kpi label="Documents on file" :value="$kpis['documents']" icon="bi-folder2-open" color="bg-amber" />
        </div>
    </div>
</x-fleet.panel>

<ul class="nav nav-tabs nav-bordered mb-3 mt-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'overview' ? 'active' : '' }}" href="{{ $tabLink('overview') }}"><i class="bi bi-person-vcard me-1"></i>Overview</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'shipments' ? 'active' : '' }}" href="{{ $tabLink('shipments') }}"><i class="bi bi-truck me-1"></i>Shipments <span class="badge bg-light text-dark border ms-1">{{ $kpis['shipments'] }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'invoices' ? 'active' : '' }}" href="{{ $tabLink('invoices') }}"><i class="bi bi-receipt me-1"></i>Invoices <span class="badge bg-light text-dark border ms-1">{{ $kpis['invoices'] }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'documents' ? 'active' : '' }}" href="{{ $tabLink('documents') }}"><i class="bi bi-folder2-open me-1"></i>Documents</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'pods' ? 'active' : '' }}" href="{{ $tabLink('pods') }}"><i class="bi bi-patch-check me-1"></i>PoDs <span class="badge bg-light text-dark border ms-1">{{ $kpis['pods'] }}</span></a>
    </li>
</ul>

@if($tab === 'overview')
<div class="row g-3">
    <div class="col-lg-4">
        <x-fleet.panel title="Customer details" icon="bi-person-lines-fill">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Name</dt><dd class="col-7">{{ $customer->name }}</dd>
                <dt class="col-5 text-muted">Email</dt><dd class="col-7">{{ $customer->email ?: '—' }}</dd>
                <dt class="col-5 text-muted">Phone</dt><dd class="col-7">{{ $customer->mobno ?: '—' }}</dd>
                <dt class="col-5 text-muted">Address</dt><dd class="col-7">{{ $customer->address ?: '—' }}</dd>
                <dt class="col-5 text-muted">Gender</dt><dd class="col-7">{{ $customer->gender == 1 ? 'Male' : 'Female' }}</dd>
            </dl>
        </x-fleet.panel>
    </div>
    <div class="col-lg-8">
        <x-fleet.panel title="Latest shipments" icon="bi-truck">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                    @forelse($shipments->take(5) as $trip)
                        <tr>
                            <td class="fw-semibold text-nowrap">{{ $trip->trip_number }}</td>
                            <td>{{ $trip->route_label }}</td>
                            <td class="text-muted">{{ optional($trip->driver)->name ?: '—' }}</td>
                            <td><x-fleet.status-pill :status="str_replace('_', ' ', $trip->status)">{{ $trip->status_label }}</x-fleet.status-pill></td>
                            <td class="text-end"><a href="{{ route('logistics.trips.show', $trip->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost">View</a></td>
                        </tr>
                    @empty
                        <tr><td class="text-muted">No shipments for this customer yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>

        <x-fleet.panel class="mt-3" title="Latest invoices" icon="bi-receipt">
            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                    @forelse($invoices->take(5) as $invoice)
                        <tr>
                            <td class="fw-semibold text-nowrap"><a href="{{ route('logistics.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                            <td class="text-muted">{{ $invoice->document_type_label }}</td>
                            <td><x-fleet.status-pill :status="str_replace('_', ' ', $invoice->status)">{{ $invoice->status_label }}</x-fleet.status-pill></td>
                            <td class="text-end text-nowrap">{{ $money($invoice->total, $invoice->currency_code, $invoice->currency_symbol) }}</td>
                            <td class="text-end text-nowrap text-danger fw-semibold">{{ $money($invoice->balance_due, $invoice->currency_code, $invoice->currency_symbol) }}</td>
                        </tr>
                    @empty
                        <tr><td class="text-muted">No invoices found for this customer.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-fleet.panel>
    </div>
</div>
@endif

@if($tab === 'shipments')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Shipments</h5>
        <div class="text-muted small">{{ $shipments->count() }} shipment(s)</div>
    </div>

    <form method="GET" action="{{ route('customers.show', $customer->id) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="shipments">
        <div class="col-md-3">
            <label class="form-label" for="trip_status">Status</label>
            <select class="form-select" id="trip_status" name="trip_status">
                <option value="">All statuses</option>
                @foreach($tripStatuses as $key => $label)
                <option value="{{ $key }}" @selected(($tripFilters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="trip_from">Departure from</label>
            <input type="date" class="form-control" id="trip_from" name="trip_from" value="{{ $tripFilters['from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="trip_to">Departure to</label>
            <input type="date" class="form-control" id="trip_to" name="trip_to" value="{{ $tripFilters['to'] ?? '' }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="fleet-btn">Filter</button>
            <a href="{{ $tabLink('shipments') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="customer-shipments-table">
            <thead>
                <tr>
                    <th>Shipment</th>
                    <th>Route</th>
                    <th>Driver</th>
                    <th>Vehicle</th>
                    <th>Cargo</th>
                    <th>Departure</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($shipments as $trip)
                <tr>
                    <td class="fw-semibold"><a href="{{ route('logistics.trips.show', $trip->id) }}">{{ $trip->trip_number }}</a></td>
                    <td>{{ $trip->route_label }}</td>
                    <td>{{ optional($trip->driver)->name ?: '—' }}</td>
                    <td>{{ optional($trip->vehicle)->license_plate ?: '—' }}</td>
                    <td class="text-truncate" style="max-width:180px">{{ $trip->cargo_description ?: '—' }}</td>
                    <td class="text-nowrap">{{ optional($trip->planned_start_at)->format('d M Y') ?: '—' }}</td>
                    <td><x-fleet.status-pill :status="str_replace('_', ' ', $trip->status)">{{ $trip->status_label }}</x-fleet.status-pill></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('logistics.trips.show', $trip->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View shipment"><i class="bi bi-eye"></i></a>
                        @if($canDocs)
                        <div class="dropdown d-inline">
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Create document"><i class="bi bi-file-earmark-plus"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('customers.documents.create', ['customer' => $customer->id, 'type' => 'delivery_note', 'trip_id' => $trip->id]) }}">Delivery note</a></li>
                                <li><a class="dropdown-item" href="{{ route('customers.documents.create', ['customer' => $customer->id, 'type' => 'dispatch_sheet', 'trip_id' => $trip->id]) }}">Dispatch / route sheet</a></li>
                                <li><a class="dropdown-item" href="{{ route('customers.documents.create', ['customer' => $customer->id, 'type' => 'waybill', 'trip_id' => $trip->id]) }}">Waybill / BoL</a></li>
                                <li><a class="dropdown-item" href="{{ route('customers.documents.create', ['customer' => $customer->id, 'type' => 'pod', 'trip_id' => $trip->id]) }}">Proof of delivery</a></li>
                            </ul>
                        </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-truck d-block mb-2" style="font-size:24px"></i>No shipments found for this customer.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>
@endif

@if($tab === 'invoices')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoices</h5>
        <div class="d-flex gap-2">
            @can('Invoices add')
            <div class="dropdown">
                <button class="fleet-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-plus-lg"></i> New</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($invoiceTypes as $key => $config)
                    <li><a class="dropdown-item" href="{{ route('logistics.invoices.create', ['type' => $key, 'client_id' => $customer->id]) }}"><i class="bi {{ $config['icon'] }} me-2"></i>{{ $config['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endcan
        </div>
    </div>

    <form method="GET" action="{{ route('customers.show', $customer->id) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="invoices">
        <div class="col-md-2">
            <label class="form-label" for="invoice_type">Type</label>
            <select class="form-select" id="invoice_type" name="invoice_type">
                <option value="">All types</option>
                @foreach($invoiceTypes as $key => $config)
                <option value="{{ $key }}" @selected(($invoiceFilters['document_type'] ?? '') === $key)>{{ $config['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="invoice_status">Status</label>
            <select class="form-select" id="invoice_status" name="invoice_status">
                <option value="">All statuses</option>
                @foreach($invoiceStatuses as $key => $label)
                <option value="{{ $key }}" @selected(($invoiceFilters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="invoice_from">Issued from</label>
            <input type="date" class="form-control" id="invoice_from" name="invoice_from" value="{{ $invoiceFilters['from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="invoice_to">Issued to</label>
            <input type="date" class="form-control" id="invoice_to" name="invoice_to" value="{{ $invoiceFilters['to'] ?? '' }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="fleet-btn">Filter</button>
            <a href="{{ $tabLink('invoices') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="customer-invoices-table">
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Issued</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Balance</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td class="fw-semibold"><a href="{{ route('logistics.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                    <td><span class="badge bg-light text-dark border"><i class="bi {{ $invoice->document_icon }} me-1"></i>{{ $invoice->document_type_label }}</span></td>
                    <td class="text-nowrap">{{ optional($invoice->issue_date)->format('d M Y') ?: '—' }}</td>
                    <td class="text-nowrap">{{ optional($invoice->due_date)->format('d M Y') ?: '—' }}</td>
                    <td><x-fleet.status-pill :status="str_replace('_', ' ', $invoice->status)">{{ $invoice->status_label }}</x-fleet.status-pill></td>
                    <td class="text-end text-nowrap">{{ $money($invoice->total, $invoice->currency_code, $invoice->currency_symbol) }}</td>
                    <td class="text-end text-nowrap {{ \App\Support\Money::toMinor($invoice->balance_due) > 0 ? 'text-danger fw-semibold' : '' }}">{{ $money($invoice->balance_due, $invoice->currency_code, $invoice->currency_symbol) }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('logistics.invoices.show', $invoice) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        @can('Invoices edit')
                        @if($invoice->isDraft())
                        <a href="{{ route('logistics.invoices.edit', $invoice) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                        @endif
                        @endcan
                        <a href="{{ route('logistics.invoices.pdf', $invoice) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="{{ route('logistics.invoices.print', $invoice) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                        @if($invoice->client_email)
                        <form method="POST" action="{{ route('logistics.invoices.email', $invoice) }}" class="d-inline" data-fleet-confirm="Email {{ $invoice->invoice_number }} to {{ $invoice->client_email }}?">
                            @csrf
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Email"><i class="bi bi-envelope"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-receipt d-block mb-2" style="font-size:24px"></i>No invoices found for this customer.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>
@endif

@if($tab === 'documents')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Transport documents</h5>
        <div class="d-flex gap-2">
            @if($canDocs)
            <div class="dropdown">
                <button class="fleet-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-plus-lg"></i> New document</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($documentTypes as $key => $config)
                    @continue($key === 'pod')
                    <li><a class="dropdown-item" href="{{ route('customers.documents.create', ['customer' => $customer->id, 'type' => $key]) }}"><i class="bi {{ $config['icon'] }} me-2"></i>{{ $config['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('customers.show', $customer->id) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="documents">
        <div class="col-md-3">
            <label class="form-label" for="doc_type">Document type</label>
            <select class="form-select" id="doc_type" name="doc_type">
                <option value="">All types</option>
                @foreach($documentTypes as $key => $config)
                @continue($key === 'pod')
                <option value="{{ $key }}" @selected(($documentFilters['type'] ?? '') === $key)>{{ $config['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="doc_status">Status</label>
            <select class="form-select" id="doc_status" name="doc_status">
                <option value="">All statuses</option>
                @foreach($documentStatuses as $key => $label)
                <option value="{{ $key }}" @selected(($documentFilters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="doc_from">From</label>
            <input type="date" class="form-control" id="doc_from" name="doc_from" value="{{ $documentFilters['from'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="doc_to">To</label>
            <input type="date" class="form-control" id="doc_to" name="doc_to" value="{{ $documentFilters['to'] ?? '' }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="fleet-btn">Filter</button>
            <a href="{{ $tabLink('documents') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="customer-documents-table">
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Shipment</th>
                    <th>Driver</th>
                    <th>Vehicle</th>
                    <th>Issued</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($documents as $document)
                <tr>
                    <td class="fw-semibold"><a href="{{ route('customers.documents.show', [$customer, $document]) }}">{{ $document->document_number }}</a></td>
                    <td><span class="badge bg-light text-dark border"><i class="bi {{ $document->getTypeConfig()['icon'] ?? 'bi-file-earmark' }} me-1"></i>{{ $document->type_label }}</span></td>
                    <td>@if($document->trip) <a href="{{ route('logistics.trips.show', $document->trip) }}">{{ $document->trip->trip_number }}</a> @else — @endif</td>
                    <td>{{ optional($document->driver)->name ?: $document->getMetaValue('driver_name', '—') }}</td>
                    <td>{{ optional($document->vehicle)->license_plate ?: $document->getMetaValue('vehicle_plate', '—') }}</td>
                    <td class="text-nowrap">{{ optional($document->issue_date)->format('d M Y') ?: '—' }}</td>
                    <td><x-fleet.status-pill :status="str_replace('_', ' ', $document->status)">{{ $document->status_label }}</x-fleet.status-pill></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('customers.documents.show', [$customer, $document]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        @can('Customer documents edit')
                        <a href="{{ route('customers.documents.edit', [$customer, $document]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                        @endcan
                        <a href="{{ route('customers.documents.download', [$customer, $document]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download"><i class="bi bi-download"></i></a>
                        <a href="{{ route('customers.documents.print', [$customer, $document]) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                        @if($customer->email)
                        <form method="POST" action="{{ route('customers.documents.email', [$customer, $document]) }}" class="d-inline" data-fleet-confirm="Email {{ $document->document_number }} to {{ $customer->email }}?">
                            @csrf
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Email"><i class="bi bi-envelope"></i></button>
                        </form>
                        @endif
                        @can('Customer documents delete')
                        <form method="POST" action="{{ route('customers.documents.destroy', [$customer, $document]) }}" class="d-inline" data-fleet-confirm="Delete {{ $document->document_number }}?">
                            @csrf @method('DELETE')
                            <button class="fleet-btn fleet-btn-sm fleet-btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-folder2-open d-block mb-2" style="font-size:24px"></i>No transport documents found for this customer.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>
@endif

@if($tab === 'pods')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-patch-check me-2"></i>Proofs of delivery</h5>
        @if($canDocs)
        <button class="fleet-btn" type="button" data-bs-toggle="collapse" data-bs-target="#podUpload"><i class="bi bi-cloud-arrow-up me-1"></i>Upload PoD</button>
        @endif
    </div>

    @if($canDocs)
    <div class="collapse mb-3" id="podUpload">
        <div class="border rounded p-3 bg-light">
            <form method="POST" action="{{ route('customers.documents.store', $customer) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                <input type="hidden" name="type" value="pod">
                <div class="col-md-4">
                    <label class="form-label" for="pod_trip">Shipment <span class="text-danger">*</span></label>
                    <select class="form-select" id="pod_trip" name="trip_id" required>
                        <option value="">-- choose shipment --</option>
                        @foreach($shipments as $trip)
                        <option value="{{ $trip->id }}">{{ $trip->trip_number }} — {{ $trip->route_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="pod_file">Signed PoD (PDF / image) <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="pod_file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="pod_signed_by">Signed by</label>
                    <input type="text" class="form-control" id="pod_signed_by" name="signed_by" maxlength="150" placeholder="Receiver name">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="pod_issue_date">Delivery date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="pod_issue_date" name="issue_date" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="pod_signed_at">Signed at</label>
                    <input type="datetime-local" class="form-control" id="pod_signed_at" name="signed_at">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="pod_status">Status</label>
                    <select class="form-select" id="pod_status" name="status">
                        <option value="delivered">Delivered</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="fleet-btn w-100"><i class="bi bi-upload me-1"></i>Upload</button>
                </div>
                <div class="col-12">
                    <input type="text" class="form-control" name="notes" maxlength="5000" placeholder="Notes (optional)">
                </div>
            </form>
        </div>
    </div>
    @endif

    <form method="GET" action="{{ route('customers.show', $customer->id) }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="pods">
        <div class="col-md-3">
            <label class="form-label" for="pod_status_filter">Status</label>
            <select class="form-select" id="pod_status_filter" name="pod_status">
                <option value="">All statuses</option>
                @foreach(['pending', 'delivered', 'rejected'] as $status)
                <option value="{{ $status }}" @selected(($podFilters['status'] ?? '') === $status)>{{ $documentStatuses[$status] ?? ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="pod_from">Delivery from</label>
            <input type="date" class="form-control" id="pod_from" name="pod_from" value="{{ $podFilters['from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="pod_to">Delivery to</label>
            <input type="date" class="form-control" id="pod_to" name="pod_to" value="{{ $podFilters['to'] ?? '' }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="fleet-btn">Filter</button>
            <a href="{{ $tabLink('pods') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="customer-pods-table">
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Shipment</th>
                    <th>Signed by</th>
                    <th>Signed at</th>
                    <th>File</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($pods as $document)
                <tr>
                    <td class="fw-semibold"><a href="{{ route('customers.documents.show', [$customer, $document]) }}">{{ $document->document_number }}</a></td>
                    <td>@if($document->trip) <a href="{{ route('logistics.trips.show', $document->trip) }}">{{ $document->trip->trip_number }}</a> @else — @endif</td>
                    <td>{{ $document->signed_by ?: '—' }}</td>
                    <td class="text-nowrap">{{ optional($document->signed_at)->format('d M Y H:i') ?: '—' }}</td>
                    <td>
                        @if($document->has_file)
                        <a href="{{ $document->file_url }}" target="_blank" rel="noopener" class="badge bg-light text-dark border text-decoration-none">
                            <i class="bi {{ $document->is_image ? 'bi-image' : 'bi-file-earmark-pdf' }} me-1"></i>{{ $document->file_name ?: 'Open file' }}
                        </a>
                        @else — @endif
                    </td>
                    <td><x-fleet.status-pill :status="$document->status">{{ $document->status_label }}</x-fleet.status-pill></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('customers.documents.show', [$customer, $document]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        @can('Customer documents edit')
                        <a href="{{ route('customers.documents.edit', [$customer, $document]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                        @endcan
                        <a href="{{ route('customers.documents.download', [$customer, $document]) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download"><i class="bi bi-download"></i></a>
                        <a href="{{ route('customers.documents.print', [$customer, $document]) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                        @if($customer->email)
                        <form method="POST" action="{{ route('customers.documents.email', [$customer, $document]) }}" class="d-inline" data-fleet-confirm="Email {{ $document->document_number }} to {{ $customer->email }}?">
                            @csrf
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Email"><i class="bi bi-envelope"></i></button>
                        </form>
                        @endif
                        @can('Customer documents delete')
                        <form method="POST" action="{{ route('customers.documents.destroy', [$customer, $document]) }}" class="d-inline" data-fleet-confirm="Delete {{ $document->document_number }}?">
                            @csrf @method('DELETE')
                            <button class="fleet-btn fleet-btn-sm fleet-btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-patch-check d-block mb-2" style="font-size:24px"></i>No proof of delivery uploaded for this customer.</td></tr>
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

        init('customer-shipments-table', 7, 'shipments');
        init('customer-invoices-table', 7, 'invoices');
        init('customer-documents-table', 7, 'documents');
        init('customer-pods-table', 6, 'PoDs');
    });
</script>
@endsection
