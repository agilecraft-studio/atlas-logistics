@extends('layouts.attex')

@section('page_title', 'Edit ' . $invoice->invoice_number)
@section('fleet_subtitle', 'Draft invoice — editable until issued')

@section('content')
@php
    $money = fn ($amount) => \App\Support\Money::format(\App\Support\Money::toMinor($amount), $invoice->currency_code, $invoice->currency_symbol);
    $totalMinor = \App\Support\Money::toMinor($invoice->total);
@endphp

@if($errors->any())
<x-fleet.alert type="danger">
    <strong>Please fix the following:</strong>
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</x-fleet.alert>
@endif

<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>{{ $invoice->invoice_number }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('logistics.invoices.show', $invoice) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
            @can('Invoices delete')
            <form method="POST" action="{{ route('logistics.invoices.destroy', $invoice) }}" data-fleet-confirm="Delete this draft invoice?">
                @csrf @method('DELETE')
                <button class="fleet-btn fleet-btn-danger"><i class="bi bi-trash me-1"></i>Delete draft</button>
            </form>
            @endcan
        </div>
    </div>

    <form method="POST" action="{{ route('logistics.invoices.update', $invoice) }}" class="fleet-form">
        @csrf @method('PUT')
        <div class="row">
            <div class="col-md-5 mb-3">
                <label class="form-label" for="client_id">Client</label>
                <select class="form-select" id="client_id" name="client_id" required>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) old('client_id', $invoice->client_id) === (string) $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Currency</label>
                <input type="text" class="form-control" value="{{ $invoice->currency_code }} ({{ $invoice->currency_symbol }})" disabled>
                <div class="form-text">Fixed for this invoice.</div>
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label" for="issue_date">Issue date</label>
                <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ old('issue_date', optional($invoice->issue_date)->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="due_date">Due date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label" for="tax_rate">Tax rate (%)</label>
                <input type="number" step="0.0001" min="0" max="100" class="form-control" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $invoice->tax_rate) }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="exchange_rate">Exchange rate to {{ $base }}</label>
                <input type="text" inputmode="decimal" class="form-control" id="exchange_rate" name="exchange_rate" value="{{ old('exchange_rate', $invoice->exchange_rate) }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label d-block">&nbsp;</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="rate_locked" name="rate_locked" value="1" @checked(old('rate_locked', $invoice->rate_locked))>
                    <label class="form-check-label" for="rate_locked">Lock this rate at issue</label>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Exchange rate policy</label>
                <div class="form-control-plaintext text-muted small">
                    @if($invoice->rate_locked)
                        Locked: {{ \App\Support\Money::roundDecimal($invoice->exchange_rate, 6) }} {{ $base }} per 1 {{ $invoice->currency_code }} will be used.
                    @else
                        Refreshed from Master Data &gt; Exchange Rates when issued (currently {{ \App\Support\Money::roundDecimal($invoice->exchange_rate, 6) }}).
                    @endif
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="payment_instructions">Payment instructions</label>
                <textarea class="form-control" id="payment_instructions" name="payment_instructions" rows="4">{{ old('payment_instructions', $invoice->payment_instructions) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </div>
        <button type="submit" class="fleet-btn"><i class="bi bi-save me-1"></i>Save header</button>
    </form>
</x-fleet.panel>

<x-fleet.panel class="mt-3">
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Invoice lines</h5>
        <div class="d-flex gap-2">
            @can('Invoices issue')
            <form method="POST" action="{{ route('logistics.invoices.issue', $invoice) }}" data-fleet-confirm="Issue this invoice? Issued invoices are locked and cannot be edited.">
                @csrf
                <button class="fleet-btn" @disabled($totalMinor <= 0)><i class="bi bi-send me-1"></i>Issue invoice</button>
            </form>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered fleet-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:12%">Type</th>
                    <th>Description</th>
                    <th style="width:18%">Trip</th>
                    <th style="width:9%" class="text-end">Qty</th>
                    <th style="width:13%" class="text-end">Unit price</th>
                    <th style="width:13%" class="text-end">Line total</th>
                    <th style="width:1%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoice->lines as $line)
                @php($formId = 'line-form-' . $line->id)
                <tr>
                    <td>
                        <select class="form-select form-select-sm" form="{{ $formId }}" name="line_type" required>
                            @foreach($lineTypes as $key => $label)
                            <option value="{{ $key }}" @selected($line->line_type === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" form="{{ $formId }}" name="description" value="{{ $line->description }}" maxlength="255" required>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" form="{{ $formId }}" name="trip_id">
                            <option value="">— none —</option>
                            @foreach($trips as $trip)
                            <option value="{{ $trip->id }}" @selected((string) $line->trip_id === (string) $trip->id)>{{ $trip->trip_number }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.0001" min="0.0001" class="form-control form-control-sm text-end" form="{{ $formId }}" name="quantity" value="{{ $line->quantity }}" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end" form="{{ $formId }}" name="unit_price" value="{{ $line->unit_price }}" required>
                    </td>
                    <td class="text-end text-nowrap">{{ $money($line->line_total) }}</td>
                    <td class="text-end text-nowrap">
                        <form id="{{ $formId }}" method="POST" action="{{ route('logistics.invoices.lines.update', [$invoice, $line]) }}" class="d-inline">
                            @csrf @method('PUT')
                            <button class="fleet-btn fleet-btn-sm"><i class="bi bi-check2"></i> Save</button>
                        </form>
                        <form method="POST" action="{{ route('logistics.invoices.lines.destroy', [$invoice, $line]) }}" class="d-inline" data-fleet-confirm="Remove this line?">
                            @csrf @method('DELETE')
                            <button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No lines yet — add one below or delete this draft and generate freight from a completed trip.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Subtotal</th>
                    <th class="text-end text-nowrap">{{ $money($invoice->subtotal) }}</th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end">Tax ({{ \App\Support\Money::roundDecimal($invoice->tax_rate, 4) }}%)</th>
                    <th class="text-end text-nowrap">{{ $money($invoice->tax_amount) }}</th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end">Total</th>
                    <th class="text-end text-nowrap fs-6">{{ $money($invoice->total) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <hr class="my-4">

    <h6 class="mb-3">Add a line</h6>
    <form method="POST" action="{{ route('logistics.invoices.lines.store', $invoice) }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-2">
            <label class="form-label" for="new_line_type">Type</label>
            <select class="form-select" id="new_line_type" name="line_type" required>
                @foreach($lineTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('line_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="new_description">Description</label>
            <input type="text" class="form-control" id="new_description" name="description" value="{{ old('description') }}" maxlength="255" required>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="new_trip">Trip</label>
            <select class="form-select" id="new_trip" name="trip_id">
                <option value="">— none —</option>
                @foreach($trips as $trip)
                <option value="{{ $trip->id }}" @selected((string) old('trip_id') === (string) $trip->id)>{{ $trip->trip_number }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <label class="form-label" for="new_quantity">Qty</label>
            <input type="number" step="0.0001" min="0.0001" class="form-control" id="new_quantity" name="quantity" value="{{ old('quantity', '1') }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="new_unit_price">Unit price ({{ $invoice->currency_code }})</label>
            <input type="number" step="0.01" min="0" class="form-control" id="new_unit_price" name="unit_price" value="{{ old('unit_price') }}" required>
        </div>
        <div class="col-md-1">
            <button class="fleet-btn w-100"><i class="bi bi-plus-lg"></i></button>
        </div>
    </form>
</x-fleet.panel>
@endsection
