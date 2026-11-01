@extends('layouts.attex')
@section('page_title', __('fleet.monthlyReport'))
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.monthlyReport')" icon="bi-calendar-month">
      <div class="fleet-toolbar">
        {!! Form::open(['route' => 'reports.monthly','method'=>'post','class'=>'d-flex flex-wrap gap-2 align-items-end w-100']) !!}
          <div class="mb-0" style="margin-right:10px">
            {!! Form::label('year', __('fleet.year'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            {!! Form::select('year', $years, $year_select,['class'=>'form-control']) !!}</div>
          </div>
          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('month', __('fleet.month'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            {!! Form::selectMonth('month',$month_select,['class'=>'form-control']) !!}</div>
          </div>
          <div class="mb-0" style="margin-right: 10px">
            {!! Form::label('vehicle', __('fleet.vehicles'), ['class' => 'form-label']) !!}
            <div class="mb-0" style="margin-left:5px;">
            <select id="vehicle_id" name="vehicle_id" class="form-control vehicles" style="width: 250px">
              <option value="">@lang('fleet.selectVehicle')</option>
              @foreach($vehicles as $vehicle)
              <option value="{{ $vehicle->id }}" @if($vehicle->id == $vehicle_select) selected @endif>{{$vehicle->make_name}}-{{$vehicle->model_name}}-{{$vehicle->license_plate}}</option>
              @endforeach
            </select></div>
          </div>
          <button type="submit" class="fleet-btn" style="margin-right: 10px">@lang('fleet.generate_report')</button>
          <button type="submit" formaction="{{url('admin/print-monthly-report')}}" class="fleet-btn fleet-btn-ghost"><i class="bi bi-printer"></i> @lang('fleet.print')</button>
        {!! Form::close() !!}
      </div>
    </x-fleet.panel>
  </div>
</div>

@if(isset($result))
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.report')" icon="bi-table">
      <div class="row">
        <div class="col-md-4">
          <x-fleet.panel :title="__('fleet.chart') . ' - ' . __('fleet.income')" icon="bi-pie-chart">
            <canvas id="canvas1" width="400" height="500"></canvas>
          </x-fleet.panel>
            <div class="table-responsive">
              <table class="fleet-table myTable">
                @php ($income_amt = (is_null($income[0]->income) ? 0 : $income[0]->income))
                @php ($expense_amt = (is_null($expenses[0]->expense) ? 0 : $expenses[0]->expense))
                <thead class="table-dark">
                  <tr>
                    <th scope="row">@lang('fleet.pl')</th>

                    <td><strong>{{ AtlasLogistics::get("currency")}}{{ $income_amt-$expense_amt}}</strong></td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row">@lang('fleet.income')</th>
                    <td>{{ AtlasLogistics::get("currency")}}{{$income_amt}}</td>
                    </tr>
                    <tr>
                    <th scope="row">@lang('fleet.expenses')</th>
                    <td>{{ AtlasLogistics::get("currency")}}{{$expense_amt}}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="col-md-4">
            <x-fleet.panel :title="__('fleet.chart') . ' - ' . __('fleet.incomeByCategory')" icon="bi-pie-chart">
              <canvas id="canvas2" width="400" height="500"></canvas>
            </x-fleet.panel>
            <div class="table-responsive">
              <table class="fleet-table myTable">
                @php($tot = 0)
                @foreach ($income_by_cat as $exp)
                @php($tot = $tot + ($exp->driver_amount??$exp->amount))
                @endforeach
                <thead class="table-dark">
                  <tr>
                    <th scope="row">@lang('fleet.incomeByCategory')</th>
                    <td><strong>{{ AtlasLogistics::get("currency")}}{{$tot}}</strong></td>
                  </tr>
                </thead>
                <tbody>
                  @foreach($income_by_cat as $exp)
                  <tr>
                    <th scope="row">{{$income_cats[$exp->income_cat]}}</th>
                    <td>{{ AtlasLogistics::get("currency")}}{{$exp->driver_amount??$exp->amount}}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <div class="col-md-4">
            <x-fleet.panel :title="__('fleet.chart') . ' - ' . __('fleet.expensesByCategory')" icon="bi-pie-chart">
              <canvas id="canvas3" width="400" height="500"></canvas>
            </x-fleet.panel>
            <div class="table-responsive">
              <table class="fleet-table expTable">
                <thead class="table-dark">
                  <tr>
                    @php($tot = 0)
                    @foreach ($expense_by_cat as $exp)
                    @php($tot = $tot + $exp->expense)
                    @endforeach
                    <th scope="row">@lang('fleet.expensesByCategory')</th>
                    <td><strong>{{ AtlasLogistics::get("currency")}}{{$tot}}</strong></td>
                  </tr>
                </thead>
                <tbody>
                  @foreach($expense_by_cat as $exp)
                  <tr>
                    <th scope="row">
                    @if($exp->type == "s")
                    {{$service[$exp->expense_type]}}
                    @else
                    {{$expense_cats[$exp->expense_type]}}
                    @endif
                    </th>
                    <td>{{ AtlasLogistics::get("currency")}}{{$exp->expense}}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
    </x-fleet.panel>
  </div>
</div>
@endif

@endsection

@section('scripts')
<script>
window.chartColors = {
  red: 'rgb(255, 99, 132)',
  orange: 'rgb(255, 159, 64)',
  yellow: 'rgb(255, 205, 86)',
  green: 'rgb(75, 192, 192)',
  blue: 'rgb(54, 162, 235)',
  purple: 'rgb(153, 102, 255)',
  grey: 'rgb(201, 203, 207)',
  black: 'rgb(0,0,0)'
};
function random_color(i){
  var color1,color2,color3;
  var col_arr=[];
  for(x=0;x<=i;x++){

  var c1 = [176,255,84,220,134,66,238];
  var c2 = [254,61,147,114,51,26,137];
  var c3 = [27,111,153,93,157,216,187,44,243];
  color1 = c1[Math.floor(Math.random()*c1.length)];
  color2 = c2[Math.floor(Math.random()*c2.length)];
  color3 = c3[Math.floor(Math.random()*c3.length)];

  col_arr.push("rgba("+color1+","+color2+","+color3+",0.5)");
  }
  return col_arr;
}

var chartData = {
    labels: ["@lang('fleet.income')", "@lang('fleet.expenses')"],
    datasets: [{
        type: 'pie',
        label: '',
        backgroundColor: [window.chartColors.green,window.chartColors.red],
        borderColor: window.chartColors.black,
        borderWidth: 1,
        data: [{{@$income_amt}},{{@$expense_amt}}]
    }]
};

var chartData2 = {
  labels: [@foreach($income_by_cat as $exp) "{{$income_cats[$exp->income_cat]}}", @endforeach],
  datasets: [{
      type: 'pie',
      label: '',
      backgroundColor: random_color({{count($income_by_cat)}}),
      borderColor: window.chartColors.black,
      borderWidth: 1,
      data: [@foreach($income_by_cat as $exp) {{$exp->driver_amount??$exp->amount}}, @endforeach]
  }]
};

var chartData3 = {
    labels: [@foreach($expense_by_cat as $exp) "@if($exp->type == "s"){{$service[$exp->expense_type]}} @else{{$expense_cats[$exp->expense_type]}}@endif", @endforeach],
    datasets: [{
        type: 'pie',
        label: '',
        backgroundColor: random_color({{count($expense_by_cat)}}),
        borderColor: window.chartColors.black,
        borderWidth: 1,
        data: [@foreach($expense_by_cat as $exp) {{$exp->expense}}, @endforeach]
    }]
};

window.onload = function() {
    var ctx = document.getElementById("canvas1").getContext("2d");
    window.myMixedChart = new Chart(ctx, {
        type: 'pie',
        data: chartData,
        options: {

            responsive: true,
            title: {
                display: false,
                text: "@lang('fleet.chart')"
            },
            tooltips: {
                mode: 'index',
                intersect: true
            }
        }
    });
    var ctx = document.getElementById("canvas3").getContext("2d");
    window.myMixedChart = new Chart(ctx, {
        type: 'pie',
        data: chartData3,
        options: {

            responsive: true,
            title: {
                display: false,
                text: "@lang('fleet.chart')"
            },
            tooltips: {
                mode: 'index',
                intersect: true
            }
        }
    });

    var ctx = document.getElementById("canvas2").getContext("2d");
    window.myMixedChart = new Chart(ctx, {
        type: 'pie',
        data: chartData2,
        options: {

            responsive: true,
            title: {
                display: false,
                text: "@lang('fleet.chart')"
            },
            tooltips: {
                mode: 'index',
                intersect: true
            }
        }
    });
};

</script>

<script type="text/javascript">
  $(document).ready(function() {
    $('#vehicle_id').select2();
      $('.myTable').DataTable({
        "paging":   false,
        "ordering": false,
        "searching": false,
        "info":     false,

        dom: 'Bfrtip',
        buttons: [{
             extend: 'collection',
                text: '@lang('fleet.Export')',
                buttons: [
                    'copy',
                    'excel',
                    'csv',
                    'pdf',
                ]}
        ],
        "language": {
                 "url": '{{ asset("assets/datatables/")."/".__("fleet.datatable_lang") }}',
              }
    });

    $('.expTable').DataTable({
      "ordering": false,
      "searching": false,
      "info":     false,
      "pageLength": 5,
      dom: 'Bfrtip',
      buttons: [{
           extend: 'collection',
              text: '@lang('fleet.Export')',
              buttons: [
                  'copy',
                  'excel',
                  'csv',
                  'pdf',
              ]}
      ],
      "language": {
               "url": '{{ asset("assets/datatables/")."/".__("fleet.datatable_lang") }}',
            }
    });
  });
</script>
@endsection