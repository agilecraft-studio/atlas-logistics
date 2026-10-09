@extends('layouts.app')

@section('extra_css')

<link rel="stylesheet" type="text/css" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">

@endsection

@section("breadcrumb")

<li class="breadcrumb-item"><a href="{{ route('bookings.index')}}">@lang('menu.bookings')</a></li>

<li class="breadcrumb-item active">@lang('fleet.edit_booking')</li>

@endsection

@section('content')

<div class="row">

  <div class="col-md-12">

    <div class="card card-warning">

      <div class="card-header">

        <h3 class="card-title">@lang('fleet.assign_driver')

        </h3>

      </div>



      <div class="card-body">

        @if (count($errors) > 0)

        <div class="alert alert-danger">

          <ul>

            @foreach ($errors->all() as $error)

            <li>{{ $error }}</li>

            @endforeach

          </ul>

        </div>

        @endif

        <form method="post" action="{{route('update_assign_driver')}}">

          @csrf

          <input type="hidden" name="b_id" value="{{$data->id}}">

            <div class="row">

              <div class="col-4">

                <label>Select Driver</label>

                <select name="driver_id" class="form-control fa" id="vehicle_id"> 

                    <option>Select Driver</option>

                    @if(count($r['data']) > 0 )



                    @foreach($r['data'] as $ra)

                    <option value="{{$ra['id']}}"@if($ra['id'] == $data->driver_id) selected @endif class="fa">
                      {{$ra['text']}}
                      
                      @if($ra['is_active'] == 1)
                      (Enable)
                      @else
                      (Disable)
                      @endif

                    </option>

                    @endforeach



                    @else

                    <option>No Driver Found</option>

                    @endif

                </select>

              </div>



              <div class="col-4 pt-4">

                  <input type="submit" value="Assign Driver" class="btn btn-success">

              </div>

          </div>



      </form>



        @if(isset($v_model))

        <div class="row mt-4">

          <div class="col-12">

            <h6><b>Vehicle Info.</b></h6>

          </div>

          

            <div class="col-6">

                <table class="table">

                  

                  <tr>

                    <th>Vehicle Type</th>

                    <td>{{($v_type->vehicletype??'-')}}</td>

                  </tr>

                  <tr>

                      <th>Vehicle Name</th>

                      <td>{{($v_model->model_name??'-')}}</td>

                  </tr>

                  <tr>

                      <th>Engine Type</th>

                      <td>{{($v_model->engine_type??'-')}}</td>

                  </tr>

                  <tr>

                      <th>Image</th>

                      <td><img src="{{url('/uploads/'.$v_model->vehicle_image)}}" height=70px width=70px></td>

                  </tr>

                </table>

            </div>



            <div class="col-6">

              <table class="table">

                <tr>

                  <th>Company Name</th>

                  <td>{{($v_model->make_name??'-')}}</td>

              </tr>

              <tr>

                  <th>Color</th>

                  <td>{{($v_model->color_name??'-')}}</td>

              </tr>

              <tr>

                  <th>License Plate</th>

                  <td>{{($v_model->license_plate??'-')}}</td>

              </tr>

             

              </table>

          </div>



        </div>



        @endif



     



    </div>

  </div>

</div>



@endsection



@section("script")

<script>

  var getDriverRoute='{{ url("admin/get_driver") }}';

  var getVehicleRoute='{{ url("admin/get_vehicle") }}';

  var prevAddress='{{ url("admin/prev-address") }}';

  var selectDriver="@lang('fleet.selectDriver')";

  var selectCustomer="@lang('fleet.selectCustomer')";

  var selectVehicle="@lang('fleet.selectVehicle')";

  var addCustomer="@lang('fleet.add_customer')";

  var prevAddressLang="@lang('fleet.prev_addr')";

  var fleet_email_already_taken="@lang('fleet.email_already_taken')";

</script>

<script src="{{asset('assets/js/bookings/edit.js')}}"></script>

@if(AtlasLogistics::api('google_api') == "1")

<script>

  function initMap() {

    $('#pickup_addr').attr("placeholder","");

    $('#dest_addr').attr("placeholder","");

      // var input = document.getElementById('searchMapInput');

      var pickup_addr = document.getElementById('pickup_addr');

      new google.maps.places.Autocomplete(pickup_addr);



      var dest_addr = document.getElementById('dest_addr');

      new google.maps.places.Autocomplete(dest_addr);



  }

</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{AtlasLogistics::api('api_key')}}&libraries=places&callback=initMap"

  async defer></script>

@endif

@endsection