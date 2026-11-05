@extends('layouts.attex')

@section('page_title', 'Invoices')
@section('fleet_subtitle', 'Client invoices for completed trips')

@section('content')
@php
    $money = fn ($amount, $invoice) => \App\Support\Money::format(\App\Support\Money::toMinor($amount), $invoice->currency_code, $invoice->currency_symbol);
@endphp

<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoices</h5>
        <div class="d-flex gap-2">
            @can('Invoices add')
            <div class="dropdown">
                <button class="fleet-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-plus-lg"></i> New document</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($documentTypes as $key => $config)
                    <li><a class="dropdown-item" href="{{ route('logistics.invoices.create', ['type' => $key]) }}"><i class="bi {{ $config['icon'] }} me-2"></i>{{ $config['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endcan
        </div>
    </div>

    <form method="GET" action="{{ route('logistics.invoices.index') }}" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label" for="f_client">Client</label>
            <select class="form-select" id="f_client" name="client_id">
                <option value="">All clients</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" @selected((string) ($filters['client_id'] ?? '') === (string) $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="f_type">Type</label>
            <select class="form-select" id="f_type" name="document_type">
                <option value="">All types</option>
                @foreach($documentTypes as $key => $config)
                <option value="{{ $key }}" @selected(($filters['document_type'] ?? '') === $key)>{{ $config['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="f_currency">Currency</label>
            <select class="form-select" id="f_currency" name="currency_code">
                <option value="">All</option>
                @foreach($currencies as $currency)
                <option value="{{ $currency->code }}" @selected(($filters['currency_code'] ?? '') === $currency->code)>{{ $currency->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="f_status">Status</label>
            <select class="form-select" id="f_status" name="status">
                <option value="">All</option>
                @foreach($statuses as $key => $label)
                <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="f_from">Issue from</label>
            <input type="date" class="form-control" id="f_from" name="from" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="f_to">Issue to</label>
            <input type="date" class="form-control" id="f_to" name="to" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="col-md-1 d-flex gap-2">
            <button type="submit" class="fleet-btn">Filter</button>
            <a href="{{ route('logistics.invoices.index') }}" class="fleet-btn fleet-btn-ghost" title="Clear filters"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover fleet-table align-middle mb-0" id="invoices-table">
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Client</th>
                    <th>Issued</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Balance</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td class="fw-semibold">
                        <a href="{{ route('logistics.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                    </td>
                    <td><span class="badge bg-light text-dark border"><i class="bi {{ $invoice->document_icon }} me-1"></i>{{ $invoice->document_type_label }}</span></td>
                    <td>{{ $invoice->client_name }}</td>
                    <td>{{ $invoice->issue_date ? $invoice->issue_date->format('d M Y') : '—' }}</td>
                    <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}</td>
                    <td><x-fleet.status-pill :status="str_replace('_', ' ', $invoice->status)">{{ $invoice->status_label }}</x-fleet.status-pill></td>
                    <td class="text-end text-nowrap">{{ $money($invoice->total, $invoice) }}</td>
                    <td class="text-end text-nowrap">{{ $money($invoice->amount_paid, $invoice) }}</td>
                    <td class="text-end text-nowrap fw-semibold">{{ $money($invoice->balance_due, $invoice) }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('logistics.invoices.show', $invoice) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('logistics.invoices.pdf', $invoice) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="{{ route('logistics.invoices.print', $invoice) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                        @if($invoice->client_email)
                        <form method="POST" action="{{ route('logistics.invoices.email', $invoice) }}" class="d-inline" data-fleet-confirm="Email {{ $invoice->invoice_number }} to {{ $invoice->client_email }}?">
                            @csrf
                            <button class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Email"><i class="bi bi-envelope"></i></button>
                        </form>
                        @endif
                        @can('Invoices edit')
                        @if($invoice->isDraft())
                        <a href="{{ route('logistics.invoices.edit', $invoice) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                        @endif
                        @endcan
                        @can('Invoices delete')
                        @if($invoice->isDraft())
                        <form method="POST" action="{{ route('logistics.invoices.destroy', $invoice) }}" class="d-inline" data-fleet-confirm="Delete this draft invoice?">
                            @csrf @method('DELETE')
                            <button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No documents match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-fleet.panel>
@endsection

@section('script')
<script>
    $(function () {
        if (!$.fn.DataTable) return;
        $('#invoices-table').DataTable({
            order: [],
            pageLength: 25,
            columnDefs: [{ orderable: false, targets: [9] }],
            language: { search: '', searchPlaceholder: 'Search invoices…', lengthMenu: '_MENU_ per page', info: 'Showing _START_ to _END_ of _TOTAL_ invoices' }
        });
    });
</script>
@endsection
