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
        <h3 class="card-title">@lang('fleet.roles') &nbsp;
        <a href="{{ route('roles.create')}}" class="btn btn-success" title="@lang('fleet.addNew')"><i class="bi bi-plus-lg"></i></a></h3>
      </div>

      <div class="card-body table-responsive">
        <table class="table" id="data_table">
          <thead class="thead-inverse">
            <tr>
              
              <th>@lang('fleet.role')</th>
              {{-- <th>@lang('fleet.permission')</th> --}}
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $row)
              <tr>
                <td>
                  {{ $row->name }}
                </td>
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="bi bi-gear"></span>
                    <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu custom" role="menu">
                    <a class="dropdown-item" href="{{url("admin/roles/".$row->id."/edit") }}"> <span aria-hidden="true" class="bi bi-pencil-square" style="color: #f0ad4e;"></span> @lang('fleet.edit')</a>
                    {!! Form::hidden("id",$row->id) !!}

                    @if($row->id != 1)
                      <a class="dropdown-item" data-id="{{$row->id}}" data-toggle="modal" data-target="#myModal"> <span aria-hidden="true" class="bi bi-trash" style="color: #dd4b39"></span> @lang('fleet.delete')</a>
                    @endif
                    </div>
                  </div>
                  {!! Form::open(['url' => 'admin/roles/'.$row->id,'method'=>'DELETE','class'=>'form-horizontal','id'=>'form_'.$row->id]) !!}

                  {!! Form::hidden("id",$row->id) !!}

                  {!! Form::close() !!}
                </td>
              </tr>
            @endforeach
          </tbody>
          
        </table>
      </div>
    </div>
  </div>
</div>

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

</script>
@endsection