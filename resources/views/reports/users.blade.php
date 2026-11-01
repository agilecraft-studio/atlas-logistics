@extends('layouts.attex')
@section('page_title', __('fleet.user_report'))
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')

@section('content')

<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.user_report')" icon="bi-people">
      <div class="fleet-toolbar">
        {!! Form::open(['route' => 'reports.users','method'=>'post','class'=>'d-flex flex-wrap gap-2 align-items-end w-100']) !!}
          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('year', __('fleet.year1'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            {!! Form::select('year', $years, $year_select,['class'=>'form-control']) !!}</div>
          </div>
          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('month', __('fleet.month'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            {!! Form::selectMonth('month',$month_select,['class'=>'form-control']) !!}</div>
          </div>
          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('user', __('fleet.users'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            <select id="user_id" name="user_id" class="form-control" required>
              <option value="">@lang('fleet.selectUser')</option>
              @foreach($users as $user)
              <option value="{{ $user->id }}" @if($user['id']==$user_id) selected @endif>{{$user->name}}</option>
              @endforeach
            </select></div>
          </div>
          <button type="submit" class="fleet-btn" style="margin-right: 10px">@lang('fleet.generate_report')</button>
          <button type="submit" formaction="{{url('admin/print-users-report')}}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer"></i> @lang('fleet.print')</button>
          {!! Form::close() !!}
      </div>
    </x-fleet.panel>
  </div>
</div>
</div>

@if(isset($result))
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.report')" icon="bi-table">
      <div class="table-responsive">
        <table class="fleet-table"  id="myTable">
          <thead class="table-dark">
            <tr>
              <th>@lang('fleet.book_by')</th>
              <th>@lang('fleet.pickup_addr')</th>
              <th>@lang('fleet.dropoff_addr')</th>
              <th>@lang('fleet.pickup')</th>
              <th>@lang('fleet.dropoff')</th>
              <th>@lang('fleet.journey_status')</th>
              <th>@lang('fleet.amount')</th>
            </tr>
          </thead>

          <tbody>
            @foreach($data as $row)
            <tr>
              <td>{{$row->user?->name ?? '—'}}</td>
              <td style="width:10% !important">{!! str_replace(",", ",<br>", $row->pickup_addr) !!}</td>
              <td style="width:10% !important">{!! str_replace(",", ",<br>", $row->dest_addr) !!}</td>
              <td>
                @if($row->pickup != null)
                {{date($date_format_setting.' g:i A',strtotime($row->pickup))}}
                @endif
              </td>
              <td>
                @if($row->dropoff != null)
                {{date($date_format_setting.' g:i A',strtotime($row->dropoff))}}
                @endif
              </td>
              <td>
                @if($row->status == 1)
                  <span class="text-success">
                  @lang('fleet.completed')
                  </span>
                @else
                  <span class="text-warning">
                  @lang('fleet.not_completed')
                  </span>
                @endif
              </td>
              <td>
                @if($row->receipt == 1)
                {{AtlasLogistics::get('currency')}} {{($row->tax_total) ? $row->tax_total : $row->total}}
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>@lang('fleet.book_by')</th>
              <th>@lang('fleet.pickup_addr')</th>
              <th>@lang('fleet.dropoff_addr')</th>
              <th>@lang('fleet.pickup')</th>
              <th>@lang('fleet.dropoff')</th>
              <th>@lang('fleet.journey_status')</th>
              <th>@lang('fleet.amount')</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </x-fleet.panel>
  </div>
</div>
@endif
@endsection

@section('scripts')

<script type="text/javascript">
	$(document).ready(function() {
		$("#user_id").select2();
	});
</script>
<script type="text/javascript">
	$(document).ready(function() {
		$('#myTable tfoot th').each( function () {
      var title = $(this).text();
      $(this).html( '<input type="text" placeholder="'+title+'" />' );
    });
    var myTable = $('#myTable').DataTable({
      dom: 'Bfrtip',
      buttons: [{
           extend: 'collection',
              text: 'Export',
              buttons: [
                  'copy',
                  'excel',
                  'csv',
                  'pdf',
              ]}
      ],
      "language": {
               "url": '{{ asset("assets/datatables/")."/".__("fleet.datatable_lang") }}',
            },
      "initComplete": function() {
              myTable.columns().every(function () {
                var that = this;
                $('input', this.footer()).on('keyup change', function () {
                    that.search(this.value).draw();
                });
              });
            }
    });
	});
</script>
@endsection