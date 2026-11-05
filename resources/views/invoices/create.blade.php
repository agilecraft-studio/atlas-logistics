@extends('layouts.attex')

@php($typeConfig = $documentTypes[$documentType] ?? $documentTypes['tax_invoice'])
@section('page_title', 'New ' . strtolower($typeConfig['label']))
@section('fleet_subtitle', 'Raise a draft document from completed trips')

@section('content')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi {{ $typeConfig['icon'] }} me-2"></i>New {{ strtolower($typeConfig['label']) }}</h5>
        <a href="{{ route('logistics.invoices.index') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    @if($errors->any())
    <x-fleet.alert type="danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </x-fleet.alert>
    @endif

    <form method="POST" action="{{ route('logistics.invoices.store') }}" class="fleet-form" id="invoice-form">
        @csrf
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label" for="document_type">Document type <span class="text-danger">*</span></label>
                <select class="form-select @error('document_type') is-invalid @enderror" id="document_type" name="document_type" required>
                    @foreach($documentTypes as $key => $config)
                    <option value="{{ $key }}" @selected(old('document_type', $documentType) === $key)>{{ $config['label'] }}</option>
                    @endforeach
                </select>
                @error('document_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3 mb-3" id="sourceInvoiceWrap">
                <label class="form-label" for="source_invoice_id">Credit against invoice</label>
                <select class="form-select @error('source_invoice_id') is-invalid @enderror" id="source_invoice_id" name="source_invoice_id">
                    <option value="">-- choose issued invoice --</option>
                    @foreach($sourceInvoices as $source)
                    <option value="{{ $source->id }}" data-client="{{ $source->client_id }}"
                            @selected((string) old('source_invoice_id') === (string) $source->id)>
                        {{ $source->invoice_number }} — {{ $source->client_name }} — {{ number_format((float) $source->total, 2) }} {{ $source->currency_code }}
                    </option>
                    @endforeach
                </select>
                @error('source_invoice_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="form-text">The credit note copies the selected invoice's lines.</div>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label" for="client_id">Client <span class="text-danger">*</span></label>
                <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                    <option value="">-- select customer --</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) old('client_id', $selectedClient) === (string) $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label" for="currency_code">Currency <span class="text-danger">*</span></label>
                <select class="form-select @error('currency_code') is-invalid @enderror" id="currency_code" name="currency_code" required>
                    @foreach($currencies as $currency)
                    <option value="{{ $currency->code }}" @selected(old('currency_code', $defaults['currency_code']) === $currency->code)>{{ $currency->code }} — {{ $currency->name }}</option>
                    @endforeach
                </select>
                @error('currency_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="form-text">All lines and payments on this invoice use this currency.</div>
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label" for="issue_date">Issue date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('issue_date') is-invalid @enderror" id="issue_date" name="issue_date"
                       value="{{ old('issue_date', $defaults['issue_date']) }}" required>
                @error('issue_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label" for="due_date">Due date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date"
                       value="{{ old('due_date', $defaults['due_date']) }}" required>
                @error('due_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label" for="tax_rate">Tax rate (%)</label>
                <input type="number" step="0.0001" min="0" max="100" class="form-control @error('tax_rate') is-invalid @enderror"
                       id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $defaults['tax_rate']) }}">
                @error('tax_rate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="form-text">Applied to the line subtotal and shown separately.</div>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label" for="exchange_rate">Exchange rate to {{ $base }}</label>
                <input type="text" inputmode="decimal" class="form-control @error('exchange_rate') is-invalid @enderror"
                       id="exchange_rate" name="exchange_rate" value="{{ old('exchange_rate', $rates[$defaults['currency_code']] ?? '') }}">
                @error('exchange_rate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="form-text">1 unit of the invoice currency = ? {{ $base }}. The rate is frozen when the invoice is issued.</div>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label d-block">&nbsp;</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="rate_locked" name="rate_locked" value="1" @checked(old('rate_locked'))>
                    <label class="form-check-label" for="rate_locked">Lock this rate at issue</label>
                </div>
                <div class="form-text">Leave off to refresh from Exchange Rates on the issue date.</div>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Invoice currency totals</label>
                <div class="form-control-plaintext text-muted">Every amount, tax and payment stays in the invoice currency.</div>
            </div>

            <div class="col-md-4 mb-3" id="tripsWrap">
                <label class="form-label" for="trip_ids">Generate freight from completed trips</label>
                <select class="form-select @error('trip_ids') is-invalid @enderror" id="trip_ids" name="trip_ids[]" multiple size="9">
                    @foreach($trips as $trip)
                    <option value="{{ $trip->id }}" @selected(in_array($trip->id, $selectedTrips, true))>
                        {{ $trip->trip_number }} — {{ $trip->route_label }} — {{ optional($trip->actual_end_at)->format('d M Y') ?: 'no delivery date' }} — {{ number_format((float) $trip->revenue_amount, 2) }} {{ $trip->revenue_currency }}
                    </option>
                    @endforeach
                </select>
                @error('trip_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="form-text">One freight line is created per trip, converted into the invoice currency. Hold Ctrl/Cmd to pick several.</div>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label" for="payment_instructions">Payment instructions</label>
                <textarea class="form-control" id="payment_instructions" name="payment_instructions" rows="8">{{ old('payment_instructions', $defaults['payment_instructions']) }}</textarea>
                <div class="form-text">Printed on the invoice: bank account, mobile money, reference format.</div>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="8">{{ old('notes') }}</textarea>
            </div>

            <div class="col-md-12 mb-3 d-none" id="creditReasonWrap">
                <label class="form-label" for="credit_reason">Reason for credit note</label>
                <textarea class="form-control @error('credit_reason') is-invalid @enderror" id="credit_reason" name="credit_reason" rows="2" maxlength="2000">{{ old('credit_reason') }}</textarea>
                @error('credit_reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="form-text">Printed on the credit note and kept in the invoice audit trail.</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="fleet-btn"><i class="bi bi-save me-1"></i>Create draft</button>
            <a href="{{ route('logistics.invoices.index') }}" class="fleet-btn fleet-btn-ghost">Cancel</a>
        </div>
        <p class="text-muted small mt-3 mb-0">After creating the draft you can add extra drops, demurrage, border and other charges before issuing.</p>
    </form>
</x-fleet.panel>
@endsection

@section('script')
<script>
    $(function () {
        var rates = @json($rates);
        var base = @json($base);
        var currency = document.getElementById('currency_code');
        var rate = document.getElementById('exchange_rate');
        var locked = document.getElementById('rate_locked');
        var type = document.getElementById('document_type');
        var source = document.getElementById('source_invoice_id');
        var sourceWrap = document.getElementById('sourceInvoiceWrap');
        var tripsWrap = document.getElementById('tripsWrap');
        var creditWrap = document.getElementById('creditReasonWrap');
        var client = document.getElementById('client_id');

        function refreshRate() {
            if (!currency || !rate || !locked || locked.checked) return;
            var code = currency.value;
            rate.value = code === base ? '1.000000' : (rates[code] || '');
        }

        function filterSources() {
            if (!source) return;
            var clientId = client ? client.value : '';
            Array.prototype.forEach.call(source.options, function (option, index) {
                if (index === 0) return;
                var match = !clientId || option.dataset.client === clientId;
                option.hidden = !match;
                if (!match && source.value === option.value) source.value = '';
            });
        }

        function refreshType() {
            var isCredit = type && type.value === 'credit_note';
            if (sourceWrap) sourceWrap.classList.toggle('d-none', !isCredit);
            if (creditWrap) creditWrap.classList.toggle('d-none', !isCredit);
            if (tripsWrap) tripsWrap.classList.toggle('d-none', isCredit);
            filterSources();
        }

        if (currency) currency.addEventListener('change', refreshRate);
        if (locked) locked.addEventListener('change', function () { if (!locked.checked) refreshRate(); });
        if (client) client.addEventListener('change', filterSources);
        if (source) source.addEventListener('change', function () {
            var option = source.selectedOptions[0];
            if (option && option.dataset.client && client && !client.value) {
                client.value = option.dataset.client;
                filterSources();
            }
        });
        if (type) type.addEventListener('change', refreshType);
        refreshRate();
        refreshType();
    });
</script>
@endsection
