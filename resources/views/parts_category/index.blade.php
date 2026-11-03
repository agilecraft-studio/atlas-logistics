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
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-tags me-2"></i>{{ __('fleet.parts_category') }}</h5>
        <div class="d-flex gap-2">
          @can('PartsCategory add')
            <a href="{{ route('parts-category.create') }}" class="fleet-btn" title="@lang('fleet.addNew')"><i class="bi bi-plus-lg"></i> @lang('fleet.addNew')</a>
          @endcan
        </div>
      </div>

      <div class="table-responsive">
        <table class="fleet-table" id="data_table">
          <thead class="table-dark">
            <tr>
              <th>
                @if($records->count() > 0)
                  <input type="checkbox" id="chk_all">
                @endif
              </th>
              <th>@lang('fleet.category_name')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($records as $row)
            <tr>
              <td>
                <input type="checkbox" name="ids[]" value="{{ $row->id }}" class="checkbox" id="chk{{ $row->id }}" onclick='checkcheckbox();'>
              </td>
              <td>
                {{$row->name}}
              </td>
              <td>
                <div class="d-flex gap-1">
                  @can('PartsCategory edit')
                    <a href="{{ url('admin/parts-category/'.$row->id.'/edit') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')"><i class="bi bi-pencil"></i></a>
                  @endcan
                  @can('PartsCategory delete')
                    <form method="POST" action="{{ url('admin/parts-category/'.$row->id) }}" class="d-inline" data-fleet-confirm="@lang('fleet.confirm_delete')">
                      @csrf
                      @method('DELETE')
                      <input type="hidden" name="id" value="{{ $row->id }}">
                      <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="@lang('fleet.delete')"><i class="bi bi-trash"></i></button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>
                @if($records->count() > 0)
                @can('PartsCategory delete')<button class="fleet-btn fleet-btn-danger" id="bulk_delete" data-bs-toggle="modal" data-bs-target="#bulkModal" disabled title="@lang('fleet.delete')" ><i class="bi bi-trash"></i></button>@endcan
                @endif
              </th>
              <th>@lang('fleet.category_name')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </x-fleet.panel>
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
        {!! Form::open(['url'=>'admin/delete-parts-category','method'=>'POST','id'=>'form_delete']) !!}
        <div id="bulk_hidden"></div>
        <p>@lang('fleet.confirm_bulk_delete')</p>
      </div>
      <div class="modal-footer">
        <button id="bulk_action" class="fleet-btn fleet-btn-danger" type="submit" data-submit="">@lang('fleet.delete')</button>
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
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
        <button id="del_btn" class="fleet-btn fleet-btn-danger" type="button" data-submit="">@lang('fleet.delete')</button>
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
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