@extends('layouts.attex')
@section('styles')
<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datepicker.min.css')}}">
<style type="text/css">
  .checkbox,
  #chk_all {
    width: 20px;
    height: 20px;
  }
</style>
@endsection
@section('page_title', __('fleet.transactions'))
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.transactions')" icon="bi-credit-card">
      <div class="fleet-toolbar">
        <div class="text-muted">{{ __('fleet.transactions') }}</div>
      </div>
      <div class="table-responsive">
        <table class="fleet-table" id="ajax_data_table" style="padding-bottom: 35px; width: 100%">
          <thead class="table-dark">
              <tr>
                <th>@lang('fleet.id')</th>
                <th>@lang('fleet.customer')</th>
                <th>@lang('fleet.method')</th>
                <th>@lang('fleet.payment_id')</th>
                <th>@lang('fleet.status')</th>
                <th>@lang('fleet.amount')</th>
                <th>@lang('fleet.date')</th>
              </tr>
            </thead>
            <tbody>
              @foreach($data as $row)
              <tr>
                <td>{{$row->id}}</td>
                <td>
                  {{$row->booking?->customer?->name ?? '—'}}
                </td>
                <td>{{$row->method}}</td>
                <td>{{$row->transaction_id}}</td>
                <td>{{$row->payment_status}}</td>
                <td>{{AtlasLogistics::get('currency').' '. $row->amount}}</td>
                <td>{{date('d-m-Y g:i A',strtotime($row->created_at))}}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th>@lang('fleet.id')</th>
                <th>@lang('fleet.customer')</th>
                <th>@lang('fleet.method')</th>
                <th>@lang('fleet.payment_id')</th>
                <th>@lang('fleet.status')</th>
                <th>@lang('fleet.amount')</th>
                <th>@lang('fleet.date')</th>
              </tr>
            </tfoot>
          </table>
        </div>
    </x-fleet.panel>
  </div>
</div>
@endsection
@section('scripts')
<script>
  $(function(){
    
    var table = $('#ajax_data_table').DataTable({      
      "language": {
          "url": '{{ asset("assets/datatables/")."/".__("fleet.datatable_lang") }}',
      },
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ url('admin/transactions-fetch') }}",
        type: 'POST',
        data:{}
      },
      columns: [
        {data: 'id',   name: 'id'},
        {data: 'customer',   name: 'booking.customer.name'},
        {data: 'method',  name: 'method'},
        {data: 'transaction_id',  name: 'transaction_id'},
        {data: 'payment_status',  name: 'payment_status'},
        {data: 'amount',  name: 'amount'},
        {name: 'created_at',data: {_: 'created_at.display',sort: 'created_at.timestamp'}}
      ],
      order: [[0, 'desc']],
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
</script>
@endsection