@extends('layouts.attex')
@section('page_title', 'Compliance Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item active">Compliance</li>
@endsection
@section('content')
<div class="row">
    <div class="col-md-3"><div class="card border-danger"><div class="card-body">
        <h6 class="text-muted">Expired</h6><h2 class="text-danger mb-0">{{ $counts['expired'] }}</h2>
        <small class="text-muted">{{ $counts['critical_expired'] }} critical (block dispatch)</small>
    </div></div></div>
    <div class="col-md-3"><div class="card border-warning"><div class="card-body">
        <h6 class="text-muted">Due in 7 days</h6><h2 class="text-warning mb-0">{{ $counts['d7'] }}</h2>
    </div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body">
        <h6 class="text-muted">Due in 30 days</h6><h2 class="mb-0">{{ $counts['d30'] }}</h2>
    </div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body">
        <h6 class="text-muted">Due in 90 days</h6><h2 class="mb-0">{{ $counts['d90'] }}</h2>
    </div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <select name="days" class="form-select" onchange="this.form.submit()">
                    @foreach([7, 14, 30, 60, 90] as $d)
                    <option value="{{ $d }}" {{ $days == $d ? 'selected' : '' }}>Expired + due within {{ $d }} days</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">Trucks, trailers and drivers</option>
                    <option value="vehicle" {{ $type === 'vehicle' ? 'selected' : '' }}>Trucks only</option>
                    <option value="trailer" {{ $type === 'trailer' ? 'selected' : '' }}>Trailers only</option>
                    <option value="driver" {{ $type === 'driver' ? 'selected' : '' }}>Drivers only</option>
                </select>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                @can('Compliance add')<a href="{{ route('logistics.documents.create') }}" class="btn btn-primary"><i class="ri-add-line"></i> Add document</a>@endcan
                <a href="{{ route('logistics.documents.index') }}" class="btn btn-light">All documents</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle dt-simple w-100">
                <thead><tr><th>Type</th><th>Belongs to</th><th>Item</th><th>Number</th><th>Expiry</th><th>Days left</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($items as $i)
                    <tr class="{{ $i['status'] === 'expired' ? 'table-danger' : '' }}">
                        <td>{{ ['vehicle' => 'Truck', 'trailer' => 'Trailer', 'driver' => 'Driver'][$i['owner_type']] }}</td>
                        <td>{{ $i['owner'] }}</td>
                        <td>{{ $i['item'] }} @if($i['critical'])<span class="badge bg-dark">critical</span>@endif</td>
                        <td>{{ $i['number'] ?: '—' }}</td>
                        <td data-order="{{ $i['expiry']->format('Y-m-d') }}">{{ $i['expiry']->format('d M Y') }}</td>
                        <td data-order="{{ $i['days_left'] }}">{{ $i['days_left'] < 0 ? abs($i['days_left']) . ' days ago' : $i['days_left'] . ' days' }}</td>
                        <td>@if($i['status'] === 'expired')<span class="badge bg-danger">Expired</span>@else<span class="badge bg-warning">Due soon</span>@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($items->isEmpty())<p class="text-center text-muted mb-0">Nothing expired or due in this window.</p>@endif
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () { $('.dt-simple').DataTable({ order: [[5, 'asc']], pageLength: 25 }); });
</script>
@endsection
