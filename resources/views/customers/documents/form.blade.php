@extends('layouts.attex')

@php
    $editing = $document !== null;
    $tab = $type === 'pod' ? 'pods' : 'documents';
    $action = $editing
        ? route('customers.documents.update', [$customer, $document])
        : route('customers.documents.store', $customer);
    $defaultStatus = $document->status ?? ($typeConfig['statuses'][0] ?? 'draft');
    $selectedTrip = old('trip_id', $document->trip_id ?? request('trip_id'));
@endphp

@section('page_title', ($editing ? 'Edit ' : 'New ') . strtolower($typeConfig['label']))
@section('fleet_subtitle', $customer->name)

@section('content')
<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi {{ $typeConfig['icon'] }} me-2"></i>{{ $editing ? 'Edit ' : 'New ' }}{{ strtolower($typeConfig['label']) }}</h5>
        <a href="{{ route('customers.show', ['customer' => $customer, 'tab' => $tab]) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    @if($errors->any())
    <x-fleet.alert type="danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </x-fleet.alert>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="fleet-form">
        @csrf
        @if($editing) @method('PUT') @endif
        <input type="hidden" name="type" value="{{ $type }}">

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label" for="trip_id">
                    Shipment @if($typeConfig['trip_required'] ?? false)<span class="text-danger">*</span>@endif
                </label>
                <select class="form-select @error('trip_id') is-invalid @enderror" id="trip_id" name="trip_id" @if($typeConfig['trip_required'] ?? false) required @endif>
                    <option value="">-- none --</option>
                    @foreach($trips as $trip)
                    <option value="{{ $trip->id }}" @selected((string) $selectedTrip === (string) $trip->id)>
                        {{ $trip->trip_number }} — {{ $trip->route_label }} — {{ $trip->status_label }}
                    </option>
                    @endforeach
                </select>
                @error('trip_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="form-text">Driver, vehicle, cargo and consignee details are copied from the shipment when saved.</div>
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                    @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $defaultStatus) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label" for="issue_date">Issue date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('issue_date') is-invalid @enderror" id="issue_date" name="issue_date"
                       value="{{ old('issue_date', optional($document?->issue_date)->toDateString() ?: now()->toDateString()) }}" required>
                @error('issue_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label" for="document_number">Document number</label>
                <input type="text" class="form-control" id="document_number" value="{{ $document->document_number ?? 'Generated on save' }}" disabled>
                <div class="form-text">Numbering uses the {{ $typeConfig['prefix'] }}-year-sequence format.</div>
            </div>

            @if($typeConfig['has_validity'] ?? false)
            <div class="col-md-3 mb-3">
                <label class="form-label" for="valid_until">Valid until</label>
                <input type="date" class="form-control @error('valid_until') is-invalid @enderror" id="valid_until" name="valid_until"
                       value="{{ old('valid_until', optional($document?->valid_until)->toDateString()) }}">
                @error('valid_until')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label" for="amount">Quoted amount</label>
                <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount"
                       value="{{ old('amount', $document?->amount) }}">
                @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label" for="currency_code">Currency</label>
                <select class="form-select @error('currency_code') is-invalid @enderror" id="currency_code" name="currency_code">
                    @foreach($currencies as $currency)
                    <option value="{{ $currency->code }}" @selected(old('currency_code', $document?->currency_code ?: \App\Services\CurrencyService::base()) === $currency->code)>{{ $currency->code }}</option>
                    @endforeach
                </select>
                @error('currency_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label" for="exchange_rate">Rate to {{ \App\Services\CurrencyService::base() }}</label>
                <input type="text" inputmode="decimal" class="form-control @error('exchange_rate') is-invalid @enderror" id="exchange_rate" name="exchange_rate"
                       value="{{ old('exchange_rate', $document?->exchange_rate ?: '1.000000') }}">
                @error('exchange_rate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            @endif

            @if($type === 'pod')
            <div class="col-md-3 mb-3">
                <label class="form-label" for="signed_by">Signed by</label>
                <input type="text" class="form-control" id="signed_by" name="signed_by" maxlength="150"
                       value="{{ old('signed_by', $document?->signed_by) }}" placeholder="Receiver name">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="signed_at">Signed at</label>
                <input type="datetime-local" class="form-control" id="signed_at" name="signed_at"
                       value="{{ old('signed_at', optional($document?->signed_at)->format('Y-m-d\TH:i')) }}">
            </div>
            @endif

            <div class="col-md-6 mb-3">
                <label class="form-label" for="file">
                    {{ $type === 'pod' ? 'Signed PoD (PDF / image)' : 'Supporting file (optional)' }}
                    @if($type === 'pod' && !$editing)<span class="text-danger">*</span>@endif
                </label>
                <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file"
                       accept=".pdf,.jpg,.jpeg,.png,.webp" @if($type === 'pod' && !$editing) required @endif>
                @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @if($editing && $document->has_file)
                <div class="form-text">
                    Current file:
                    <a href="{{ $document->file_url }}" target="_blank" rel="noopener">{{ $document->file_name ?: basename($document->file_path) }}</a>
                    · upload a new file to replace it.
                </div>
                @else
                <div class="form-text">PDF, JPG, PNG or WEBP up to 10 MB.</div>
                @endif
            </div>

            <div class="col-12 mb-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="4" maxlength="5000">{{ old('notes', $document?->notes) }}</textarea>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="fleet-btn"><i class="bi bi-save me-1"></i>{{ $editing ? 'Save changes' : 'Create document' }}</button>
            <a href="{{ route('customers.show', ['customer' => $customer, 'tab' => $tab]) }}" class="fleet-btn fleet-btn-ghost">Cancel</a>
        </div>
    </form>
</x-fleet.panel>
@endsection
