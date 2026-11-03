@extends('layouts.attex')

@section('page_title', 'New invoice')
@section('fleet_subtitle', 'Raise a draft invoice from completed trips')

@section('content')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>New invoice</h5>
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
            <div class="col-md-5 mb-3">
                <label class="form-label" for="client_id">Client <span class="text-danger">*</span></label>
                <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                    <option value="">-- select customer --</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) old('client_id') === (string) $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3 mb-3">
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

            <div class="col-md-4 mb-3">
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

        function refreshRate() {
            if (!currency || !rate || !locked || locked.checked) return;
            var code = currency.value;
            rate.value = code === base ? '1.000000' : (rates[code] || '');
        }

        if (currency) currency.addEventListener('change', refreshRate);
        if (locked) locked.addEventListener('change', function () { if (!locked.checked) refreshRate(); });
        refreshRate();
    });
</script>
@endsection
