@extends('layouts.attex')

@section('page_title', $invoice->invoice_number)
@section('fleet_subtitle', 'Client invoice')

@section('content')
@php
    $money = fn ($amount) => \App\Support\Money::format(\App\Support\Money::toMinor($amount), $invoice->currency_code, $invoice->currency_symbol);
    $qty = fn ($value) => rtrim(rtrim((string) $value, '0'), '.') ?: '0';
    $trips = $invoice->lines->pluck('trip')->filter()->unique('id')->values();
    $balanceMinor = \App\Support\Money::toMinor($invoice->balance_due);
    $auditText = function ($log) {
        $d = $log->details ?: [];
        return match ($log->action) {
            'payment_recorded' => 'Payment ' . ($d['amount'] ?? '') . ' · ' . ucfirst(str_replace('_', ' ', $d['method'] ?? '')),
            'payment_removed' => 'Payment removed · ' . ($d['amount'] ?? ''),
            'issued' => 'Rate ' . ($d['rate'] ?? '—') . ' · total ' . ($d['total'] ?? '—'),
            'cancelled' => 'Reason: ' . ($d['reason'] ?? '—'),
            'line_added', 'line_updated' => $d['description'] ?? '',
            'line_removed' => 'Removed: ' . ($d['description'] ?? ''),
            default => collect($d)->map(fn ($v, $k) => $k . ': ' . (is_scalar($v) ? $v : json_encode($v)))->implode(' · '),
        };
    };
@endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
<x-fleet.alert type="danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</x-fleet.alert>
@endif

<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0">
            <i class="bi {{ $invoice->document_icon }} me-2"></i>{{ $invoice->invoice_number }}
            <span class="badge bg-light text-dark border ms-1">{{ $invoice->document_type_label }}</span>
            <x-fleet.status-pill class="ms-2" :status="str_replace('_', ' ', $invoice->status)">{{ $invoice->status_label }}</x-fleet.status-pill>
        </h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('logistics.invoices.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
            @can('Invoices edit')
            @if($invoice->isDraft())
            <a href="{{ route('logistics.invoices.edit', $invoice) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endif
            @endcan
            @can('Invoices issue')
            @if($invoice->isDraft())
            <form method="POST" action="{{ route('logistics.invoices.issue', $invoice) }}" data-fleet-confirm="Issue this invoice? It will be locked and cannot be edited.">
                @csrf
                <button class="fleet-btn"><i class="bi bi-send me-1"></i>Issue</button>
            </form>
            @endif
            @endcan
            <a href="{{ route('logistics.invoices.pdf', $invoice) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="{{ route('logistics.invoices.print', $invoice) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer me-1"></i>Print</a>
            @if($invoice->client_email)
            <form method="POST" action="{{ route('logistics.invoices.email', $invoice) }}" data-fleet-confirm="Email {{ $invoice->invoice_number }} to {{ $invoice->client_email }}?">
                @csrf
                <button class="fleet-btn fleet-btn-ghost"><i class="bi bi-envelope me-1"></i>Email</button>
            </form>
            @endif
            @can('Invoices cancel')
            @if(in_array($invoice->status, ['issued', 'part_paid'], true) && \App\Support\Money::toMinor($invoice->amount_paid) === 0)
            <button type="button" class="fleet-btn fleet-btn-danger" data-bs-toggle="modal" data-bs-target="#cancelInvoiceModal"><i class="bi bi-x-circle me-1"></i>Cancel</button>
            @endif
            @endcan
            @can('Invoices delete')
            @if($invoice->isDraft())
            <form method="POST" action="{{ route('logistics.invoices.destroy', $invoice) }}" data-fleet-confirm="Delete this draft invoice?">
                @csrf @method('DELETE')
                <button class="fleet-btn fleet-btn-danger"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
            @endif
            @endcan
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Bill to</div>
            <div class="fw-semibold fs-5">{{ $invoice->client_name }}</div>
            @if($invoice->client_address)<div class="text-muted">{{ $invoice->client_address }}</div>@endif
            @if($invoice->client_email)<div class="text-muted">{{ $invoice->client_email }}</div>@endif
            @if($invoice->client_phone)<div class="text-muted">{{ $invoice->client_phone }}</div>@endif
            @if($invoice->client)
            @can('Customer list')
            <a href="{{ route('customers.show', $invoice->client_id) }}" class="small">Open customer record</a>
            @endcan
            @endif
        </div>
        <div class="col-md-4">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Issue date</dt><dd class="col-7">{{ optional($invoice->issue_date)->format('d M Y') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Due date</dt><dd class="col-7">{{ optional($invoice->due_date)->format('d M Y') ?: '—' }}</dd>
                <dt class="col-5 text-muted">Currency</dt><dd class="col-7">{{ $invoice->currency_code }} ({{ $invoice->currency_symbol }})</dd>
                <dt class="col-5 text-muted">Rate to {{ $invoice->base_currency_code }}</dt>
                <dd class="col-7">{{ \App\Support\Money::roundDecimal($invoice->exchange_rate, 6) }}{{ $invoice->rate_locked ? ' · locked' : '' }}</dd>
                <dt class="col-5 text-muted">Tax rate</dt><dd class="col-7">{{ \App\Support\Money::roundDecimal($invoice->tax_rate, 4) }}%</dd>
            </dl>
        </div>
        <div class="col-md-3">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Created</dt><dd class="col-7">{{ optional($invoice->creator)->name ?: '—' }}</dd>
                <dt class="col-5 text-muted">Issued</dt>
                <dd class="col-7">{{ $invoice->issued_at ? optional($invoice->issuer)->name . ' · ' . $invoice->issued_at->format('d M Y H:i') : 'Not issued' }}</dd>
                @if($invoice->isCancelled())
                <dt class="col-5 text-muted">Cancelled</dt>
                <dd class="col-7">{{ optional($invoice->canceller)->name }} · {{ optional($invoice->cancelled_at)->format('d M Y H:i') }}</dd>
                @endif
            </dl>
        </div>
    </div>

    @if($trips->isNotEmpty())
    <div class="mt-3">
        <span class="text-muted small text-uppercase fw-semibold me-2">Trips</span>
        @foreach($trips as $trip)
        @can('Trips list')
        <a href="{{ route('logistics.trips.show', $trip->id) }}" class="badge bg-light text-dark border text-decoration-none me-1">{{ $trip->trip_number }}</a>
        @else
        <span class="badge bg-light text-dark border me-1">{{ $trip->trip_number }}</span>
        @endcan
        @endforeach
    </div>
    @endif

    @if($invoice->isCancelled() && $invoice->cancel_reason)
    <div class="alert alert-danger mt-3 mb-0"><strong>Cancelled:</strong> {{ $invoice->cancel_reason }}</div>
    @endif

    @if($invoice->sourceInvoice)
    <div class="alert alert-warning mt-3 mb-0">
        <strong>Credit note for</strong>
        <a href="{{ route('logistics.invoices.show', $invoice->sourceInvoice) }}">{{ $invoice->sourceInvoice->invoice_number }}</a>
        @if($invoice->credit_reason) · {{ $invoice->credit_reason }} @endif
    </div>
    @endif

    @if($invoice->creditNotes->isNotEmpty())
    <div class="mt-3">
        <span class="text-muted small text-uppercase fw-semibold me-2">Credit notes</span>
        @foreach($invoice->creditNotes as $credit)
        <a href="{{ route('logistics.invoices.show', $credit) }}" class="badge bg-light text-dark border text-decoration-none me-1">{{ $credit->invoice_number }}</a>
        @endforeach
    </div>
    @endif

    @if(!$invoice->isTaxInvoice())
    <div class="alert alert-info mt-3 mb-0">
        {{ $invoice->document_type_label }} documents do not accept payments and do not count as revenue.
    </div>
    @endif
</x-fleet.panel>

<x-fleet.panel class="mt-3">
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Lines</h5>
        <div class="text-muted small">{{ $invoice->lines->count() }} line(s)</div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered fleet-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Trip</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit price</th>
                    <th class="text-end">Line total</th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoice->lines as $line)
                <tr>
                    <td>{{ $line->line_type_label }}</td>
                    <td>{{ $line->description }}</td>
                    <td>@if($line->trip)@can('Trips list')<a href="{{ route('logistics.trips.show', $line->trip_id) }}">{{ $line->trip->trip_number }}</a>@else{{ $line->trip->trip_number }}@endcan @else — @endif</td>
                    <td class="text-end">{{ $qty($line->quantity) }}</td>
                    <td class="text-end text-nowrap">{{ $money($line->unit_price) }}</td>
                    <td class="text-end text-nowrap">{{ $money($line->line_total) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No lines.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
                <tr><th colspan="5" class="text-end">Subtotal</th><th class="text-end text-nowrap">{{ $money($invoice->subtotal) }}</th></tr>
                <tr><th colspan="5" class="text-end">Tax ({{ \App\Support\Money::roundDecimal($invoice->tax_rate, 4) }}%)</th><th class="text-end text-nowrap">{{ $money($invoice->tax_amount) }}</th></tr>
                <tr><th colspan="5" class="text-end">Total</th><th class="text-end text-nowrap fs-6">{{ $money($invoice->total) }}</th></tr>
                <tr><th colspan="5" class="text-end">Paid</th><th class="text-end text-nowrap">{{ $money($invoice->amount_paid) }}</th></tr>
                <tr><th colspan="5" class="text-end">Balance due</th><th class="text-end text-nowrap fs-5 {{ $balanceMinor > 0 ? 'text-danger' : 'text-success' }}">{{ $money($invoice->balance_due) }}</th></tr>
            </tfoot>
        </table>
    </div>
</x-fleet.panel>

<div class="row g-3 mt-0">
    <div class="col-lg-8">
        @if($invoice->isTaxInvoice())
        <x-fleet.panel class="mt-3">
            <div class="fleet-toolbar">
                <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Payments</h5>
                <div class="text-muted small">Invoice currency: {{ $invoice->currency_code }}</div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered fleet-table align-middle mb-0">
                    <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th><th>Recorded by</th><th></th></tr></thead>
                    <tbody>
                    @forelse($invoice->payments as $payment)
                        <tr>
                            <td>{{ optional($payment->paid_at)->format('d M Y') }}</td>
                            <td>{{ $payment->method_label }}</td>
                            <td>{{ $payment->reference ?: '—' }}</td>
                            <td class="text-end text-nowrap">{{ $money($payment->amount) }}</td>
                            <td>{{ optional($payment->recordedBy)->name ?: '—' }}</td>
                            <td class="text-end">
                                @can('Invoices payments')
                                @if(in_array($invoice->status, ['issued', 'part_paid', 'paid'], true))
                                <form method="POST" action="{{ route('logistics.invoices.payments.destroy', [$invoice, $payment]) }}" data-fleet-confirm="Remove this payment? It stays in the audit trail.">
                                    @csrf @method('DELETE')
                                    <button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No payments recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @can('Invoices payments')
            @if($invoice->canTakePayments() && $balanceMinor > 0)
            <hr class="my-4">
            <h6 class="mb-3">Record a payment ({{ $invoice->currency_code }})</h6>
            <form method="POST" action="{{ route('logistics.invoices.payments.store', $invoice) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label" for="amount">Amount</label>
                    <input type="number" step="0.01" min="0.01" max="{{ \App\Support\Money::fromMinor($balanceMinor) }}"
                           class="form-control" id="amount" name="amount" value="{{ \App\Support\Money::fromMinor($balanceMinor) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="paid_at">Paid on</label>
                    <input type="date" class="form-control" id="paid_at" name="paid_at" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="method">Method</label>
                    <select class="form-select" id="method" name="method" required>
                        @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="reference">Reference</label>
                    <input type="text" class="form-control" id="reference" name="reference" maxlength="100" placeholder="Bank slip / mobile money ref">
                </div>
                <div class="col-md-2">
                    <button class="fleet-btn w-100"><i class="bi bi-plus-lg me-1"></i>Record</button>
                </div>
                <div class="col-md-12">
                    <input type="text" class="form-control" name="notes" maxlength="1000" placeholder="Notes (optional)">
                </div>
            </form>
            @endif
            @endcan
        </x-fleet.panel>
        @endif

        <x-fleet.panel class="mt-3">
            <div class="fleet-toolbar">
                <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Audit trail</h5>
            </div>
            <ul class="list-unstyled mb-0">
                @forelse($invoice->auditLogs as $log)
                <li class="d-flex justify-content-between gap-3 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</div>
                        <div class="text-muted small">{{ $auditText($log) }}</div>
                    </div>
                    <div class="text-end text-muted small text-nowrap">
                        {{ optional($log->user)->name ?: 'system' }}<br>{{ $log->created_at->format('d M Y H:i') }}
                    </div>
                </li>
                @empty
                <li class="text-muted py-3">No activity yet.</li>
                @endforelse
            </ul>
        </x-fleet.panel>
    </div>

    <div class="col-lg-4">
        @if($invoice->payment_instructions)
        <x-fleet.panel class="mt-3" title="Payment instructions" icon="bi-bank">
            <div class="text-muted" style="white-space:pre-line">{{ $invoice->payment_instructions }}</div>
        </x-fleet.panel>
        @endif

        @if($invoice->notes)
        <x-fleet.panel class="mt-3" title="Notes" icon="bi-sticky">
            <div class="text-muted" style="white-space:pre-line">{{ $invoice->notes }}</div>
        </x-fleet.panel>
        @endif

        @if($invoice->isTaxInvoice())
        <x-fleet.panel class="mt-3" title="Profitability" icon="bi-graph-up">
            <p class="text-muted small mb-1">Base-currency value at the frozen rate</p>
            <div class="fs-5 fw-semibold">{{ \App\Support\Money::format(\App\Support\Money::toMinor($invoice->total_base), $invoice->base_currency_code, null) }}</div>
            <p class="text-muted small mb-0">Issued invoices feed trip profitability once issued.</p>
        </x-fleet.panel>
        @endif
    </div>
</div>

@can('Invoices cancel')
@if(in_array($invoice->status, ['issued', 'part_paid'], true) && \App\Support\Money::toMinor($invoice->amount_paid) === 0)
<x-fleet.modal id="cancelInvoiceModal" title="Cancel invoice">
    <form method="POST" action="{{ route('logistics.invoices.cancel', $invoice) }}">
        @csrf
        <p class="text-muted">Cancelling keeps the invoice and its audit trail. The number is not reused.</p>
        <div class="mb-3">
            <label class="form-label" for="cancel_reason">Reason</label>
            <textarea class="form-control" id="cancel_reason" name="reason" rows="3" maxlength="1000" required></textarea>
        </div>
        <div class="d-flex gap-2">
            <button class="fleet-btn fleet-btn-danger">Cancel invoice</button>
            <button type="button" class="fleet-btn fleet-btn-ghost" data-fleet-close>Keep it</button>
        </div>
    </form>
</x-fleet.modal>
@endif
@endcan
@endsection
