@extends('layouts.attex')
@section('content')
<div class="row">
  <div class="col-md-6">
  <!-- Profile Image -->
    <div class="card">
      <div class="card-body box-profile">
        <div class="text-center">
          @if($data->getMeta('driver_image') != null)
            @if(starts_with($data->getMeta('driver_image'),'http'))
              @php($src = $data->getMeta('driver_image'))
            @else
              @php($src=asset('uploads/'.$data->getMeta('driver_image')))
            @endif
            <img src="{{$src}}" class="profile-user-img img-responsive img-circle"  alt="User profile picture">
          @else
            <img src="{{ asset("assets/images/no-user.jpg")}}" alt="User profile picture" class="profile-user-img img-responsive img-circle">
          @endif
        </div>
        <h3 class="profile-username text-center"> {{$data->getMeta('first_name')}} {{ $data->getMeta('last_name')}}</h3>
        <a href="{{ url('admin/change-details/'.Auth::user()->id) }}" class="fleet-btn fleet-btn-ghost btn-block"><b>@lang('fleet.editProfile')</b></a>
      </div>
    </div>
    <!-- About Me Box -->
    <x-fleet.panel :title="__('fleet.about_me')">
        <strong><i class="bi bi-person margin-r-5"></i> @lang('fleet.personal_info')</strong>
        <p class="text-muted">
          @lang('fleet.driver') @lang('fleet.name'): {{$data->getMeta('first_name')}} {{$data->getMeta('middle_name')}} {{$data->getMeta('last_name')}}
          <br>
          @lang('fleet.phone'): {{$data->getMeta('phone')}}
          <br>
          @lang('fleet.email'): {{$data->email}}
          <br>
          @lang('fleet.address'): {{$data->getMeta('address')}}
        </p>
        <hr>
        <strong><i class="bi bi-file-earmark margin-r-5"></i> @lang('fleet.doc_info')</strong>

        <p class="text-muted">
          @lang('fleet.licenseNumber'): {{$data->getMeta('license_number')}}
          <br>
          @lang('fleet.issueDate'): {{$data->getMeta('issue_date')}}
          <br>
          @lang('fleet.expirationDate'): {{$data->getMeta('exp_date')}}
          <br>
          @lang('fleet.employee_id'): {{$data->getMeta('emp_id')}}
          <br>
          @lang('fleet.contract'): {{$data->getMeta('contract_number')}}
        </p>
        <hr>
        <p>
          @if($data->getMeta('license_image') != null)
          <a href="{{asset('uploads/'.$data->getMeta('license_image'))}}" target="_blank" class="fleet-btn fleet-btn-ghost">
          @lang('fleet.lic_photo')
          </a>
          @endif
        </p>
        <p>
          @if($data->getMeta('documents') != null)
            <a href="{{asset('uploads/'.$data->getMeta('documents'))}}" target="_blank" class="fleet-btn fleet-btn-ghost">
            @lang('fleet.documents')
            </a>
          @endif
        </p>
    </x-fleet.panel>
    <!-- /.box -->
  </div>
</div>
@endsection
