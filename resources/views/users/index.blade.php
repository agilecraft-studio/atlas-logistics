@extends('layouts.attex')

@section('styles')
<style type="text/css">
  .checkbox,
  #chk_all {
    width: 20px;
    height: 20px;
  }
  td>img {
    border-radius: 50%;
  }
</style>
@endsection
@section('page_title', __('fleet.users') . ' & ' . __('fleet.roles'))
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel>
      <div class="fleet-toolbar">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>{{ __('fleet.users') }} &amp; {{ __('fleet.roles') }}</h5>
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('users.index') }}" class="fleet-btn">@lang('fleet.users')</a>
          <a href="{{ route('roles.index') }}" class="fleet-btn fleet-btn-ghost">@lang('fleet.roles')</a>
          @can('Users add')
            <a href="{{route('users.create')}}" class="fleet-btn" title="@lang('fleet.addUser')"><i class="bi bi-plus-lg"></i> @lang('fleet.addUser')</a>
          @endcan
        </div>
      </div>
      <div class="table-responsive">
        <table class="fleet-table" id="ajax_data_table">
          <thead class="table-dark">
            <tr>
              <th>
                <input type="checkbox" id="chk_all">
              </th>
              <th>@lang('fleet.id')</th>
              <th>@lang('fleet.profile_photo')</th>
              <th>@lang('fleet.name')</th>
              <th>@lang('fleet.email')</th>
              <th>@lang('fleet.created')</th>
              <th>@lang('fleet.action')</th>
            </tr>
          </thead>
          <tbody>

          </tbody>
          <tfoot>
            <tr>
              <th>

                @can('Users delete')<button class="fleet-btn fleet-btn-danger" id="bulk_delete" data-bs-toggle="modal"
                  title="@lang('fleet.delete')" data-bs-target="#bulkModal" disabled>
                  <i class="bi bi-trash"></i></button>@endcan

              </th>
              <th>#</th>
              <th>@lang('fleet.profile_photo')</th>
              <th>@lang('fleet.name')</th>
              <th>@lang('fleet.email')</th>
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
<div id="bulkModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('fleet.delete')</h4>
        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {!! Form::open(['url'=>'admin/delete-users','method'=>'POST','id'=>'form_delete']) !!}
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
@endsection

@section('scripts')
<script type="text/javascript">
  // Action rows are injected by DataTables after fleet.js binds static forms.
  $(document).on('submit', 'form[data-fleet-confirm]', function (e) {
    if (!confirm($(this).attr('data-fleet-confirm') || 'Delete this record?')) {
      e.preventDefault();
    }
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
         ajax: {
          url: "{{ url('admin/users-fetch') }}",
          type: 'POST',
          data:{}
         },
         columns: [
            {data: 'check',name:'check', searchable:false, orderable:false},
            {data: 'id', name: 'id'},
            {data: 'profile_image',name:'profile_image', searchable:false, orderable:false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},            
            {data: 'created_at', name: 'created_at'},
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
@endsection
