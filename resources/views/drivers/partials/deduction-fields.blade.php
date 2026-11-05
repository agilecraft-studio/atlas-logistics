@php
    $value = fn ($key) => old($key, $deduction?->{$key});
@endphp
<div class="row g-2">
    <div class="col-md-4">
        <label class="form-label" for="deduction_type">Type</label>
        <select class="form-select" id="deduction_type" name="type" required>
            @foreach($deductionTypes as $key => $label)
            <option value="{{ $key }}" @selected($value('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="deduction_amount">Amount</label>
        <input type="number" step="0.01" min="0.01" class="form-control" id="deduction_amount" name="amount" value="{{ $value('amount') }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="deduction_currency">Currency</label>
        <select class="form-select" id="deduction_currency" name="currency_code" required>
            @foreach($currencies as $currency)
            <option value="{{ $currency->code }}" @selected(($value('currency_code') ?: \App\Services\CurrencyService::base()) === $currency->code)>{{ $currency->code }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="deduction_rate">Exchange rate</label>
        <input type="number" step="0.000001" min="0.000001" class="form-control" id="deduction_rate" name="exchange_rate" value="{{ $value('exchange_rate') ?: 1 }}">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="deduction_description">Description</label>
        <input type="text" class="form-control" id="deduction_description" name="description" maxlength="255" value="{{ $value('description') }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="deduction_balance">Outstanding balance</label>
        <input type="number" step="0.01" min="0" class="form-control" id="deduction_balance" name="balance" value="{{ $value('balance') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="deduction_incurred_on">Incurred on</label>
        <input type="date" class="form-control" id="deduction_incurred_on" name="incurred_on" value="{{ $value('incurred_on') instanceof \Carbon\Carbon ? $value('incurred_on')->format('Y-m-d') : ($value('incurred_on') ?: now()->toDateString()) }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="deduction_status">Status</label>
        <select class="form-select" id="deduction_status" name="status" required>
            @foreach($deductionStatuses as $key => $label)
            <option value="{{ $key }}" @selected(($value('status') ?: 'active') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
