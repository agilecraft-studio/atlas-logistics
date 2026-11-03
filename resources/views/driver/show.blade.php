@extends('layouts.driver')

@section('page_title', $trip->trip_number)

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('driver.index') }}" class="text-decoration-none small">
            <i class="bi bi-chevron-left"></i> My trips
        </a>
        <span class="badge {{ $trip->status === 'in_transit' ? 'bg-success' : ($trip->status === 'completed' ? 'bg-primary' : 'bg-secondary') }}">
            {{ $trip->status_label }}
        </span>
    </div>

    <div class="driver-card mb-3">
        <div class="card-body">
            <div class="fs-4 fw-bold">{{ $trip->trip_number }}</div>
            <div class="text-muted">{{ $trip->route_label }}</div>
            <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
                <span><i class="bi bi-truck me-1"></i>{{ optional($trip->vehicle)->license_plate ?: 'No vehicle' }}</span>
                @if($trip->trailer)
                    <span><i class="bi bi-truck-front me-1"></i>{{ $trip->trailer->trailer_number ?? $trip->trailer->plate_number ?? 'Trailer' }}</span>
                @endif
                <span><i class="bi bi-calendar-event me-1"></i>{{ optional($trip->planned_start_at)->format('d M Y H:i') ?: 'No planned date' }}</span>
            </div>
        </div>
    </div>

    @if($trip->status === 'completed')
        <div class="driver-card mb-3">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                <h5 class="mt-3 mb-1">Trip delivered</h5>
                <p class="text-muted mb-0">
                    Arrival was recorded{{ optional($trip->actual_end_at)->format(' on d M Y \a\t H:i') }}.
                    Your odometer and fuel readings are with dispatch.
                </p>
            </div>
        </div>
    @else
        <div class="driver-card mb-3">
            <div class="card-body">
                <form id="checkpoint-form" method="POST" action="{{ route('driver.checkpoints.store', $trip) }}" novalidate>
                    @csrf

                    <div class="field-label">What are you recording?</div>
                    <div class="kind-tiles mb-4">
                        @foreach(config('logistics.checkpoint_kinds') as $value => $label)
                            <input type="radio" class="btn-check" name="kind" id="kind-{{ $value }}" value="{{ $value }}"
                                   autocomplete="off"
                                   @if($value === 'departure') @disabled($trip->status !== 'planned') @checked($trip->status === 'planned') @endif
                                   @if($value !== 'departure') @disabled($trip->status !== 'in_transit') @checked($trip->status === 'in_transit' && $value === 'stop') @endif>
                            <label class="kind-tile" for="kind-{{ $value }}">{{ $label }}</label>
                        @endforeach
                    </div>

                    <div class="mb-4">
                        <label class="field-label" for="odometer">Trip odometer (km)</label>
                        <input type="number" inputmode="decimal" step="0.1" min="0" max="9999999"
                               class="form-control form-control-lg odometer-input" id="odometer" name="odometer"
                               placeholder="0.0" value="{{ old('odometer', $lastOdometer > 0 ? $lastOdometer : '') }}" required>
                        <div class="form-text">Last reading: <span id="last-reading">{{ number_format($lastOdometer, 1) }}</span> km. The reading can only go up.</div>
                    </div>

                    <div class="mb-4">
                        <div class="field-label">Fuel gauge</div>
                        <div class="fuel-tiles">
                            @foreach($fuelLevels as $value => $label)
                                <input type="radio" class="btn-check" name="fuel_level" id="fuel-{{ $value }}" value="{{ $value }}"
                                       autocomplete="off" @checked($lastFuel === $value)>
                                <label class="fuel-tile" for="fuel-{{ $value }}">
                                    <i class="bi bi-fuel-pump d-block mb-1"></i>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="field-label" for="location">Where are you? <span class="fw-normal text-muted">(optional)</span></label>
                        <input type="text" class="form-control form-control-lg" id="location" name="location"
                               maxlength="150" placeholder="e.g. Mwanza border" autocomplete="off">
                    </div>

                    <div class="mb-4">
                        <label class="field-label" for="notes">Note <span class="fw-normal text-muted">(optional)</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" maxlength="255"
                                  placeholder="Anything dispatch should know"></textarea>
                    </div>

                    <div id="form-status" class="alert d-none" role="status" aria-live="polite"></div>

                    <button type="submit" id="save-btn" class="btn btn-primary w-100 driver-save">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Save checkpoint
                    </button>
                    <div class="text-center text-muted small mt-2">
                        The time is recorded automatically when you save.
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="driver-card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold">Recent checkpoints</div>
                <span class="text-muted small">Latest first</span>
            </div>

            @if($checkpoints->isEmpty())
                <div class="text-muted small py-2">Nothing recorded yet.</div>
            @else
                <ul class="checkpoint-history">
                    @foreach($checkpoints as $checkpoint)
                        <li>
                            <div class="min-w-0">
                                <div class="fw-semibold">
                                    {{ config('logistics.checkpoint_kinds.' . $checkpoint->kind, ucfirst($checkpoint->kind)) }}
                                    @if($checkpoint->location)
                                        <span class="text-muted fw-normal">· {{ $checkpoint->location }}</span>
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    {{ optional($checkpoint->arrived_at)->format('d M Y H:i') }}
                                    @if($checkpoint->recordedBy)
                                        · {{ $checkpoint->recordedBy->name }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-end small">
                                <div class="fw-semibold">{{ $checkpoint->odometer !== null ? number_format((float) $checkpoint->odometer, 1) . ' km' : '—' }}</div>
                                <div class="text-muted">{{ $checkpoint->fuel_level ? ($fuelLevels[$checkpoint->fuel_level] ?? $checkpoint->fuel_level) : '—' }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection

@section('script')
    @if($trip->status !== 'completed')
        <script>
            (function () {
                'use strict';

                var form = document.getElementById('checkpoint-form');
                if (!form) return;

                var tripId = {{ (int) $trip->id }};
                var tripNumber = @json($trip->trip_number);
                var indexUrl = @json(route('driver.index'));
                var serverLast = {{ (float) $lastOdometer }};
                var startedLocal = {{ $trip->status === 'in_transit' ? 'true' : 'false' }};
                var arrivalLocal = false;

                var odometer = document.getElementById('odometer');
                var kindInputs = form.querySelectorAll('input[name="kind"]');
                var fuelInputs = form.querySelectorAll('input[name="fuel_level"]');
                var saveBtn = document.getElementById('save-btn');
                var statusBox = document.getElementById('form-status');
                var lastReading = document.getElementById('last-reading');

                function queued() {
                    return window.DriverQueue ? DriverQueue.forTrip(tripId) : [];
                }

                function hasQueued(kind) {
                    return queued().some(function (item) { return item.payload && item.payload.kind === kind; });
                }

                function effectiveLast() {
                    var last = serverLast;
                    queued().forEach(function (item) {
                        var value = parseFloat(item.payload && item.payload.odometer);
                        if (isFinite(value) && value > last) last = value;
                    });
                    return last;
                }

                function showStatus(message, type) {
                    statusBox.className = 'alert alert-' + type;
                    statusBox.textContent = message;
                    statusBox.classList.remove('d-none');
                }

                function hideStatus() {
                    statusBox.classList.add('d-none');
                    statusBox.textContent = '';
                }

                function setKindState() {
                    var started = startedLocal || hasQueued('departure');
                    var delivered = arrivalLocal || hasQueued('arrival');

                    var departure = document.getElementById('kind-departure');
                    var stop = document.getElementById('kind-stop');
                    var arrival = document.getElementById('kind-arrival');

                    departure.disabled = started || delivered;
                    stop.disabled = !started || delivered;
                    arrival.disabled = !started || delivered;

                    if (started && !delivered && departure.checked) {
                        stop.checked = true;
                    }
                    if (!started && !delivered) {
                        departure.checked = true;
                    }

                    if (delivered) {
                        kindInputs.forEach(function (input) { input.disabled = true; });
                        fuelInputs.forEach(function (input) { input.disabled = true; });
                        odometer.disabled = true;
                        saveBtn.disabled = true;
                        showStatus('Arrival saved. It will send when you have signal — this trip is finished.', 'info');
                    }

                    lastReading.textContent = effectiveLast().toFixed(1);
                }

                function clearInvalid() {
                    odometer.classList.remove('is-invalid');
                }

                odometer.addEventListener('input', clearInvalid);

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    clearInvalid();

                    var kindInput = form.querySelector('input[name="kind"]:checked');
                    var fuelInput = form.querySelector('input[name="fuel_level"]:checked');
                    var value = parseFloat(odometer.value);

                    if (!kindInput) {
                        showStatus('Choose Departure, Stop or Arrival.', 'warning');
                        return;
                    }
                    if (!isFinite(value) || value < 0) {
                        odometer.classList.add('is-invalid');
                        showStatus('Enter the odometer reading.', 'warning');
                        return;
                    }
                    if (kindInput.value !== 'departure' && value < effectiveLast()) {
                        odometer.classList.add('is-invalid');
                        showStatus('The odometer cannot be lower than ' + effectiveLast().toFixed(1) + ' km.', 'warning');
                        return;
                    }
                    if (!fuelInput) {
                        showStatus('Choose the fuel gauge level.', 'warning');
                        return;
                    }

                    var payload = {
                        kind: kindInput.value,
                        odometer: value,
                        fuel_level: fuelInput.value,
                        location: form.location.value.trim() || null,
                        notes: form.notes.value.trim() || null,
                        client_uuid: DriverQueue.uuid()
                    };

                    DriverQueue.enqueue({
                        key: payload.client_uuid,
                        tripId: tripId,
                        url: form.action,
                        payload: payload,
                        queuedAt: Date.now()
                    });

                    if (payload.kind === 'departure') {
                        startedLocal = true;
                    }
                    if (payload.kind === 'arrival') {
                        arrivalLocal = true;
                    }

                    form.location.value = '';
                    form.notes.value = '';
                    odometer.value = payload.odometer.toFixed(1);

                    if (payload.kind !== 'arrival') {
                        showStatus(navigator.onLine ? 'Saved. Sending now…' : 'Saved on this phone. It will send when you have signal.', 'success');
                    }

                    setKindState();
                });

                document.addEventListener('driver:synced', function (event) {
                    var item = event.detail && event.detail.item;
                    if (!item || Number(item.tripId) !== tripId) return;

                    if (item.payload && item.payload.kind === 'arrival') {
                        window.location.href = indexUrl + '?arrived=' + encodeURIComponent(tripNumber);
                        return;
                    }
                    if (item.payload && item.payload.kind === 'departure') {
                        startedLocal = true;
                    }
                    showStatus('Checkpoint sent. Saved on the server.', 'success');
                    setKindState();
                });

                document.addEventListener('driver:rejected', function (event) {
                    var item = event.detail && event.detail.item;
                    if (!item || Number(item.tripId) !== tripId) return;

                    if (item.payload && item.payload.kind === 'arrival') {
                        arrivalLocal = false;
                        odometer.disabled = false;
                        saveBtn.disabled = false;
                    }
                    showStatus(event.detail.message, 'danger');
                    setKindState();
                });

                document.addEventListener('driver:queue-offline', function () {
                    showStatus('Saved on this phone. It will send when you have signal.', 'info');
                });

                setKindState();
            })();
        </script>
    @endif
@endsection
