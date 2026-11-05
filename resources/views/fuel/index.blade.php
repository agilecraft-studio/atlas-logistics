@extends('layouts.attex')

@section('page_title', 'Fuel Management')

@section('fleet_topbar_actions')
    @unless($isDriver)
    @can('Fuel add')
    <button type="button" class="fleet-btn" data-bs-toggle="modal" data-bs-target="#fuelCardModal" data-mode="create"><i class="bi bi-plus-lg me-1"></i>Add fuel card</button>
    @endcan
    @endunless
    @can('Fuel add')
    <a href="{{ route('fuel.create') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-fuel-pump me-1"></i>Log fuel</a>
    @endcan
@endsection

@section('content')
@php
    $currency = \App\Model\AtlasLogistics::get('currency');
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d M Y') : '—';
    $tabLink = fn ($key) => route('fuel.index', ['tab' => $key]);
    $printLink = fn ($id) => route('fleet.records.print', ['type' => 'fuel', 'id' => $id]);
    $pdfLink = fn ($id) => route('fleet.records.pdf', ['type' => 'fuel', 'id' => $id]);
    $filters = $filters ?? [];

    $logLitres = $logs->sum(fn ($log) => (float) $log->qty);
    $logCost = $logs->sum(fn ($log) => (float) $log->qty * (float) $log->cost_per_unit);
    $pricePerLitre = $logLitres > 0 ? $logCost / $logLitres : 0;
    $chartLabels = collect($analytics['vehicles'])->map(fn ($v) => $v->vehicle?->license_plate ?: 'Vehicle #' . $v->vehicle_id)->values();
    $chartValues = collect($analytics['vehicles'])->map(fn ($v) => $v->litres)->values();
@endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
<x-fleet.alert type="danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-fleet.alert>
@endif

<div class="row g-3">
    @if($tab === 'logs')
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Fuel records" :value="$logs->count()" icon="bi-fuel-pump" color="bg-blue" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Litres" :value="number_format($logLitres, 2) . ' L'" icon="bi-droplet-half" color="bg-amber" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Cost" :value="$money($logCost)" icon="bi-cash-stack" color="bg-teal" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Average price / litre" :value="number_format($pricePerLitre, 2)" icon="bi-tag" color="bg-red" /></div>
    @elseif($tab === 'cards')
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Active cards" :value="$cardStats['active']" icon="bi-credit-card-2-front" color="bg-teal" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Card balance" :value="$money($cardStats['balance'])" icon="bi-wallet2" color="bg-blue" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Suspended" :value="$cardStats['suspended']" icon="bi-pause-circle" color="bg-amber" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Expired" :value="$cardStats['expired']" icon="bi-x-circle" color="bg-red" /></div>
    @else
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Litres" :value="number_format($analytics['totals']['litres'], 2) . ' L'" icon="bi-droplet-half" color="bg-amber" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Cost" :value="$money($analytics['totals']['cost'])" icon="bi-cash-stack" color="bg-teal" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Distance" :value="number_format($analytics['totals']['distance'], 0) . ' km'" icon="bi-speedometer2" color="bg-blue" /></div>
        <div class="col-6 col-lg-3"><x-fleet.kpi label="Anomalies" :value="count($analytics['anomalies'])" icon="bi-exclamation-triangle" color="bg-red" /></div>
    @endif
</div>

<ul class="nav nav-tabs nav-bordered mb-3 mt-3" role="tablist">
    <li class="nav-item"><a class="nav-link {{ $tab === 'logs' ? 'active' : '' }}" href="{{ $tabLink('logs') }}"><i class="bi bi-journal-text me-1"></i>Fuel logs</a></li>
    @unless($isDriver)
    <li class="nav-item"><a class="nav-link {{ $tab === 'cards' ? 'active' : '' }}" href="{{ $tabLink('cards') }}"><i class="bi bi-credit-card me-1"></i>Fuel cards &amp; accounts <span class="badge bg-light text-dark border ms-1">{{ $cards->count() }}</span></a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'analytics' ? 'active' : '' }}" href="{{ $tabLink('analytics') }}"><i class="bi bi-graph-up me-1"></i>Analytics &amp; efficiency @if(count($analytics['anomalies']))<span class="badge bg-danger ms-1">{{ count($analytics['anomalies']) }}</span>@endif</a></li>
    @endunless
</ul>

@if($tab === 'logs')
<x-fleet.panel>
    <form method="GET" action="{{ route('fuel.index') }}" class="fleet-toolbar">
        <input type="hidden" name="tab" value="logs">
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="search" name="fl_q" value="{{ $filters['fl_q'] ?? '' }}" class="form-control form-control-sm" placeholder="Reference, receipt, vehicle…">
            </div>
            @unless($isDriver)
            <div>
                <label class="form-label small text-muted mb-1">Vehicle</label>
                <select name="fl_vehicle" class="form-select form-select-sm">
                    <option value="">All vehicles</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected((string)($filters['fl_vehicle'] ?? '') === (string)$vehicle->id)>{{ $vehicle->license_plate ?: 'Vehicle #' . $vehicle->id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Driver</label>
                <select name="fl_driver" class="form-select form-select-sm">
                    <option value="">All drivers</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" @selected((string)($filters['fl_driver'] ?? '') === (string)$driver->id)>{{ $driver->name }}</option>
                    @endforeach
                </select>
            </div>
            @endunless
            <div>
                <label class="form-label small text-muted mb-1">From</label>
                <input type="date" name="fl_from" value="{{ $filters['fl_from'] ?? '' }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">To</label>
                <input type="date" name="fl_to" value="{{ $filters['fl_to'] ?? '' }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Station / vendor</label>
                <input type="text" name="fl_station" value="{{ $filters['fl_station'] ?? '' }}" class="form-control form-control-sm" placeholder="Name or vendor">
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="fleet-btn fleet-btn-sm"><i class="bi bi-funnel me-1"></i>Apply</button>
            <a href="{{ $tabLink('logs') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost">Reset</a>
        </div>
    </form>

    @if($logs->isEmpty())
        <x-fleet.empty icon="bi-fuel-pump">No fuel records match these filters.</x-fleet.empty>
    @else
    <div class="table-responsive">
        <table class="fleet-table" id="fuel_logs_table">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Litres</th>
                    <th>Unit price</th>
                    <th>Cost</th>
                    <th>Odometer</th>
                    <th>Station / source</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td class="text-nowrap">{{ $date($log->date) }}</td>
                    <td>
                        <div class="fw-semibold">{{ $log->vehicle_data?->license_plate ?: 'Vehicle #' . $log->vehicle_id }}</div>
                        <div class="text-muted small">{{ $log->reference ?: ($log->complete ? 'Full fill' : 'Partial fill') }}</div>
                    </td>
                    <td>{{ $log->driver?->name ?: '—' }}</td>
                    <td>{{ number_format((float) $log->qty, 2) }} L</td>
                    <td>{{ number_format((float) $log->cost_per_unit, 2) }}</td>
                    <td class="fw-semibold">{{ $money($log->total_cost) }}</td>
                    <td>{{ number_format((float) $log->start_meter, 0) }}</td>
                    <td>
                        <div>{{ $log->vendor?->name ?: ($log->vendor_name ?: '—') }}</div>
                        <div class="text-muted small">{{ $log->card ? 'Card ' . $log->card->label : ($log->fuel_from ?: '—') }}</div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @can('Fuel edit')
                            <a href="{{ route('fuel.edit', $log->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endcan
                            <a href="{{ $printLink($log->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                            <a href="{{ $pdfLink($log->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            @canany(['Fuel delete', 'Vehicles edit'])
                            <form method="POST" action="{{ route('fleet.records.archive', ['type' => 'fuel', 'id' => $log->id]) }}" class="d-inline" data-fleet-confirm="Archive this fuel record?">
                                @csrf
                                <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="Archive"><i class="bi bi-archive"></i></button>
                            </form>
                            @endcanany
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-fleet.panel>
@endif

@unless($isDriver)
@if($tab === 'cards')
<x-fleet.panel>
    <form method="GET" action="{{ route('fuel.index') }}" class="fleet-toolbar">
        <input type="hidden" name="tab" value="cards">
        <div>
            <label class="form-label small text-muted mb-1">Status</label>
            <select name="fc_status" class="form-select form-select-sm">
                <option value="">All cards</option>
                @foreach($cardStatuses as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['fc_status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="fleet-btn fleet-btn-sm"><i class="bi bi-funnel me-1"></i>Apply</button>
            <a href="{{ $tabLink('cards') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost">Reset</a>
        </div>
    </form>

    @if($cards->isEmpty())
        <x-fleet.empty icon="bi-credit-card">No fuel cards yet. Add one to track provider balances and limits.</x-fleet.empty>
    @else
    <div class="table-responsive">
        <table class="fleet-table" id="fuel_cards_table">
            <thead class="table-dark">
                <tr>
                    <th>Card</th>
                    <th>Provider</th>
                    <th>Assigned to</th>
                    <th>Balance</th>
                    <th>Monthly limit</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cards as $card)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $card->label }}</div>
                        <div class="text-muted small">{{ $card->card_number }}</div>
                    </td>
                    <td>{{ $card->vendor?->name ?: '—' }}</td>
                    <td>
                        <div>{{ $card->vehicle?->license_plate ?: 'Unassigned vehicle' }}</div>
                        <div class="text-muted small">{{ $card->driver?->name ?: 'Unassigned driver' }}</div>
                    </td>
                    <td class="fw-semibold">{{ $card->currency_code }} {{ number_format((float) $card->balance, 2) }}</td>
                    <td>{{ $card->currency_code }} {{ number_format((float) $card->monthly_limit, 2) }}</td>
                    <td><x-fleet.status-pill :status="$card->status">{{ $card->status_label }}</x-fleet.status-pill></td>
                    <td>{{ $card->expires_on?->format('d M Y') ?: '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            @can('Fuel edit')
                            <button type="button" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"
                                data-bs-toggle="modal" data-bs-target="#fuelCardModal" data-mode="edit"
                                data-action="{{ route('fuel.cards.update', $card->id) }}"
                                data-label="{{ $card->label }}"
                                data-card-number="{{ $card->card_number }}"
                                data-vendor="{{ $card->vendor_id }}"
                                data-vehicle="{{ $card->vehicle_id }}"
                                data-driver="{{ $card->driver_id }}"
                                data-currency="{{ $card->currency_code }}"
                                data-limit="{{ $card->monthly_limit }}"
                                data-balance="{{ $card->balance }}"
                                data-status="{{ $card->status }}"
                                data-expires="{{ $card->expires_on?->format('Y-m-d') }}"
                                data-note="{{ $card->note }}"><i class="bi bi-pencil"></i></button>
                            @endcan
                            @can('Fuel delete')
                            <form method="POST" action="{{ route('fuel.cards.archive', $card->id) }}" class="d-inline" data-fleet-confirm="Archive this fuel card?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="Archive"><i class="bi bi-archive"></i></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-fleet.panel>
@endif
@endunless

@if($tab === 'analytics')
<div class="row g-3">
    <div class="col-lg-7">
        <x-fleet.panel title="Litres by vehicle" icon="bi-bar-chart">
            <canvas id="fuelByVehicleChart" height="230"></canvas>
        </x-fleet.panel>
    </div>
    <div class="col-lg-5">
        <x-fleet.panel title="Efficiency summary" icon="bi-speedometer2">
            <dl class="row mb-0 small">
                <dt class="col-7 text-muted">Total litres</dt><dd class="col-5 text-end">{{ number_format($analytics['totals']['litres'], 2) }} L</dd>
                <dt class="col-7 text-muted">Total cost</dt><dd class="col-5 text-end">{{ $money($analytics['totals']['cost']) }}</dd>
                <dt class="col-7 text-muted">Distance covered</dt><dd class="col-5 text-end">{{ number_format($analytics['totals']['distance'], 0) }} km</dd>
                <dt class="col-7 text-muted">Fleet average</dt><dd class="col-5 text-end">{{ $analytics['totals']['km_per_litre'] ? number_format($analytics['totals']['km_per_litre'], 2) . ' km/L' : '—' }}</dd>
                <dt class="col-7 text-muted">Anomalies flagged</dt><dd class="col-5 text-end">{{ count($analytics['anomalies']) }}</dd>
            </dl>
        </x-fleet.panel>
    </div>
</div>

<x-fleet.panel class="mt-3" title="Consumption by vehicle" icon="bi-truck">
    @if(empty($analytics['vehicles']))
        <x-fleet.empty icon="bi-graph-up">No fuel records to analyse yet.</x-fleet.empty>
    @else
    <div class="table-responsive">
        <table class="fleet-table" id="fuel_vehicle_table">
            <thead class="table-dark">
                <tr><th>Vehicle</th><th>Fills</th><th>Litres</th><th>Cost</th><th>Distance</th><th>km / L</th><th>Cost / km</th></tr>
            </thead>
            <tbody>
                @foreach($analytics['vehicles'] as $vehicle)
                <tr>
                    <td class="fw-semibold">{{ $vehicle->vehicle?->license_plate ?: 'Vehicle #' . $vehicle->vehicle_id }}</td>
                    <td>{{ $vehicle->fills }}</td>
                    <td>{{ number_format($vehicle->litres, 2) }}</td>
                    <td>{{ $money($vehicle->cost) }}</td>
                    <td>{{ number_format($vehicle->distance, 0) }} km</td>
                    <td>{{ $vehicle->km_per_litre ? number_format($vehicle->km_per_litre, 2) : '—' }}</td>
                    <td>{{ $vehicle->cost_per_km ? number_format($vehicle->cost_per_km, 2) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-fleet.panel>

<x-fleet.panel class="mt-3" title="Consumption by driver" icon="bi-person-badge">
    @if($analytics['drivers']->isEmpty())
        <x-fleet.empty icon="bi-person-badge">No driver-linked fuel records yet.</x-fleet.empty>
    @else
    <div class="table-responsive">
        <table class="fleet-table" id="fuel_driver_table">
            <thead class="table-dark">
                <tr><th>Driver</th><th>Fills</th><th>Litres</th><th>Cost</th></tr>
            </thead>
            <tbody>
                @foreach($analytics['drivers'] as $driver)
                <tr>
                    <td class="fw-semibold">{{ $driver->driver?->name ?: 'Driver #' . $driver->driver_id }}</td>
                    <td>{{ $driver->fills }}</td>
                    <td>{{ number_format($driver->litres, 2) }}</td>
                    <td>{{ $money($driver->cost) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-fleet.panel>

<x-fleet.panel class="mt-3" title="Anomalies &amp; potential fuel theft" icon="bi-exclamation-triangle">
    @if(empty($analytics['anomalies']))
        <x-fleet.empty icon="bi-shield-check">No anomalies flagged. Consumption is within each vehicle's normal range.</x-fleet.empty>
    @else
    <div class="table-responsive">
        <table class="fleet-table" id="fuel_anomaly_table">
            <thead class="table-dark">
                <tr><th>Date</th><th>Vehicle</th><th>Driver</th><th>Litres</th><th>Odometer gap</th><th>km / L</th><th>Flag</th></tr>
            </thead>
            <tbody>
                @foreach($analytics['anomalies'] as $anomaly)
                <tr>
                    <td class="text-nowrap">{{ $date($anomaly->date) }}</td>
                    <td>{{ $anomaly->vehicle?->license_plate ?: '—' }}</td>
                    <td>{{ $anomaly->driver?->name ?: '—' }}</td>
                    <td>{{ number_format($anomaly->litres, 2) }}</td>
                    <td>{{ number_format($anomaly->odometer_gap, 0) }} km</td>
                    <td>{{ $anomaly->km_per_litre !== null ? number_format($anomaly->km_per_litre, 2) : '—' }}</td>
                    <td><x-fleet.status-pill status="overdue">{{ $anomaly->reason }}</x-fleet.status-pill></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-fleet.panel>
@endif

@if($tab === 'cards' || $tab === 'logs')
<div class="modal fade" id="fuelCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="fuelCardForm" method="POST" action="{{ route('fuel.cards.store') }}">
                @csrf
                <input type="hidden" name="_method" id="fuelCardMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="fuelCardTitle">Add fuel card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Label</label>
                            <input type="text" name="label" id="card_label" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Card number</label>
                            <input type="text" name="card_number" id="card_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Provider (vendor)</label>
                            <select name="vendor_id" id="card_vendor" class="form-select">
                                <option value="">—</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}@if($vendor->type) ({{ $vendor->type }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="card_status" class="form-select">
                                @foreach($cardStatuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned vehicle</label>
                            <select name="vehicle_id" id="card_vehicle" class="form-select">
                                <option value="">—</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->license_plate ?: 'Vehicle #' . $vehicle->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned driver</label>
                            <select name="driver_id" id="card_driver" class="form-select">
                                <option value="">—</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <select name="currency_code" id="card_currency" class="form-select">
                                @forelse($currencies as $currencyOption)
                                    <option value="{{ $currencyOption->code }}">{{ $currencyOption->code }}</option>
                                @empty
                                    <option value="{{ config('logistics.base_currency', 'MWK') }}">{{ config('logistics.base_currency', 'MWK') }}</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monthly limit</label>
                            <input type="number" step="0.01" min="0" name="monthly_limit" id="card_limit" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Balance</label>
                            <input type="number" step="0.01" min="0" name="balance" id="card_balance" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expires on</label>
                            <input type="date" name="expires_on" id="card_expires" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" id="card_note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="fleet-btn">Save card</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    $(function () {
        var tableOptions = {
            pageLength: 25,
            columnDefs: [{ orderable: false, targets: [-1] }],
            language: { url: '{{ asset("assets/datatables/") . "/" . __("fleet.datatable_lang") }}' }
        };
        ['#fuel_logs_table', '#fuel_cards_table'].forEach(function (selector) {
            if ($(selector).length) { $(selector).DataTable(tableOptions); }
        });
        ['#fuel_vehicle_table', '#fuel_driver_table', '#fuel_anomaly_table'].forEach(function (selector) {
            if ($(selector).length) { $(selector).DataTable({ pageLength: 25, language: tableOptions.language }); }
        });

        var cardModal = document.getElementById('fuelCardModal');
        if (cardModal) {
            cardModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var form = document.getElementById('fuelCardForm');
                var method = document.getElementById('fuelCardMethod');
                var title = document.getElementById('fuelCardTitle');
                var isEdit = button && button.getAttribute('data-mode') === 'edit';

                form.reset();
                form.action = isEdit ? button.getAttribute('data-action') : '{{ route('fuel.cards.store') }}';
                method.value = isEdit ? 'PUT' : 'POST';
                title.textContent = isEdit ? 'Edit fuel card' : 'Add fuel card';

                if (isEdit) {
                    document.getElementById('card_label').value = button.getAttribute('data-label') || '';
                    document.getElementById('card_number').value = button.getAttribute('data-card-number') || '';
                    document.getElementById('card_vendor').value = button.getAttribute('data-vendor') || '';
                    document.getElementById('card_vehicle').value = button.getAttribute('data-vehicle') || '';
                    document.getElementById('card_driver').value = button.getAttribute('data-driver') || '';
                    document.getElementById('card_currency').value = button.getAttribute('data-currency') || '';
                    document.getElementById('card_limit').value = button.getAttribute('data-limit') || '0';
                    document.getElementById('card_balance').value = button.getAttribute('data-balance') || '0';
                    document.getElementById('card_status').value = button.getAttribute('data-status') || 'active';
                    document.getElementById('card_expires').value = button.getAttribute('data-expires') || '';
                    document.getElementById('card_note').value = button.getAttribute('data-note') || '';
                }
            });
        }

        var chartCanvas = document.getElementById('fuelByVehicleChart');
        if (chartCanvas && window.Chart) {
            var token = function (name, fallback) {
                var value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
                return value || fallback;
            };
            new Chart(chartCanvas, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Litres',
                        data: @json($chartValues),
                        backgroundColor: token('--chart-1', '#0f2a44'),
                        borderRadius: 4,
                        maxBarThickness: 46
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: token('--atlas-border', '#e5e7eb') } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endsection
