@extends('layouts.attex')

@php
    $tab = $document->type === 'pod' ? 'pods' : 'documents';
    $meta = $document->meta ?: [];
    $money = fn ($amount) => \App\Support\Money::format(
        \App\Support\Money::toMinor($amount),
        $document->currency_code ?: \App\Services\CurrencyService::base()
    );
@endphp

@section('page_title', $document->document_number)
@section('fleet_subtitle', $document->type_label . ' · ' . $customer->name)

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<x-fleet.panel>
    <div class="fleet-toolbar">
        <h5 class="mb-0">
            <i class="bi {{ $typeConfig['icon'] ?? 'bi-file-earmark' }} me-2"></i>{{ $document->document_number }}
            <span class="badge bg-light text-dark border ms-1">{{ $document->type_label }}</span>
            <x-fleet.status-pill class="ms-2" :status="$document->status">{{ $document->status_label }}</x-fleet.status-pill>
        </h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('customers.show', ['customer' => $customer, 'tab' => $tab]) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
            @can('Customer documents edit')
            <a href="{{ route('customers.documents.edit', [$customer, $document]) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            <a href="{{ route('customers.documents.download', [$customer, $document]) }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-download me-1"></i>Download</a>
            <a href="{{ route('customers.documents.print', [$customer, $document]) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer me-1"></i>Print</a>
            @if($customer->email)
            <form method="POST" action="{{ route('customers.documents.email', [$customer, $document]) }}" data-fleet-confirm="Email {{ $document->document_number }} to {{ $customer->email }}?">
                @csrf
                <button class="fleet-btn fleet-btn-ghost"><i class="bi bi-envelope me-1"></i>Email</button>
            </form>
            @endif
            @can('Customer documents delete')
            <form method="POST" action="{{ route('customers.documents.destroy', [$customer, $document]) }}" data-fleet-confirm="Delete {{ $document->document_number }}?">
                @csrf @method('DELETE')
                <button class="fleet-btn fleet-btn-danger"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
            @endcan
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Customer</div>
            <div class="fw-semibold fs-5">{{ $customer->name }}</div>
            <div class="text-muted">{{ $meta['customer_address'] ?? $customer->address ?: '—' }}</div>
            <div class="text-muted">{{ $customer->email ?: 'No email' }} @if($customer->mobno) · {{ $customer->mobno }} @endif</div>

            <dl class="row mb-0 small mt-3">
                <dt class="col-5 text-muted">Issue date</dt><dd class="col-7">{{ optional($document->issue_date)->format('d M Y') ?: '—' }}</dd>
                @if($document->valid_until)
                <dt class="col-5 text-muted">Valid until</dt><dd class="col-7">{{ $document->valid_until->format('d M Y') }}</dd>
                @endif
                @if($document->amount !== null)
                <dt class="col-5 text-muted">Quoted amount</dt><dd class="col-7 fw-semibold">{{ $money($document->amount) }}</dd>
                @endif
                @if($document->signed_by)
                <dt class="col-5 text-muted">Signed by</dt><dd class="col-7">{{ $document->signed_by }}</dd>
                @endif
                @if($document->signed_at)
                <dt class="col-5 text-muted">Signed at</dt><dd class="col-7">{{ $document->signed_at->format('d M Y H:i') }}</dd>
                @endif
                <dt class="col-5 text-muted">Created by</dt><dd class="col-7">{{ optional($document->creator)->name ?: '—' }}</dd>
                <dt class="col-5 text-muted">Last updated</dt><dd class="col-7">{{ $document->updated_at ? $document->updated_at->format('d M Y H:i') : '—' }}</dd>
            </dl>
        </div>

        <div class="col-lg-6">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Shipment</div>
            @if($document->trip)
            <div class="fw-semibold fs-5"><a href="{{ route('logistics.trips.show', $document->trip) }}">{{ $document->trip->trip_number }}</a></div>
            @else
            <div class="fw-semibold fs-5">Not linked</div>
            @endif
            <dl class="row mb-0 small mt-3">
                <dt class="col-5 text-muted">Route</dt><dd class="col-7">{{ $meta['route'] ?? '—' }}</dd>
                <dt class="col-5 text-muted">Driver</dt><dd class="col-7">{{ $meta['driver_name'] ?? optional($document->driver)->name ?: '—' }}</dd>
                <dt class="col-5 text-muted">Vehicle</dt><dd class="col-7">{{ $meta['vehicle_plate'] ?? optional($document->vehicle)->license_plate ?: '—' }}</dd>
                <dt class="col-5 text-muted">Trailer</dt><dd class="col-7">{{ $meta['trailer_plate'] ?? optional($document->trailer)->registration_number ?: '—' }}</dd>
                <dt class="col-5 text-muted">Cargo</dt><dd class="col-7">{{ $meta['cargo_description'] ?? '—' }}</dd>
                <dt class="col-5 text-muted">Weight</dt><dd class="col-7">{{ $meta['cargo_weight_kg'] ? $meta['cargo_weight_kg'] . ' kg' : '—' }}</dd>
                <dt class="col-5 text-muted">Consignee</dt><dd class="col-7">{{ $meta['consignee_name'] ?? '—' }}@if(!empty($meta['consignee_phone'])) · {{ $meta['consignee_phone'] }}@endif</dd>
            </dl>
        </div>
    </div>

    @if($document->notes)
    <div class="mt-3">
        <div class="text-muted small text-uppercase fw-semibold mb-1">Notes</div>
        <div class="text-muted" style="white-space:pre-line">{{ $document->notes }}</div>
    </div>
    @endif
</x-fleet.panel>

@if($document->has_file)
<x-fleet.panel class="mt-3" title="Attached file" icon="bi-paperclip">
    @if($document->is_image)
    <a href="{{ $document->file_url }}" target="_blank" rel="noopener">
        <img src="{{ $document->file_url }}" alt="{{ $document->document_number }}" class="img-fluid rounded border" style="max-height:640px">
    </a>
    @else
    <div class="d-flex flex-wrap align-items-center gap-3">
        <i class="bi bi-file-earmark-pdf text-danger" style="font-size:40px"></i>
        <div>
            <div class="fw-semibold">{{ $document->file_name ?: basename($document->file_path) }}</div>
            <div class="text-muted small">{{ $document->mime_type }} · {{ $document->file_size ? number_format($document->file_size / 1024, 1) . ' KB' : '' }}</div>
        </div>
        <a href="{{ $document->file_url }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-ghost">Open file</a>
    </div>
    @endif
</x-fleet.panel>
@endif
@endsection
