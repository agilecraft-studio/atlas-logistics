@extends('layouts.attex')
@section('page_title', $title)
@section('breadcrumb')
<li class="breadcrumb-item active">{{ $title }}</li>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div class="text-muted">{{ $intro }}</div>
            <div class="d-flex gap-2">
                @foreach($extra as $x)<a href="{{ $x['url'] }}" class="btn btn-light">{{ $x['label'] }}</a>@endforeach
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle dt-simple w-100">
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
                            @if(is_array($cell))<span class="badge bg-{{ $cell['badge'] }}">{{ $cell['text'] }}</span>@else{{ $cell }}@endif
                        </td>
                        @endforeach
                        <td class="text-end text-nowrap">
                            @foreach($row['links'] as $l)
                            <a href="{{ $l['url'] }}" class="btn btn-sm {{ $l['class'] ?? 'btn-light' }}">{{ $l['label'] }}</a>
                            @endforeach
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
