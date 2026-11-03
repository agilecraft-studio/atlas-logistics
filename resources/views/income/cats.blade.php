@extends('layouts.attex')
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')

@section('content')
<div class="row">
  <div class="col-md-12">
    @if (count($errors) > 0)
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>{{ __('fleet.incomeCategories') }}</h5>
        <div class="d-flex gap-2">
          <a href="{{ route('incomecategories.create') }}" class="fleet-btn" title="@lang('fleet.addNew')"><i class="bi bi-plus-lg"></i> @lang('fleet.addNew')</a>
          <button data-bs-toggle="modal" data-bs-target="#import" class="fleet-btn fleet-btn-ghost">@lang('fleet.import')</button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="fleet-table" id="data_table">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>@lang('fleet.name')</th>
              <th>@lang('fleet.created')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $row)
            <tr>
              <td>{{$row->id}}</td>
              <td>{{$row->name}}</td>
              <td>{{date($date_format_setting,strtotime($row->created_at))}}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ url('admin/incomecategories/'.$row->id.'/edit') }}" class="fleet-btn fleet-btn-sm fleet-btn-ghost" title="@lang('fleet.edit')"><i class="bi bi-pencil"></i></a>
                  @if($row->type == 'u')
                    <form method="POST" action="{{ url('admin/incomecategories/'.$row->id) }}" class="d-inline" data-fleet-confirm="@lang('fleet.confirm_delete')">
                      @csrf
                      @method('DELETE')
                      <input type="hidden" name="id" value="{{ $row->id }}">
                      <button type="submit" class="fleet-btn fleet-btn-sm fleet-btn-danger" title="@lang('fleet.delete')"><i class="bi bi-trash"></i></button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>#</th>
              <th>@lang('fleet.name')</th>
              <th>@lang('fleet.created')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </x-fleet.panel>
  </div>
</div>

<!-- Modal -->
<div id="import" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.importIncome')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {!! Form::open(['url'=>'admin/import-income','method'=>'POST','files'=>true]) !!}
        <div class="mb-3">
          {!! Form::label('excel',__('fleet.importIncome'),['class'=>"form-label"]) !!}
          {!! Form::file('excel',['class'=>"form-control",'required']) !!}
        </div>
        <div class="mb-3">
          <a href="{{ asset('assets/samples/income.xlsx') }}">@lang('fleet.downloadSampleExcel')</a>
        </div>
        <div class="mb-3">
          <h6 class="text-muted">@lang('fleet.note'):</h6>
          <ul class="text-muted">
            <li>@lang('fleet.fileTypeNote')</li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button class="fleet-btn" type="submit">@lang('fleet.import')</button>
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
</script>
@endsection