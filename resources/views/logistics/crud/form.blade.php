@extends('layouts.attex')
@section('page_title', $title)
@section('fleet_subtitle', $intro ?? 'Fill in the details below.')
@section('content')
<x-fleet.panel :title="$title" icon="bi-pencil-square">
        @if($intro ?? null)<p class="text-muted">{{ $intro }}</p>@endif
        @if($errors->any())
        <x-fleet.alert type="danger">
            <strong>Please fix the following:</strong>
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-fleet.alert>
        @endif
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="fleet-form">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="row">
                @foreach($fields as $f)
                @php
                    $type = $f['type'] ?? 'text';
                    $name = $f['name'];
                    $val = old($name, data_get($model, $name));
                    if ($val instanceof \Carbon\Carbon) {
                        $val = $type === 'datetime' ? $val->format('Y-m-d\TH:i') : $val->format('Y-m-d');
                    }
                    $required = isset($f['rules']) && str_contains(is_array($f['rules']) ? implode('|', $f['rules']) : $f['rules'], 'required');
                    $col = $f['col'] ?? 6;
                @endphp
                <div class="col-md-{{ $col }} mb-3">
                    @if($type === 'checkbox')
                        @php $checked = old($name, $model->exists ? data_get($model, $name) : ($f['default'] ?? true)); @endphp
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" id="f_{{ $name }}" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }}>
                            <label class="form-check-label" for="f_{{ $name }}">{{ $f['label'] }}</label>
                        </div>
                    @else
                        <label class="form-label" for="f_{{ $name }}">{{ $f['label'] }}@if($required) <span class="text-danger">*</span>@endif</label>
                        @if($type === 'textarea')
                            <textarea class="form-control @error($name) is-invalid @enderror" id="f_{{ $name }}" name="{{ $name }}" rows="3">{{ $val }}</textarea>
                        @elseif($type === 'select')
                            <select class="form-select @error($name) is-invalid @enderror" id="f_{{ $name }}" name="{{ $name }}">
                                <option value="">{{ $required ? '-- select --' : '-- none --' }}</option>
                                @foreach($f['options'] as $ov => $ol)
                                <option value="{{ $ov }}" {{ (string) $val === (string) $ov ? 'selected' : '' }}>{{ $ol }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'multiselect')
                            @php $sel = array_map('strval', (array) old($name, data_get($model, $name) ?? [])); @endphp
                            <select class="form-select @error($name) is-invalid @enderror" id="f_{{ $name }}" name="{{ $name }}[]" multiple size="6">
                                @foreach($f['options'] as $ov => $ol)
                                <option value="{{ $ov }}" {{ in_array((string) $ov, $sel, true) ? 'selected' : '' }}>{{ $ol }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'file')
                            <input type="file" class="form-control @error($name) is-invalid @enderror" id="f_{{ $name }}" name="{{ $name }}">
                            @if(data_get($model, $name))
                            <div class="form-text"><a href="{{ \Illuminate\Support\Facades\Storage::url(data_get($model, $name)) }}" target="_blank">View current file</a> (upload a new one to replace it)</div>
                            @endif
                        @else
                            @php $htmlType = $type === 'datetime' ? 'datetime-local' : $type; @endphp
                            <input type="{{ $htmlType }}" class="form-control @error($name) is-invalid @enderror" id="f_{{ $name }}" name="{{ $name }}"
                                value="{{ $val }}" @if(isset($f['step'])) step="{{ $f['step'] }}" @endif @if(isset($f['placeholder'])) placeholder="{{ $f['placeholder'] }}" @endif>
                        @endif
                        @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @if(!empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
                    @endif
                </div>
                @endforeach
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="fleet-btn">Save</button>
                <a href="{{ $back }}" class="fleet-btn fleet-btn-ghost">Cancel</a>
            </div>
        </form>
</x-fleet.panel>
@endsection
