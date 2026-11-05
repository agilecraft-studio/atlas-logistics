@php
    $value = fn ($key) => old($key, $incident?->{$key});
    $dateTime = fn ($key) => old($key, $incident?->{$key}?->format('Y-m-d\TH:i'));
@endphp
<div class="row g-2">
    <div class="col-md-6">
        <label class="form-label" for="incident_trip_id">Trip</label>
        <select class="form-select" id="incident_trip_id" name="trip_id">
            <option value="">No specific trip</option>
            @foreach($tripOptions as $option)
            <option value="{{ $option->id }}" @selected((string) $value('trip_id') === (string) $option->id)>{{ $option->trip_number }} · {{ $option->route_label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="incident_type">Type</label>
        <select class="form-select" id="incident_type" name="type" required>
            @foreach($incidentTypes as $key => $label)
            <option value="{{ $key }}" @selected($value('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="incident_occurred_at">Occurred at</label>
        <input type="datetime-local" class="form-control" id="incident_occurred_at" name="occurred_at" value="{{ $dateTime('occurred_at') ?: now()->format('Y-m-d\TH:i') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="incident_location">Location</label>
        <input type="text" class="form-control" id="incident_location" name="location" maxlength="255" value="{{ $value('location') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="incident_fine">Fine amount</label>
        <input type="number" step="0.01" min="0" class="form-control" id="incident_fine" name="fine_amount" value="{{ $value('fine_amount') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="incident_currency">Currency</label>
        <select class="form-select" id="incident_currency" name="currency_code" required>
            @foreach($currencies as $currency)
            <option value="{{ $currency->code }}" @selected(($value('currency_code') ?: \App\Services\CurrencyService::base()) === $currency->code)>{{ $currency->code }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="incident_rate">Exchange rate</label>
        <input type="number" step="0.000001" min="0.000001" class="form-control" id="incident_rate" name="exchange_rate" value="{{ $value('exchange_rate') ?: 1 }}">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="incident_points">Points</label>
        <input type="number" min="0" max="100" class="form-control" id="incident_points" name="points" value="{{ $value('points') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="incident_status">Status</label>
        <select class="form-select" id="incident_status" name="status" required>
            @foreach($incidentStatuses as $key => $label)
            <option value="{{ $key }}" @selected(($value('status') ?: 'open') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="incident_resolved_at">Resolved at</label>
        <input type="datetime-local" class="form-control" id="incident_resolved_at" name="resolved_at" value="{{ $dateTime('resolved_at') }}">
    </div>
    <div class="col-12">
        <label class="form-label" for="incident_description">Description</label>
        <textarea class="form-control" id="incident_description" name="description" rows="2" maxlength="4000">{{ $value('description') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="incident_action_taken">Action taken</label>
        <textarea class="form-control" id="incident_action_taken" name="action_taken" rows="2" maxlength="4000">{{ $value('action_taken') }}</textarea>
    </div>
</div>
