@extends('layouts.attex')

@section('page_title', 'Maintenance & Work Orders')

@section('fleet_topbar_actions')
    @can('WorkOrders add')
    <a href="{{ route('work_order.create') }}" class="fleet-btn"><i class="bi bi-plus-lg me-1"></i>New work order</a>
    @endcan
    @can('ServiceReminders add')
    <a href="{{ route('service-reminder.create') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-bell me-1"></i>Schedule service</a>
    @endcan
@endsection

@section('content')
@php
    $currency = \App\Model\AtlasLogistics::get('currency');
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d M Y') : '—';
    $tabLink = fn ($key) => route('maintenance.index', ['tab' => $key]);
    $printLink = fn ($id) => route('fleet.records.print', ['type' => 'work_order', 'id' => $id]);
    $pdfLink = fn ($id) => route('fleet.records.pdf', ['type' => 'work_order', 'id' => $id]);
    $filters = $filters ?? [];
@endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
<x-fleet.alert type="danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-fleet.alert>
@endif

<div class="row g-3">
    <div class="col-6 col-lg-3"><x-fleet.kpi label="Active work orders" :value="$kpis['active']" icon="bi-tools" color="bg-blue" /></div>
    <div class="col-6 col-lg-3"><x-fleet.kpi label="Awaiting parts" :value="$kpis['awaiting_parts']" icon="bi-box-seam" color="bg-amber" /></div>
    <div class="col-6 col-lg-3"><x-fleet.kpi label="Overdue services" :value="$kpis['overdue_services']" icon="bi-exclamation-triangle" color="bg-red" /></div>
    <div class="col-6 col-lg-3"><x-fleet.kpi label="Spend this month" :value="$money($kpis['month_cost'])" icon="bi-cash-stack" color="bg-teal" /></div>
</div>

<ul class="nav nav-tabs nav-bordered mb-3 mt-3" role="tablist">
    <li class="nav-item"><a class="nav-link {{ $tab === 'active' ? 'active' : '' }}" href="{{ $tabLink('active') }}"><i class="bi bi-tools me-1"></i>Active work orders <span class="badge bg-light text-dark border ms-1">{{ $kpis['active'] }}</span></a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'history' ? 'active' : '' }}" href="{{ $tabLink('history') }}"><i class="bi bi-clock-history me-1"></i>Work order history</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'preventative' ? 'active' : '' }}" href="{{ $tabLink('preventative') }}"><i class="bi bi-calendar-check me-1"></i>Preventative maintenance <span class="badge bg-light text-dark border ms-1">{{ $kpis['overdue_services'] + $kpis['due_soon_services'] }}</span></a></li>
</ul>

@if($tab === 'active' || $tab === 'history')
<x-fleet.panel>
    <form method="GET" action="{{ route('maintenance.index') }}" class="fleet-toolbar">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="search" name="mnt_q" value="{{ $filters['mnt_q'] ?? '' }}" class="form-control form-control-sm" placeholder="Order, asset, vendor…">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="mnt_status" class="form-select form-select-sm">
                    @if($tab === 'active')
                        <option value="">All active</option>
                        @foreach(['pending_approval', 'in_bay', 'awaiting_parts'] as $key)
                            <option value="{{ $key }}" @selected(($filters['mnt_status'] ?? '') === $key)>{{ $statuses[$key] }}</option>
                        @endforeach
                    @else
                        <option value="">Completed &amp; cancelled</option>
                        <option value="completed" @selected(($filters['mnt_status'] ?? '') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(($filters['mnt_status'] ?? '') === 'cancelled')>Cancelled</option>
                        <option value="archived" @selected(($filters['mnt_status'] ?? '') === 'archived')>Archived</option>
                    @endif
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Type</label>
                <select name="mnt_type" class="form-select form-select-sm">
                    <option value="">All types</option>
                    @foreach($maintenanceTypes as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['mnt_type'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Vehicle</label>
                <select name="mnt_vehicle" class="form-select form-select-sm">
                    <option value="">All vehicles</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected((string)($filters['mnt_vehicle'] ?? '') === (string)$vehicle->id)>{{ $vehicle->license_plate ?: 'Vehicle #' . $vehicle->id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">From</label>
                <input type="date" name="mnt_from" value="{{ $filters['mnt_from'] ?? '' }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">To</label>
                <input type="date" name="mnt_to" value="{{ $filters['mnt_to'] ?? '' }}" class="form-control form-control-sm">
            </div>
            @if($tab === 'history')
            <div>
                <label class="form-label small text-muted mb-1">Min cost</label>
                <input type="number" step="0.01" min="0" name="mnt_min_cost" value="{{ $filters['mnt_min_cost'] ?? '' }}" class="form-control form-control-sm" style="width:110px">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Max cost</label>
                <input type="number" step="0.01" min="0" name="mnt_max_cost" value="{{ $filters['mnt_max_cost'] ?? '' }}" class="form-control form-control-sm" style="width:110px">
            </div>
            @endif
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="fleet-btn fleet-btn-sm"><i class="bi bi-funnel me-1"></i>Apply</button>
            <a href="{{ $tabLink($tab) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost">Reset</a>
        </div>
    </form>

    @php($rows = $tab === 'active' ? $active : $history)
    @if($rows->isEmpty())
        <x-fleet.empty icon="bi-tools">{{ $tab === 'active' ? 'No open work orders match these filters.' : 'No completed or archived work orders match these filters.' }}</x-fleet.empty>
    @else
    <div class="table-responsive">
        <table class="fleet-table" id="mnt_table">
            <thead class="table-dark">
                <tr>
                    <th>Work order</th>
                    <th>Asset</th>
                    <th>Driver</th>
                    <th>Mechanic / vendor</th>
                    <th>Required by</th>
                    <th>Status</th>
                    <th>Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $order)
                <tr @if($order->trashed()) class="opacity-75" @endif>
                    <td>
                        <div class="fw-semibold">#{{ $order->id }} · {{ $order->maintenance_type_label }}</div>
                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($order->description ?: $order->note ?: 'No description', 60) }}</div>
                        @if($order->reference)<div class="text-muted small">Ref: {{ $order->reference }}</div>@endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $order->vehicle?->license_plate ?: '—' }}</div>
                        <div class="text-muted small">{{ trim(($order->vehicle?->make_name ?? '') . ' ' . ($order->vehicle?->model_name ?? '')) ?: ($order->trailer?->registration_number ? 'Trailer ' . $order->trailer->registration_number : '') }}</div>
                    </td>
                    <td>{{ $order->driver?->name ?: '—' }}</td>
                    <td>
                        <div>{{ $order->mechanic?->name ?: '—' }}</div>
                        <div class="text-muted small">{{ $order->vendor?->name ?: '—' }}</div>
                    </td>
                    <td class="text-nowrap">{{ $date($order->required_by) }}</td>
                    <td>
                        <x-fleet.status-pill :status="str_replace('_', ' ', $order->status_key)">{{ $order->status_label }}</x-fleet.status-pill>
                        @if($order->trashed())<div class="mt-1"><x-fleet.status-pill status="archived">Archived</x-fleet.status-pill></div>@endif
                        @if($order->completed_on)<div class="text-muted small mt-1">{{ $date($order->completed_on) }}</div>@endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $money($order->total_cost) }}</div>
                        <div class="text-muted small">Parts {{ $money($order->parts_total) }}</div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ url('admin/parts-used/' . $order->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Parts used"><i class="bi bi-wrench-adjustable"></i></a>
                            @can('WorkOrders edit')
                            <a href="{{ route('work_order.edit', $order->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endcan
                            <a href="{{ $printLink($order->id) }}" target="_blank" rel="noopener" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Print"><i class="bi bi-printer"></i></a>
                            <a href="{{ $pdfLink($order->id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            @if(!$order->trashed())
                                @canany(['WorkOrders delete', 'WorkOrders edit', 'Vehicles edit', 'Trailers edit', 'FleetRegister edit'])
                                <form method="POST" action="{{ route('fleet.records.archive', ['type' => 'work_order', 'id' => $order->id]) }}" class="d-inline" data-fleet-confirm="Archive this work order and return its parts to stock?">
                                    @csrf
                                    <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="Archive"><i class="bi bi-archive"></i></button>
                                </form>
                                @endcanany
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-fleet.panel>
@endif

@if($tab === 'preventative')
<x-fleet.panel>
    <form method="GET" action="{{ route('maintenance.index') }}" class="fleet-toolbar">
        <input type="hidden" name="tab" value="preventative">
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label small text-muted mb-1">Due state</label>
                <select name="mnt_state" class="form-select form-select-sm">
                    <option value="">All services</option>
                    <option value="overdue" @selected(($filters['mnt_state'] ?? '') === 'overdue')>Overdue</option>
                    <option value="due_soon" @selected(($filters['mnt_state'] ?? '') === 'due_soon')>Due soon</option>
                    <option value="upcoming" @selected(($filters['mnt_state'] ?? '') === 'upcoming')>Upcoming</option>
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Vehicle</label>
                <select name="mnt_vehicle" class="form-select form-select-sm">
                    <option value="">All vehicles</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected((string)($filters['mnt_vehicle'] ?? '') === (string)$vehicle->id)>{{ $vehicle->license_plate ?: 'Vehicle #' . $vehicle->id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="fleet-btn fleet-btn-sm"><i class="bi bi-funnel me-1"></i>Apply</button>
            <a href="{{ $tabLink('preventative') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost">Reset</a>
        </div>
    </form>

    @if($preventative->isEmpty())
        <x-fleet.empty icon="bi-calendar-check">No service reminders match these filters. Schedule a service to start tracking intervals.</x-fleet.empty>
    @else
    <div class="table-responsive">
        <table class="fleet-table" id="mnt_services_table">
            <thead class="table-dark">
                <tr>
                    <th>Vehicle</th>
                    <th>Service item</th>
                    <th>Interval</th>
                    <th>Last performed</th>
                    <th>Next due</th>
                    <th>State</th>
                    <th>Next due meter</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($preventative as $item)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $item->vehicle?->license_plate ?: 'Vehicle #' . ($item->reminder->vehicle_id ?? '—') }}</div>
                        <div class="text-muted small">{{ trim(($item->vehicle?->make_name ?? '') . ' ' . ($item->vehicle?->model_name ?? '')) ?: '—' }}</div>
                    </td>
                    <td>{{ $item->service?->description ?: 'Service #' . ($item->reminder->service_id ?? '—') }}</td>
                    <td>{{ $item->interval ?: '—' }}</td>
                    <td>
                        <div>{{ $item->last_date ?: 'N/D' }}</div>
                        <div class="text-muted small">{{ number_format($item->last_meter) }} km</div>
                    </td>
                    <td>
                        <div>{{ $item->next_due ? $item->next_due->format('d M Y') : '—' }}</div>
                        @if($item->days !== null)
                            <div class="small {{ $item->state === 'overdue' ? 'text-danger fw-semibold' : ($item->state === 'due_soon' ? 'text-warning-emphasis' : 'text-muted') }}">
                                {{ $item->days < 0 ? abs($item->days) . ' days overdue' : $item->days . ' days left' }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <x-fleet.status-pill :status="str_replace('_', ' ', $item->state)">
                            {{ $item->state === 'due_soon' ? 'Due soon' : ucfirst($item->state) }}
                        </x-fleet.status-pill>
                    </td>
                    <td>
                        @if($item->next_meter !== null)
                            <div>{{ number_format($item->next_meter) }} km</div>
                            <div class="small {{ $item->meter_state === 'overdue' ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ $item->meter_remaining <= 0 ? abs($item->meter_remaining) . ' km overdue' : number_format($item->meter_remaining) . ' km left' }}
                            </div>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @can('ServiceReminders add')
                            <a href="{{ route('service-reminder.create') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Record service"><i class="bi bi-check2-circle"></i></a>
                            @endcan
                            @can('ServiceItems edit')
                            <a href="{{ route('service-item.edit', $item->reminder->service_id) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Edit service item"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('WorkOrders add')
                            <a href="{{ route('work_order.create') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="Raise work order"><i class="bi bi-plus-square"></i></a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-fleet.panel>
@endif
@endsection

@section('scripts')
<script>
    $(function () {
        if ($('#mnt_table').length) {
            $('#mnt_table').DataTable({
                pageLength: 25,
                order: [[4, 'asc']],
                columnDefs: [{ orderable: false, targets: [-1] }],
                language: { url: '{{ asset("assets/datatables/") . "/" . __("fleet.datatable_lang") }}' }
            });
        }
        if ($('#mnt_services_table').length) {
            $('#mnt_services_table').DataTable({
                pageLength: 25,
                order: [[4, 'asc']],
                columnDefs: [{ orderable: false, targets: [-1] }],
                language: { url: '{{ asset("assets/datatables/") . "/" . __("fleet.datatable_lang") }}' }
            });
        }
    });
</script>
@endsection
