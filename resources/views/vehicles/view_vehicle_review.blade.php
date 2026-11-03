@extends('layouts.attex')
@section('styles')
<style type="text/css">
  .custom{
  min-width: 50px;
  left: -105px !important;
  right: 0;
}
</style>
@endsection

@section('content')
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>{{ __('fleet.vehicle_inspection') }}: {{ $review->vehicle?->make_name ?? '—' }} - {{ $review->vehicle?->model_name ?? '—' }} - {{ $review->vehicle?->types?->displayname ?? '—' }}</h5>
        <div class="d-flex gap-2">
          <a href="{{ url('admin/print-vehicle-review/'.$review->id) }}" class="fleet-btn" target="_blank"><i class="bi bi-printer"></i> @lang('fleet.print')</a>
          <a href="{{ url('admin/vehicle-reviews') }}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-arrow-left"></i> @lang('fleet.back')</a>
        </div>
      </div>

      <div>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('vehicle_id',__('fleet.vehicle')." : ", ['class' => 'form-label']) !!}
              {{$review->vehicle?->make_name ?? '—'}} - {{$review->vehicle?->model_name ?? '—'}} - {{$review->vehicle?->types?->displayname ?? '—'}}
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('reg_no',__('fleet.reg_no')." : ", ['class' => 'form-label']) !!}
              {{$review->reg_no}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('kms_out',__('fleet.kms_out')." (".AtlasLogistics::get('dis_format').") : ", ['class' => 'form-label']) !!}
              {{$review->kms_outgoing}} {{AtlasLogistics::get('dis_format')}}
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('kms_in',__('fleet.kms_in')." (".AtlasLogistics::get('dis_format').") : ", ['class' => 'form-label']) !!}
              {{$review->kms_incoming}} {{AtlasLogistics::get('dis_format')}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('fuel_out',__('fleet.fuel_out')." : ", ['class' => 'form-label']) !!}
              @if($review->fuel_level_out == 0) 1/4 @endif
              @if($review->fuel_level_out == 1) 1/2 @endif
              @if($review->fuel_level_out == 2) 3/4 @endif
              @if($review->fuel_level_out == 3) Full Tank @endif
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('fuel_in',__('fleet.fuel_in')." : ", ['class' => 'form-label']) !!}
              @if($review->fuel_level_in == 0) 1/4 @endif
              @if($review->fuel_level_in == 1) 1/2 @endif
              @if($review->fuel_level_in == 2) 3/4 @endif
              @if($review->fuel_level_in == 3) Full Tank @endif
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('datetime_out',__('fleet.datetime_out')." : ", ['class' => 'form-label']) !!}
              {{date($date_format_setting.' g:i A',strtotime($review->datetime_outgoing))}}
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('datetime_in',__('fleet.datetime_in')." : ", ['class' => 'form-label']) !!}
              {{date($date_format_setting.' g:i A',strtotime($review->datetime_incoming))}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($petrol_card=\App\Support\Safe::unserialize($review->petrol_card))
            <div class="mb-3">
              {!! Form::label('petrol_card',__('fleet.petrol_card')." : ", ['class' => 'form-label']) !!}
              @if($petrol_card['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($petrol_card['flag'] == 0 && $petrol_card['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp;{{$petrol_card['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($light=\App\Support\Safe::unserialize($review->lights))
            <div class="mb-3">
              {!! Form::label('lights',__('fleet.lights')." : ", ['class' => 'form-label']) !!}
              @if($light['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($light['flag'] == 0 && $light['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$light['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($invertor=\App\Support\Safe::unserialize($review->invertor))
            <div class="mb-3">
              {!! Form::label('invertor',__('fleet.invertor')." : ", ['class' => 'form-label']) !!}
              @if($invertor['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($invertor['flag'] == 0 && $invertor['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$invertor['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($car_mat=\App\Support\Safe::unserialize($review->car_mats))
            <div class="mb-3">
              {!! Form::label('car_mats',__('fleet.car_mats')." : ", ['class' => 'form-label']) !!}
              @if($car_mat['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($car_mat['flag'] == 0 && $car_mat['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$car_mat['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($int_damage=\App\Support\Safe::unserialize($review->int_damage))
            <div class="mb-3">
              {!! Form::label('int_damage',__('fleet.int_damage')." : ", ['class' => 'form-label']) !!}
              @if($int_damage['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($int_damage['flag'] == 0 && $int_damage['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$int_damage['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($int_lights=\App\Support\Safe::unserialize($review->int_lights))
            <div class="mb-3">
              {!! Form::label('int_lights',__('fleet.int_lights')." : ", ['class' => 'form-label']) !!}
              @if($int_lights['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($int_lights['flag'] == 0 && $int_lights['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$int_lights['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($ext_car=\App\Support\Safe::unserialize($review->ext_car))
            <div class="mb-3">
              {!! Form::label('ext_car',__('fleet.ext_car')." : ", ['class' => 'form-label']) !!}
              @if($ext_car['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($ext_car['flag'] == 0 && $ext_car['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$ext_car['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($tyre=\App\Support\Safe::unserialize($review->tyre))
            <div class="mb-3">
            {!! Form::label('tyre',__('fleet.tyre')." : ", ['class' => 'form-label']) !!}
            @if($tyre['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
            @if($tyre['flag'] == 0 && $tyre['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
            &nbsp; {{$tyre['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($ladder=\App\Support\Safe::unserialize($review->ladder))
            <div class="mb-3">
              {!! Form::label('ladder',__('fleet.ladder')." : ", ['class' => 'form-label']) !!}
              @if($ladder['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($ladder['flag'] == 0 && $ladder['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$ladder['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($leed=\App\Support\Safe::unserialize($review->leed))
            <div class="mb-3">
              {!! Form::label('leed',__('fleet.leed')." : ", ['class' => 'form-label']) !!}
              @if($leed['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($leed['flag'] == 0 && $leed['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$leed['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($power_tool=\App\Support\Safe::unserialize($review->power_tool))
            <div class="mb-3">
              {!! Form::label('power_tool',__('fleet.power_tool')." : ", ['class' => 'form-label']) !!}
              @if($power_tool['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($power_tool['flag'] == 0 && $power_tool['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$power_tool['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($ac=\App\Support\Safe::unserialize($review->ac))
            <div class="mb-3">
              {!! Form::label('ac',__('fleet.ac')." : ", ['class' => 'form-label']) !!}
              @if($ac['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($ac['flag'] == 0 && $ac['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$ac['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($head_light=\App\Support\Safe::unserialize($review->head_light))
            <div class="mb-3">
              {!! Form::label('head_light',__('fleet.head_light')." : ", ['class' => 'form-label']) !!}
              @if($head_light['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($head_light['flag'] == 0 && $head_light['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$head_light['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($lock=\App\Support\Safe::unserialize($review->lock))
            <div class="mb-3">
              {!! Form::label('lock',__('fleet.lock')." : ", ['class' => 'form-label']) !!}
              @if($lock['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($lock['flag'] == 0 && $lock['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$lock['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($windows=\App\Support\Safe::unserialize($review->windows))
            <div class="mb-3">
              {!! Form::label('windows',__('fleet.windows')." : ", ['class' => 'form-label']) !!}
              @if($windows['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($windows['flag'] == 0 && $windows['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$windows['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($condition=\App\Support\Safe::unserialize($review->condition))
            <div class="mb-3">
              {!! Form::label('condition',__('fleet.condition')." : ", ['class' => 'form-label']) !!}
              @if($condition['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($condition['flag'] == 0 && $condition['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$condition['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            @php($oil_chk=\App\Support\Safe::unserialize($review->oil_chk))
            <div class="mb-3">
              {!! Form::label('oil_chk',__('fleet.oil_chk')." : ", ['class' => 'form-label']) !!}
              @if($oil_chk['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($oil_chk['flag'] == 0 && $oil_chk['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$oil_chk['text']}}
            </div>
          </div>

          <div class="col-md-6">
            @php($suspension=\App\Support\Safe::unserialize($review->suspension))
            <div class="mb-3">
              {!! Form::label('suspension',__('fleet.suspension')." : ", ['class' => 'form-label']) !!}
              @if($suspension['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($suspension['flag'] == 0 && $suspension['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$suspension['text']}}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            @php($tool_box=\App\Support\Safe::unserialize($review->tool_box))
            <div class="mb-3">
              {!! Form::label('tool_box',__('fleet.tool_box')." : ", ['class' => 'form-label']) !!}
              @if($tool_box['flag'] == 1) <i class="bi bi-check-lg fa-lg"></i> @endif
              @if($tool_box['flag'] == 0 && $tool_box['flag'] != null) <i class="bi bi-x-lg fa-lg"></i> @endif
              &nbsp; {{$tool_box['text']}}
            </div>
          </div>
        </div>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endsection