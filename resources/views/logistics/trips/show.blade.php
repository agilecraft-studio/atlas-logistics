@extends('layouts.attex')
@section('page_title', 'Trip ' . $trip->trip_number)
@section('fleet_subtitle', $trip->route_label)
@section('content')
@php
    $m = fn ($v) => number_format((float) $v, 2);
    $statusColor = ['planned' => 'info', 'in_transit' => 'primary', 'completed' => 'success', 'closed' => 'dark', 'cancelled' => 'danger'][$trip->status] ?? 'secondary';
    $open = !in_array($trip->status, ['closed', 'cancelled'], true);
    $shownBlockers = session('blockers', $blockers ?? []);
    $tripInvoices = $trip->invoiceLines->pluck('invoice')->filter()->unique('id')->values();
@endphp

@if($errors->any())<x-fleet.alert type="danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-fleet.alert>@endif
@if(!empty($shownBlockers))<x-fleet.alert type="warning"><strong>Dispatch checks failed:</strong><ul class="mb-0">@foreach($shownBlockers as $b)<li>{{ $b }}</li>@endforeach</ul></x-fleet.alert>@endif

<x-fleet.panel>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div>
            <h4 class="mb-1">{{ $trip->trip_number }} <span class="badge bg-{{ $statusColor }}">{{ config('logistics.trip_statuses.' . $trip->status, $trip->status) }}</span></h4>
            <div class="text-muted">{{ $trip->route_label }} @if($trip->trip_type)&middot; {{ config('logistics.trip_types.' . $trip->trip_type, $trip->trip_type) }}@endif</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($trip->status === 'planned')
                @can('Trips edit')<a href="{{ route('logistics.trips.edit', $trip->id) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-pencil"></i> Edit</a>@endcan
                @can('Trips start')<button class="fleet-btn" data-bs-toggle="modal" data-bs-target="#startModal"><i class="bi bi-truck"></i> Start trip</button>@endcan
                @can('Trips edit')<button class="fleet-btn fleet-btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal"><i class="bi bi-x-circle"></i> Cancel</button>@endcan
            @elseif($trip->status === 'in_transit')
                @can('Trips edit')<button class="fleet-btn" data-bs-toggle="modal" data-bs-target="#completeModal"><i class="bi bi-check2-circle"></i> Mark delivered</button>@endcan
            @elseif($trip->status === 'completed')
                @can('Trips edit')<form method="POST" action="{{ route('logistics.trips.close', $trip->id) }}" data-fleet-confirm="Close this trip? Figures become final.">@csrf <button class="fleet-btn fleet-btn-danger"><i class="bi bi-lock"></i> Close trip</button></form>@endcan
            @endif
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3"><small class="text-muted">Truck</small><div>{{ optional($trip->vehicle)->license_plate }}</div></div>
        <div class="col-md-3"><small class="text-muted">Trailer</small><div>{{ optional($trip->trailer)->registration_number ?: '—' }}</div></div>
        <div class="col-md-3"><small class="text-muted">Driver</small><div>{{ optional($trip->driver)->name }} @if($trip->coDriver)<small class="text-muted">+ {{ $trip->coDriver->name }}</small>@endif</div></div>
        <div class="col-md-3"><small class="text-muted">Customer</small><div>{{ optional($trip->customer)->name ?: '—' }}</div></div>
        <div class="col-md-3 mt-2"><small class="text-muted">Planned</small><div>{{ optional($trip->planned_start_at)->format('d M Y H:i') ?: '—' }} → {{ optional($trip->planned_end_at)->format('d M Y H:i') ?: '—' }}</div></div>
        <div class="col-md-3 mt-2"><small class="text-muted">Actual</small><div>{{ optional($trip->actual_start_at)->format('d M Y H:i') ?: '—' }} → {{ optional($trip->actual_end_at)->format('d M Y H:i') ?: '—' }}</div></div>
        <div class="col-md-3 mt-2"><small class="text-muted">Odometer</small><div>{{ $trip->start_odometer ?? '—' }} → {{ $trip->end_odometer ?? '—' }} @if($trip->distance_km)({{ $trip->distance_km }} km)@endif</div></div>
        <div class="col-md-3 mt-2"><small class="text-muted">Cargo</small><div>{{ $trip->cargo_description ?: '—' }} @if($trip->cargo_weight_kg)({{ number_format($trip->cargo_weight_kg) }} kg)@endif</div></div>
    </div>
</x-fleet.panel>

<div class="row g-3 mt-1">
    <div class="col-md-3"><div class="card"><div class="card-body"><h6 class="text-muted">Revenue ({{ $base }})</h6><h3 class="mb-0">{{ $m($summary['revenue']) }}</h3><small class="text-muted">{{ $summary['invoiced_revenue'] > 0 ? 'From issued invoices' : $m($trip->revenue_amount) . ' ' . $trip->revenue_currency }}</small></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6 class="text-muted">Approved costs</h6><h3 class="mb-0">{{ $m($summary['total_cost']) }}</h3><small class="text-muted">@if($summary['allowances'] > 0)incl. {{ $m($summary['allowances']) }} allowances · @endif @if($summary['pending_count'])<span class="text-warning">+ {{ $m($summary['pending']) }} pending</span>@else all approved @endif</small></div></div></div>
    <div class="col-md-3"><div class="card {{ $summary['profit'] < 0 ? 'border-danger' : 'border-success' }}"><div class="card-body"><h6 class="text-muted">Profit / (loss)</h6><h3 class="mb-0 {{ $summary['profit'] < 0 ? 'text-danger' : 'text-success' }}">{{ $m($summary['profit']) }}</h3><small class="text-muted">Margin {{ $summary['margin'] }}%</small></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6 class="text-muted">Driver advances</h6><h3 class="mb-0">{{ $m($summary['advances']) }}</h3><small class="text-muted">Allowances {{ $m($summary['allowances']) }} · </small><small class="{{ $summary['advance_balance'] < 0 ? 'text-danger' : 'text-muted' }}">@if($summary['advance_balance'] >= 0)Driver returns {{ $m($summary['advance_balance']) }}@else Company owes {{ $m(abs($summary['advance_balance'])) }}@endif</small></div></div></div>
</div>

@can('Invoices list')
<x-fleet.panel class="mt-3">
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoices</h5>
        <div class="d-flex gap-2">
            @can('Invoices add')
            @if(in_array($trip->status, ['completed', 'closed'], true) && $tripInvoices->isEmpty())
            <a href="{{ route('logistics.invoices.create', ['trip_ids' => [$trip->id]]) }}" class="fleet-btn fleet-btn-sm"><i class="bi bi-plus-lg"></i> Invoice this trip</a>
            @endif
            @endcan
        </div>
    </div>
    @if($tripInvoices->isEmpty())
        <div class="text-muted small">No invoice has been raised for this trip yet.</div>
    @else
        <div class="table-responsive">
            <table class="table fleet-table align-middle mb-0">
                <thead><tr><th>Invoice</th><th>Status</th><th>Issued</th><th class="text-end">Total</th><th class="text-end">Balance</th><th></th></tr></thead>
                <tbody>
                @foreach($tripInvoices as $invoice)
                    <tr>
                        <td><a href="{{ route('logistics.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td><x-fleet.status-pill :status="str_replace('_', ' ', $invoice->status)">{{ $invoice->status_label }}</x-fleet.status-pill></td>
                        <td>{{ optional($invoice->issue_date)->format('d M Y') ?: '—' }}</td>
                        <td class="text-end text-nowrap">{{ \App\Support\Money::format(\App\Support\Money::toMinor($invoice->total), $invoice->currency_code, $invoice->currency_symbol) }}</td>
                        <td class="text-end text-nowrap">{{ \App\Support\Money::format(\App\Support\Money::toMinor($invoice->balance_due), $invoice->currency_code, $invoice->currency_symbol) }}</td>
                        <td class="text-end"><a href="{{ route('logistics.invoices.pdf', $invoice) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost"><i class="bi bi-file-earmark-pdf"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-fleet.panel>
@endcan

<x-fleet.panel class="mt-3">
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-signpost-split me-2"></i>Border crossings &amp; checkpoints</h5>
        @if($open)@can('Trips edit')<div class="d-flex gap-2"><button class="fleet-btn fleet-btn-sm" data-bs-toggle="modal" data-bs-target="#checkpointModal"><i class="bi bi-plus-lg"></i> Add checkpoint</button></div>@endcan @endif
    </div>
    <div class="table-responsive">
        <table class="table fleet-table align-middle mb-0">
            <thead><tr><th>Location</th><th>Arrived</th><th>Departed</th><th>Notes</th><th></th></tr></thead>
            <tbody>@forelse($trip->checkpoints as $c)
                <tr><td>{{ optional($c->borderPost)->name ?: $c->location }}</td><td>{{ $c->arrived_at->format('d M H:i') }}</td><td>{{ optional($c->departed_at)->format('d M H:i') ?: 'still there' }}</td><td>{{ $c->notes }}</td>
                <td class="text-end">@if($open)@can('Trips edit')<form method="POST" action="{{ route('logistics.trips.checkpoints.destroy', [$trip->id, $c->id]) }}" data-fleet-confirm="Remove this checkpoint?">@csrf @method('DELETE')<button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button></form>@endcan @endif</td></tr>
            @empty<tr><td colspan="5" class="text-muted text-center">None recorded.</td></tr>@endforelse</tbody>
        </table>
    </div>
</x-fleet.panel>

<x-fleet.panel class="mt-3">
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Expenses</h5>
        @if($open)@can('TripExpenses add')<div class="d-flex gap-2"><button class="fleet-btn fleet-btn-sm" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="bi bi-plus-lg"></i> Add expense</button></div>@endcan @endif
    </div>
    <div class="table-responsive">
        <table class="table fleet-table align-middle mb-0">
            <thead><tr><th>Date</th><th>Category</th><th class="text-end">Amount</th><th class="text-end">{{ $base }}</th><th>Paid by</th><th>Status</th><th></th></tr></thead>
            <tbody>@forelse($trip->expenses as $e)
                <tr><td>{{ $e->expense_date->format('d M') }}</td><td>{{ optional($e->category)->name }}</td><td class="text-end">{{ $m($e->amount) }} {{ $e->currency }}</td><td class="text-end">{{ $m($e->amount_base) }}</td><td>{{ $e->paid_by === 'company' ? 'Company' : 'Driver cash' }}</td>
                <td><span class="badge bg-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$e->status] }}">{{ ucfirst($e->status) }}</span></td>
                <td class="text-end text-nowrap">@if($open && $e->status === 'pending')@can('TripExpenses approve')<form method="POST" action="{{ route('logistics.expenses.approve', $e->id) }}" class="d-inline">@csrf @method('PUT')<button class="fleet-btn fleet-btn-sm"><i class="bi bi-check2"></i> Approve</button></form>@endcan @endif</td></tr>
            @empty<tr><td colspan="7" class="text-muted text-center">No expenses yet.</td></tr>@endforelse</tbody>
        </table>
    </div>
</x-fleet.panel>

<x-fleet.panel class="mt-3">
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Driver advances</h5>
        @if($open)@can('Advances add')<div class="d-flex gap-2"><button class="fleet-btn fleet-btn-sm" data-bs-toggle="modal" data-bs-target="#advanceModal"><i class="bi bi-plus-lg"></i> Record advance</button></div>@endcan @endif
    </div>
    <div class="table-responsive">
        <table class="table fleet-table align-middle mb-0">
            <thead><tr><th>Date</th><th>Purpose</th><th class="text-end">Amount</th><th class="text-end">{{ $base }}</th><th>Recorded by</th><th>Reference</th><th></th></tr></thead>
            <tbody>@forelse($trip->advances as $a)
                <tr><td>{{ $a->issued_at->format('d M Y') }}</td><td>{{ ucfirst($a->purpose) }}</td><td class="text-end">{{ $m($a->amount) }} {{ $a->currency }}</td><td class="text-end">{{ $m($a->amount_base) }}</td><td>{{ optional($a->issuedBy)->name ?: '—' }}</td><td>{{ $a->reference ?: ($a->method ? ucfirst(str_replace('_', ' ', $a->method)) : '—') }}</td>
                <td class="text-end">@if($open)@can('Advances delete')<form method="POST" action="{{ route('logistics.advances.destroy', $a->id) }}" data-fleet-confirm="Remove this advance? The audit trail is kept.">@csrf @method('DELETE')<button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button></form>@endcan @endif</td></tr>
            @empty<tr><td colspan="7" class="text-muted text-center">No advances recorded.</td></tr>@endforelse</tbody>
        </table>
    </div>
</x-fleet.panel>

<x-fleet.panel class="mt-3">
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Breakdowns &amp; incidents</h5>
        @if($open)@can('Trips edit')<div class="d-flex gap-2"><button class="fleet-btn fleet-btn-sm" data-bs-toggle="modal" data-bs-target="#incidentModal"><i class="bi bi-plus-lg"></i> Report incident</button></div>@endcan @endif
    </div>
    <div class="table-responsive">
        <table class="table fleet-table align-middle mb-0">
            <thead><tr><th>When</th><th>Type</th><th>Where</th><th>What happened</th><th class="text-end">Cost ({{ $base }})</th><th></th></tr></thead>
            <tbody>@forelse($trip->incidents as $i)
                <tr><td>{{ $i->occurred_at->format('d M H:i') }}</td><td>{{ ucfirst($i->type) }}</td><td>{{ $i->location ?: '—' }}</td><td>{{ $i->description }}</td><td class="text-end">{{ $m($i->cost_base) }}</td>
                <td class="text-end">@if($open)@can('Trips edit')<form method="POST" action="{{ route('logistics.trips.incidents.destroy', [$trip->id, $i->id]) }}" data-fleet-confirm="Remove this incident?">@csrf @method('DELETE')<button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button></form>@endcan @endif</td></tr>
            @empty<tr><td colspan="6" class="text-muted text-center">None reported.</td></tr>@endforelse</tbody>
        </table>
    </div>
</x-fleet.panel>

{{-- ============ Modals ============ --}}
@php $currencyOptions = function ($selected) use ($currencies) { $h = ''; foreach ($currencies as $c) { $h .= '<option value="' . e($c->code) . '"' . ($c->code === $selected ? ' selected' : '') . '>' . e($c->code) . '</option>'; } return $h; }; @endphp

<div class="modal fade" id="startModal" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('logistics.trips.start', $trip->id) }}" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Start trip</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Odometer at departure (km) *</label><input type="number" step="0.1" min="0" name="start_odometer" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Departure time (blank = now)</label><input type="datetime-local" name="actual_start_at" class="form-control"></div>
        @can('Trips override')<div class="form-check mb-2"><input type="checkbox" class="form-check-input" name="override" value="1" id="ov"><label class="form-check-label" for="ov">Override failed checks</label></div><input type="text" name="override_reason" class="form-control" placeholder="Reason for override">@endcan
    </div><div class="modal-footer"><button class="fleet-btn">Dispatch</button></div></form></div></div>

<div class="modal fade" id="completeModal" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('logistics.trips.complete', $trip->id) }}" enctype="multipart/form-data" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Mark delivered</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Odometer at drop-off (km) *</label><input type="number" step="0.1" min="{{ $trip->start_odometer }}" name="end_odometer" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Arrival time (blank = now)</label><input type="datetime-local" name="actual_end_at" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Proof of delivery (PDF / photo)</label><input type="file" name="pod" class="form-control"></div>
    </div><div class="modal-footer"><button class="fleet-btn">Confirm delivery</button></div></form></div></div>

<div class="modal fade" id="cancelModal" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('logistics.trips.cancel', $trip->id) }}" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Cancel trip</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label class="form-label">Reason *</label><input type="text" name="reason" class="form-control" required maxlength="500"></div>
    <div class="modal-footer"><button class="fleet-btn fleet-btn-danger">Cancel trip</button></div></form></div></div>

<div class="modal fade" id="checkpointModal" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('logistics.trips.checkpoints.store', $trip->id) }}" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Add checkpoint</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Border post</label><select name="border_post_id" class="form-select"><option value="">-- none --</option>@foreach($borders as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">...or other location</label><input type="text" name="location" class="form-control" maxlength="150"></div>
        <div class="row"><div class="col-6 mb-3"><label class="form-label">Arrived *</label><input type="datetime-local" name="arrived_at" class="form-control" required></div><div class="col-6 mb-3"><label class="form-label">Departed</label><input type="datetime-local" name="departed_at" class="form-control"></div></div>
        <label class="form-label">Notes</label><input type="text" name="notes" class="form-control" maxlength="500">
    </div><div class="modal-footer"><button class="fleet-btn">Save</button></div></form></div></div>

<div class="modal fade" id="expenseModal" tabindex="-1"><div class="modal-dialog modal-lg"><form method="POST" action="{{ route('logistics.expenses.store', $trip->id) }}" enctype="multipart/form-data" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Add expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Category *</label><select name="category_id" class="form-select" required>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-4 mb-3"><label class="form-label">Date *</label><input type="date" name="expense_date" class="form-control" required value="{{ now()->toDateString() }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Country</label><select name="country_id" class="form-select"><option value="">--</option>@foreach($countries as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-4 mb-3"><label class="form-label">Amount *</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Currency *</label><select name="currency" class="form-select" required>{!! $currencyOptions($base) !!}</select></div>
        <div class="col-md-4 mb-3"><label class="form-label">Rate to {{ $base }}</label><input type="number" step="0.000001" min="0" name="exchange_rate" class="form-control"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Paid by *</label><select name="paid_by" class="form-select"><option value="driver_cash">Driver cash</option><option value="company">Company</option></select></div>
        <div class="col-md-6 mb-3"><label class="form-label">Receipt (PDF / photo)</label><input type="file" name="receipt" class="form-control"></div>
    </div></div><div class="modal-footer"><button class="fleet-btn">Save expense</button></div></form></div></div>

<div class="modal fade" id="advanceModal" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('logistics.advances.store', $trip->id) }}" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Record advance for {{ optional($trip->driver)->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row">
        <div class="col-6 mb-3"><label class="form-label">Amount *</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" required></div>
        <div class="col-6 mb-3"><label class="form-label">Currency *</label><select name="currency" class="form-select" required>{!! $currencyOptions($base) !!}</select></div>
        <div class="col-6 mb-3"><label class="form-label">Rate to {{ $base }}</label><input type="number" step="0.000001" min="0" name="exchange_rate" class="form-control"></div>
        <div class="col-6 mb-3"><label class="form-label">Date *</label><input type="date" name="issued_at" class="form-control" required value="{{ now()->toDateString() }}"></div>
        <div class="col-12 mb-3"><label class="form-label">Purpose *</label><select name="purpose" class="form-select"><option value="allowance">Allowance</option><option value="fuel">Fuel</option><option value="border">Border costs</option><option value="other">Other</option></select></div>
        <div class="col-6 mb-3"><label class="form-label">Method</label><select name="method" class="form-select"><option value="">--</option>@foreach(config('logistics.payment_methods') as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
        <div class="col-6 mb-3"><label class="form-label">Reference</label><input type="text" name="reference" class="form-control" maxlength="100"></div>
        <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2" maxlength="500"></textarea></div>
    </div></div><div class="modal-footer"><button class="fleet-btn">Record advance</button></div></form></div></div>

<div class="modal fade" id="incidentModal" tabindex="-1"><div class="modal-dialog modal-lg"><form method="POST" action="{{ route('logistics.trips.incidents.store', $trip->id) }}" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Report incident</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Type *</label><select name="type" class="form-select" required><option value="breakdown">Breakdown</option><option value="accident">Accident</option><option value="delay">Delay</option><option value="theft">Theft</option><option value="other">Other</option></select></div>
        <div class="col-md-4 mb-3"><label class="form-label">When *</label><input type="datetime-local" name="occurred_at" class="form-control" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Resolved at</label><input type="datetime-local" name="resolved_at" class="form-control"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" maxlength="150"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Country</label><select name="country_id" class="form-select"><option value="">--</option>@foreach($countries as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-12 mb-3"><label class="form-label">What happened</label><textarea name="description" class="form-control" rows="2" maxlength="1000"></textarea></div>
        <div class="col-md-4 mb-3"><label class="form-label">Repair / loss cost</label><input type="number" step="0.01" min="0" name="cost" class="form-control" value="0"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Currency *</label><select name="currency" class="form-select" required>{!! $currencyOptions($base) !!}</select></div>
        <div class="col-md-4 mb-3"><label class="form-label">Rate (blank = stored)</label><input type="number" step="0.000001" min="0" name="exchange_rate" class="form-control"></div>
    </div></div><div class="modal-footer"><button class="fleet-btn">Save incident</button></div></form></div></div>
@endsection
