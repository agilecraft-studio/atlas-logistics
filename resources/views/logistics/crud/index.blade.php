@extends('layouts.attex')
@section('page_title', $title)
@section('fleet_subtitle', $intro)
@section('content')
@if(isset($entities))
<div class="fleet-toolbar">
    @foreach($entities as $key => $label)
    <a class="fleet-btn {{ ($entity ?? '') === $key ? '' : 'fleet-btn-ghost' }}" href="{{ route('logistics.master.index', ['entity' => $key]) }}">{{ $label }}</a>
    @endforeach
</div>
@endif
<x-fleet.panel :title="$title" icon="bi-table">
<div class="fleet-toolbar">
<div class="text-muted">{{ $intro }}</div>
            <div class="d-flex gap-2">
                @foreach($headerLinks as $hl)
                <a href="{{ $hl['url'] }}" class="fleet-btn fleet-btn-ghost">{{ $hl['label'] }}</a>
                @endforeach
                @can($permission . ' add')
                <a href="{{ route($prefix . '.create', $params) }}" class="fleet-btn"><i class="bi bi-plus-lg"></i> Add {{ $singular }}</a>
                @endcan
            </div>
        </div>
        <x-fleet.table>
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
                                @if($val)<x-fleet.status-pill status="active">Yes</x-fleet.status-pill>@else<x-fleet.status-pill status="no">No</x-fleet.status-pill>@endif
                            @elseif($type === 'badge')
                                @if($val !== null && $val !== '')<x-fleet.status-pill :status="$val">{{ ucfirst(str_replace('_', ' ', $val)) }}</x-fleet.status-pill>@endif
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
                            <a href="{{ $link['url'] }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost">{{ $link['label'] }}</a>
                            @endforeach
                            @can($permission . ' edit')
                            <a href="{{ route($prefix . '.edit', array_merge($params, ['id' => $row->id])) }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can($permission . ' delete')
                            @if($canDelete($row))
                            <form method="POST" action="{{ route($prefix . '.destroy', array_merge($params, ['id' => $row->id])) }}" class="d-inline" data-fleet-confirm="Delete this record?">
                                @csrf @method('DELETE')
                                <button class="fleet-btn fleet-btn-sm fleet-btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </x-fleet.table>
</x-fleet.panel>
@endsection
