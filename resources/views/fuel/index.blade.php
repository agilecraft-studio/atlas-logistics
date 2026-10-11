@extends('layouts.fleet')
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')
@php($currency=AtlasLogistics::get('currency'))
@section('styles')
<style type="text/css">
  .checkbox,
  #chk_all {
    width: 20px;
    height: 20px;
  }
</style>
@endsection
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          @lang('fleet.manageFuel')
          &nbsp;
          @can('Fuel add')<a href="{{ route('fuel.create')}}" class="btn btn-success"  title="@lang('fleet.addNew')"><i class="bi bi-plus-lg"></i></a>@endcan
        </h3>
      </div>

      <div class="card-body table-responsive">
        <table class="table" id="data_table">
          <thead class="thead-inverse">
            <tr>
              <th>
                @if($data->count() > 0)
                <input type="checkbox" id="chk_all">
                @endif
              </th>
              <th>@lang('fleet.vehicleImage')</th>
              <th>@lang('fleet.vehicle_name')</th>
              <th>@lang('fleet.date')</th>
              <th>@lang('fleet.qty')</th>
              <th>@lang('fleet.cost')</th>
              <th>@lang('fleet.meter')</th>
              <th>@lang('fleet.consumption')</th>
              <th>@lang('fleet.province')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $row)
            <tr>
              <td>
                <input type="checkbox" name="ids[]" value="{{ $row->id }}" class="checkbox" id="chk{{ $row->id }}"
                  onclick='checkcheckbox();'>
              </td>
              <td>
                @if($row->vehicle_data['vehicle_image'] != null)
                <img src="{{asset('uploads/'.$row->vehicle_data['vehicle_image'])}}" height="70px" width="70px">
                @else
                <img src="{{ asset('assets/images/vehicle.jpeg')}}" height="70px" width="70px">
                @endif
              </td>
              <td>
                <a href="{{ url("admin/vehicles/".$row->vehicle_id."/edit")}}">
                  {{$row->vehicle_data['year']}}
                  {{$row->vehicle_data->make_name}}-{{$row->vehicle_data->model_name}}
                </a>
                <br>
                <b>@lang('fleet.vin'):</b> {{$row->vehicle_data['vin']}}
              </td>
              <td>{{date($date_format_setting,strtotime($row->date))}}</td>
              <td> {{$row->qty}} @if(AtlasLogistics::get('fuel_unit') == "gallon") @lang('fleet.gal') @else Liter @endif </td>
              <td>
                @php ($total = $row->qty * $row->cost_per_unit)
                {{ $currency }} {{$total}}
                <br>
                {{ $currency }} {{$row->cost_per_unit}}/ {{ AtlasLogistics::get('fuel_unit') }}
              </td>
              <td>
                @lang('fleet.start'): {{$row->start_meter}} {{AtlasLogistics::get('dis_format')}}
                <br>
                @lang('fleet.end'): {{$row->end_meter}} {{AtlasLogistics::get('dis_format')}}
                <br>
                @lang('fleet.distence'):

                @if($row->end_meter == 0)
                0.00 {{AtlasLogistics::get('dis_format')}}
                @else
                {{$row->end_meter - $row->start_meter}} {{AtlasLogistics::get('dis_format')}}
                @endif
              </td>
              <td>
                {{ $row->consumption }}
                @if(AtlasLogistics::get('dis_format') == "km")
                @if(AtlasLogistics::get('fuel_unit') == "gallon")KMPG @else KMPL @endif
                @else
                @if(AtlasLogistics::get('fuel_unit') == "gallon")MPG @else MPL @endif
                @endif
              </td>
              <td>
                {{$row->province}}
              </td>
              <td>
                <div class="btn-group">
                  <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="bi bi-gear"></span>
                    <span class="visually-hidden">Toggle Dropdown</span>
                  </button>
                  <div class="dropdown-menu custom" role="menu">
                    @can('Fuel edit')<a class="dropdown-item" href="{{ url("admin/fuel/".$row->id."/edit")}}"> <span
                        aria-hidden="true" class="bi bi-pencil-square" style="color: #f0ad4e;"></span>
                      @lang('fleet.edit')</a>@endcan
                    @can('Fuel delete')<a class="dropdown-item" data-id="{{$row->id}}" data-toggle="modal"
                      data-target="#myModal"><span aria-hidden="true" class="bi bi-trash" style="color: #dd4b39"></span>
                      @lang('fleet.delete')</a>@endcan
                  </div>
                </div>
                {!! Form::open(['url' =>
                'admin/fuel/'.$row->id,'method'=>'DELETE','class'=>'form-horizontal','id'=>'form_'.$row->id]) !!}
                {!! Form::hidden("id",$row->id) !!}
                {!! Form::close() !!}
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>
                @if($data->count() > 0)
                @can('Fuel delete')
                <button class="btn btn-danger" id="bulk_delete" data-bs-toggle="modal" data-bs-target="#bulkModal" disabled title="@lang('fleet.delete')"><i class="bi bi-trash"></i></button>
                @endcan
                @endif
              </th>
              <th>@lang('fleet.vehicleImage')</th>
              <th>@lang('fleet.vehicle_name')</th>
              <th>@lang('fleet.date')</th>
              <th>@lang('fleet.qty')</th>
              <th>@lang('fleet.cost')</th>
              <th>@lang('fleet.meter')</th>
              <th>@lang('fleet.consumption')</th>
              <th>@lang('fleet.province')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="bulkModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.delete')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      {!! Form::open(['url'=>'admin/delete-fuel/','method'=>'POST','id'=>'form_delete']) !!}
      <div class="modal-body">
        <div id="bulk_hidden"></div>
        <p>@lang('fleet.confirm_bulk_delete')</p>
      </div>
      <div class="modal-footer">
        
        <button id="bulk_action" class="btn btn-danger" type="submit" data-submit="">@lang('fleet.delete')</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>
<!-- Modal -->

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.delete')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>@lang('fleet.confirm_delete')</p>
      </div>
      <div class="modal-footer">
        <button id="del_btn" class="btn btn-danger" type="button" data-submit="">@lang('fleet.delete')</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
@endsection

@section('scripts')
<script type="text/javascript">
  $("#del_btn").on("click",function(){
    var id=$(this).data("submit");
    $("#form_"+id).submit();
  });

  $('#myModal').on('show.bs.modal', function(e) {
    var id = e.relatedTarget.dataset.id;
    $("#del_btn").attr("data-submit",id);
  });

  $('input[type="checkbox"]').on('click',function(){
    $('#bulk_delete').removeAttr('disabled');
  });

  $('#bulk_delete').on('click',function(){
    // console.log($( "input[name='ids[]']:checked" ).length);
    if($( "input[name='ids[]']:checked" ).length == 0){
      $('#bulk_delete').prop('type','button');
        new PNotify({
            title: 'Failed!',
            text: "@lang('fleet.delete_error')",
            type: 'error'
          });
        $('#bulk_delete').attr('disabled',true);
    }
    if($("input[name='ids[]']:checked").length > 0){
      // var favorite = [];
      $.each($("input[name='ids[]']:checked"), function(){
          // favorite.push($(this).val());
          $("#bulk_hidden").append('<input type=hidden name=ids[] value='+$(this).val()+'>');
      });
      // console.log(favorite);
    }
  });


  $('#chk_all').on('click',function(){
    if(this.checked){
      $('.checkbox').each(function(){
        $('.checkbox').prop("checked",true);
      });
    }else{
      $('.checkbox').each(function(){
        $('.checkbox').prop("checked",false);
      });
    }
  });

  // Checkbox checked
  function checkcheckbox(){
    // Total checkboxes
    var length = $('.checkbox').length;
    // Total checked checkboxes
    var totalchecked = 0;
    $('.checkbox').each(function(){
        if($(this).is(':checked')){
            totalchecked+=1;
        }
    });
    // console.log(length+" "+totalchecked);
    // Checked unchecked checkbox
    if(totalchecked == length){
        $("#chk_all").prop('checked', true);
    }else{
        $('#chk_all').prop('checked', false);
    }
  }
</script>
@endsection