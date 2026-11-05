@extends('layouts.master-data')

@section('page_title', 'Master Data')

@section('master-content')
<div class="row g-3">
    @foreach($groups as $group)
        @php($items = collect($group['items'])->filter(fn ($item) => auth()->user() && auth()->user()->can($item['permission'])))
        @if($items->isNotEmpty())
            <div class="col-md-6">
                <x-fleet.panel class="h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded d-flex align-items-center justify-content-center text-white" style="width:46px;height:46px;background:var(--atlas-navy)">
                            <i class="bi {{ $group['icon'] }}" style="font-size:20px"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $group['label'] }}</h5>
                            <p class="text-muted small mb-3">{{ $group['description'] }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($items as $item)
                                    <a href="{{ $item['url'] }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost">{{ $item['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-fleet.panel>
            </div>
        @endif
    @endforeach
</div>
@endsection
