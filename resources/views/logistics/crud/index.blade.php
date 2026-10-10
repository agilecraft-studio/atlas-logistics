@extends('layouts.attex')
@section('page_title', $title)
@section('breadcrumb')
<li class="breadcrumb-item active">{{ $title }}</li>
@endsection
@section('content')
@if(isset($entities))
<ul class="nav nav-pills mb-3">
    @foreach($entities as $key => $label)
    <li class="nav-item"><a class="nav-link {{ ($entity ?? '') === $key ? 'active' : '' }}" href="{{ route('logistics.master.index', ['entity' => $key]) }}">{{ $label }}</a></li>
    @endforeach
</ul>
@endif
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div class="text-muted">{{ $intro }}</div>
            <div class="d-flex gap-2">
                @foreach($headerLinks as $hl)
                <a href="{{ $hl['url'] }}" class="btn btn-light">{{ $hl['label'] }}</a>
                @endforeach
                @can($permission . ' add')
                <a href="{{ route($prefix . '.create', $params) }}" class="btn btn-primary"><i class="ri-add-line"></i> Add {{ $singular }}</a>
                @endcan
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle dt-simple w-100">
                <thead>
                    <tr>
                        @foreach($columns as $col)<th>{{ $col['label'] }}</th>@endforeach
                        <th class="text-end" style="width:1%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($columns as $col)
                        @php
                            $val = isset($col['value']) ? $col['value']($row) : data_get($row, $col['field'] ?? '');
                            $type = $col['type'] ?? 'text';
                            $badgeMap = ['expired' => 'danger', 'due soon' => 'warning', 'valid' => 'success', 'no expiry' => 'secondary',
                                'planned' => 'info', 'in transit' => 'primary', 'delivered' => 'success', 'closed' => 'dark', 'cancelled' => 'danger',
                                'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'available' => 'success',
                                'fitted' => 'success', 'removed' => 'secondary', 'scrapped' => 'danger', 'spare' => 'info',
                                'active' => 'success', 'suspended' => 'danger', 'terminated' => 'dark', 'on trip' => 'primary',
                                'workshop' => 'warning', 'retired' => 'secondary'];
                        @endphp
                        <td>
                            @if($type === 'date')
                                {{ $val ? \Carbon\Carbon::parse($val)->format('d M Y') : '—' }}
                            @elseif($type === 'datetime')
                                {{ $val ? \Carbon\Carbon::parse($val)->format('d M Y H:i') : '—' }}
                            @elseif($type === 'bool')
                                @if($val)<span class="badge bg-success">Yes</span>@else<span class="badge bg-secondary">No</span>@endif
                            @elseif($type === 'badge')
                                @if($val !== null && $val !== '')<span class="badge bg-{{ $badgeMap[strtolower(str_replace('_', ' ', $val))] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $val)) }}</span>@endif
                            @elseif($type === 'number')
                                {{ $val !== null && $val !== '' ? rtrim(rtrim(number_format((float) $val, 6), '0'), '.') : '—' }}
                            @elseif($type === 'money')
                                {{ $val !== null && $val !== '' ? number_format((float) $val, 2) : '—' }}
                            @else
                                {{ $val !== null && $val !== '' ? $val : '—' }}
                            @endif
                        </td>
                        @endforeach
                        <td class="text-end text-nowrap">
                            @foreach($rowLinks($row) as $link)
                            <a href="{{ $link['url'] }}" class="btn btn-sm btn-light">{{ $link['label'] }}</a>
                            @endforeach
                            @can($permission . ' edit')
                            <a href="{{ route($prefix . '.edit', array_merge($params, ['id' => $row->id])) }}" class="btn btn-sm btn-info"><i class="ri-pencil-line"></i></a>
                            @endcan
                            @can($permission . ' delete')
                            @if($canDelete($row))
                            <form method="POST" action="{{ route($prefix . '.destroy', array_merge($params, ['id' => $row->id])) }}" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="ri-delete-bin-line"></i></button>
                            </form>
                            @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () { $('.dt-simple').DataTable({ order: [], pageLength: 25 }); });
</script>
@endsection
