@extends('layouts.fleet')
@section('fleet_title', $title)
@section('fleet_subtitle', $intro)
@section('content')
<x-fleet.panel :title="$title" icon="bi-table">
        <div class="fleet-toolbar">
            <div class="text-muted">{{ $intro }}</div>
            <div class="d-flex gap-2">
                @foreach($extra as $x)<a href="{{ $x['url'] }}" class="fleet-btn fleet-btn-ghost">{{ $x['label'] }}</a>@endforeach
            </div>
        </div>
        <x-fleet.table>
                <thead>
                    <tr>
                        @foreach($headers as $h)<th>{{ $h }}</th>@endforeach
                        <th class="text-end" style="width:1%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($row['cells'] as $cell)
                        <td>
                            @if(is_array($cell))<x-fleet.status-pill :status="$cell['text']">{{ $cell['text'] }}</x-fleet.status-pill>@else{{ $cell }}@endif
                        </td>
                        @endforeach
                        <td class="text-end text-nowrap">
                            @foreach($row['links'] as $l)
                            <a href="{{ $l['url'] }}" class="fleet-btn fleet-btn-sm {{ ($l['class'] ?? '') === 'btn-danger' ? 'fleet-btn-danger' : 'fleet-btn-ghost' }}">{{ $l['label'] }}</a>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </x-fleet.table>
</x-fleet.panel>
@endsection
