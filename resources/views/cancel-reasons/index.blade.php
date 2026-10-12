@extends('layouts.attex')
@section('styles')
<style type="text/css">
  .checkbox, #chk_all{
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
        @lang('fleet.cancellation')
        &nbsp;
        <a href="{{ route('cancel-reason.create')}}" class="btn btn-success" title="@lang('fleet.addNew')"><i class="bi bi-plus-lg"></i></a></h3>
      </div>

      <div class="card-body table-responsive">
        <table class="table" id="data_table">
          <thead class="thead-inverse">
            <tr>
              <th>
                @if($reasons->count() > 0)
                  <input type="checkbox" id="chk_all">
                @endif
              </th>
              <th>@lang('fleet.reason')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($reasons as $reason)
            <tr>
              <td>
                <input type="checkbox" name="ids[]" value="{{ $reason->id }}" class="checkbox" id="chk{{ $reason->id }}" onclick='checkcheckbox();'>
              </td>
              <td>{{$reason->reason}}</td>
              <td>
              <div class="btn-group">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                  <span class="bi bi-gear"></span>
                  <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu custom" role="menu">
                  <a class="dropdown-item" href="{{ url("admin/cancel-reason/".$reason->id."/edit")}}"> <span aria-hidden="true" class="bi bi-pencil-square" style="color: #f0ad4e;"></span> @lang('fleet.edit')</a>
                  <a class="dropdown-item" data-id="{{$reason->id}}" data-toggle="modal" data-target="#myModal" ><span aria-hidden="true" class="bi bi-trash" style="color: #dd4b39"></span> @lang('fleet.delete')</a>
                </div>
              </div>
              {!! Form::open(['url' => 'admin/cancel-reason/'.$reason->id,'method'=>'DELETE','class'=>'form-horizontal','id'=>'form_'.$reason->id]) !!}
              {!! Form::hidden("id",$reason->id) !!}
              {!! Form::close() !!}
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>
                @if($reasons->count() > 0)
                  <button class="btn btn-danger" id="bulk_delete" data-bs-toggle="modal" data-bs-target="#bulkModal" disabled title="@lang('fleet.delete')" ><i class="bi bi-trash"></i></button>
                @endif
              </th>
              <th>@lang('fleet.reason')</th>
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
      <div class="modal-body">
        {!! Form::open(['url'=>'admin/delete-reasons','method'=>'POST','id'=>'form_delete']) !!}
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