@extends('layouts.attex')

@section('styles')
<style type="text/css">
  .checkbox, #chk_all{
    width: 20px;
    height: 20px;
  }
  .show-password-button{
    outline: none;
    border: 1px solid #ced4da;
  }
</style>
@endsection
@section('page_title', __('fleet.manage_customers'))
@section('content')
<div class="row">
  <div class="col-md-12">
    @if (count($errors ?? []) > 0)
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
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>{{ __('fleet.manage_customers') }}</h5>
        <div class="d-flex gap-2">
          @can('Customer add')
            <a href="{{route('customers.create')}}" class="fleet-btn" title="@lang('fleet.add_new')"><i class="bi bi-plus-lg"></i> @lang('fleet.add_new')</a>
          @endcan
          @can('Customer import')
            <button data-bs-toggle="modal" data-bs-target="#import" class="fleet-btn fleet-btn-ghost">@lang('fleet.import')</button>
          @endcan
        </div>
      </div>
      <div class="table-responsive">
        <table class="fleet-table" id="ajax_data_table" style="padding-bottom: 25px">
          <thead class="table-dark">
            <tr>
              <th>
                <input type="checkbox" id="chk_all">
              </th>
              <th>@lang('fleet.name')</th>
              <th>@lang('fleet.email')</th>
              <th>@lang('fleet.phone')</th>
              <th>@lang('fleet.gender')</th>
              <th>@lang('fleet.address')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>
            
          </tbody>
          <tfoot>
            <tr>
              <th>
                @can('Customer delete')<button class="fleet-btn fleet-btn-danger" id="bulk_delete" data-bs-toggle="modal" data-bs-target="#bulkModal" disabled title="@lang('fleet.delete')"><i class="bi bi-trash"></i></button>@endcan
              </th>
              <th>@lang('fleet.name')</th>
              <th>@lang('fleet.email')</th>
              <th>@lang('fleet.phone')</th>
              <th>@lang('fleet.gender')</th>
              <th>@lang('fleet.address')</th>
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
        <h4 class="modal-title">@lang('fleet.importCustomers')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {!! Form::open(['url'=>'admin/import-customers','method'=>'POST','files'=>true]) !!}
        <div class="mb-3">
          {!! Form::label('excel',__('fleet.importCustomers'),['class'=>"form-label"]) !!}
          {!! Form::file('excel',['class'=>"form-control",'required']) !!}
        </div>
        <div class="mb-3">
          <a href="{{ asset('assets/samples/customers.xlsx') }}">@lang('fleet.downloadSampleExcel')</a>
        </div>
        <div class="mb-3">
          <h6 class="text-muted">@lang('fleet.note'):</h6>
          <ul class="text-muted">
            <li>@lang('fleet.customerImportNote')</li>
            <li>@lang('fleet.excelNote')</li>
            <li>@lang('fleet.fileTypeNote')</li>
            <li>@lang('fleet.skipNote')</li>
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
<div id="bulkModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.delete')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {!! Form::open(['url'=>'admin/delete-customer','method'=>'POST','id'=>'form_delete']) !!}
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
<div id="changepass" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      {!! Form::open(['url'=>url('admin/change_password'),'id'=>'changepass_form']) !!}
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.change_password')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {!! Form::hidden('driver_id',"",['id'=>'driver_id'])!!}
        <div class="mb-3">
          {!! Form::label('passwd',__('fleet.password'),['class'=>"form-label"]) !!}
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            {!! Form::password('passwd',['class'=>"form-control",'id'=>'passwd','required']) !!}
            <button type="button" id="show-password-button" class="btn btn-outline-secondary"><i class="bi bi-eye" aria-hidden="true"></i></button>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button id="password" class="fleet-btn" type="submit">@lang('fleet.change_password')</button>
        <button type="button" class="fleet-btn fleet-btn-ghost" data-bs-dismiss="modal">@lang('fleet.close')</button>
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>
<!-- Modal -->


@endsection

@section('scripts')

<script type="text/javascript">

  $('#changepass').on('show.bs.modal', function(e) {
    var id = e.relatedTarget.dataset.id;
    $("#driver_id").val(id);
  });

   $("#changepass_form").on("submit",function(e){
    $.ajax({
      type: "POST",
      url: $(this).attr("action"),
      data: $(this).serialize(),
      success: function(data){

       new PNotify({
            title: 'Success!',
            text: "@lang('fleet.passwordChanged')",
            type: 'info'
        });
      },

      dataType: "html"
    });
    bootstrap.Modal.getOrCreateInstance(document.getElementById('changepass')).hide();
    e.preventDefault();
  });

  $(function(){
    
    var table = $('#ajax_data_table').DataTable({
      dom: 'Bfrtip',
      buttons: [
          {
        extend: 'print',
        text: '<i class="bi bi-printer"></i> {{__("fleet.print")}}',

        exportOptions: {
           columns: ([1,2,3,4,5]),
        },
        customize: function ( win ) {
                
                $(win.document.body).find( 'table' )
                    .addClass( 'table-bordered' );
                // $(win.document.body).find( 'td' ).css( 'font-size', '10pt' );

            },
            
          },
          {
            extend: 'excel',
            text: '<i class="bi bi-file-earmark-spreadsheet"></i> Excel',
            exportOptions: {
                columns: [1, 2, 3, 4, 5]
            }
        }
    ],
          "language": {
              "url": '{{ asset("assets/datatables/")."/".__("fleet.datatable_lang") }}',
          },
         processing: true,
         serverSide: true,
        //  stateSave: true,
         ajax: {
          url: "{{ url('admin/customers-fetch') }}",
          type: 'POST',
          data:{}
         },
         columns: [
            {data: 'check',name:'check', searchable:false, orderable:false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},            
            {data: 'mobno', name: 'user_data.value'},            
            {data: 'gender', name: 'user_data.value'},            
            {data: 'address', name: 'user_data.value'},
            {data: 'action',name:'action',  searchable:false, orderable:false}
        ],
        order: [[1, 'desc']],
        "initComplete": function() {
              table.columns().every(function () {
                var that = this;
                $('input', this.footer()).on('keyup change', function () {
                  // console.log($(this).parent().index());
                    that.search(this.value).draw();
                });
              });
            }
    });
  });
  $(document).on('click','input[type="checkbox"]',function(){
    if(this.checked){
      $('#bulk_delete').prop('disabled',false);

    }else { 
      if($("input[name='ids[]']:checked").length == 0){
        $('#bulk_delete').prop('disabled',true);
      } 
    } 
    
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
      $('#bulk_delete').prop('disabled',true);
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
{{-- show password script --}}
<script>
  $(document).ready(function() {
  $('#show-password-button').click(function() {
    $('#show-password-button').show();
    var passwordField = $('#passwd');
    var fieldType = passwordField.attr('type');
    if (fieldType === 'password') {
      passwordField.attr('type', 'text');
      $(this).attr('title', 'Hide password');
      $(this).find('i').removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
      passwordField.attr('type', 'password');
      $(this).attr('title', 'Show password');
      $(this).find('i').removeClass('bi-eye-slash').addClass('bi-eye');
    }
  });
});

</script>
{{-- show password script end --}}
@endsection