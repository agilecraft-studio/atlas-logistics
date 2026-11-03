@extends('layouts.attex')
@section('page_title', __('fleet.editUser') . ' ' . __('fleet.managers'))
@section('content')
<div class="row">
  <div class="col-md-12">
    <x-fleet.panel :title="__('fleet.editUser') . ' ' . __('fleet.managers')" icon="bi-person-gear">
        @if (count($errors) > 0)
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        @php($names = explode(" ", $user->name))

        {!! Form::open(['route' => ['users.update',$user->id],'files'=>true,'method'=>'PATCH']) !!}
        {!! Form::hidden('id',$user->id) !!}
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('first_name', __('fleet.firstname'), ['class' => 'form-label']) !!}
              {!! Form::text('first_name', $names[0],['class' => 'form-control','required']) !!}
            </div>

            <div class="mb-3">
              {!! Form::label('last_name', __('fleet.lastname'), ['class' => 'form-label']) !!}
              {!! Form::text('last_name', $names[1],['class' => 'form-control','required']) !!}
            </div>

            <div class="mb-3">
              {!! Form::label('group_id',__('fleet.selectGroup'), ['class' => 'form-label']) !!}

              <select id="group_id" name="group_id" class="form-control">
                <option value="">@lang('fleet.vehicleGroup')</option>
                @foreach($groups as $group)
                <option value="{{$group->id}}" @if($group->id == $user->group_id) selected @endif>{{$group->name}}
                </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              {!! Form::label('email', __('fleet.email'), ['class' => 'form-label']) !!}
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                {!! Form::email('email', $user->email,['class' => 'form-control','required']) !!}
              </div>
            </div>

            <div class="mb-3">
              {!! Form::label('role_id',__('fleet.role'), ['class' => 'form-label']) !!}
              <select id="role_id" name="role_id" class="form-control" required>
                <option value="">@lang('fleet.role')</option>
                @foreach($roles as $role)
                <option value="{{$role->id}}" @if($user->roles->first()->id == $role->id) selected
                  @endif>{{$role->name}}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              {!! Form::label('profile_image', __('fleet.profile_photo'), ['class' => 'form-label']) !!}
              @if($user->getMeta('profile_image') != null)
              <a href="{{ asset('uploads/'.$user->getMeta('profile_image')) }}" target="_blank">@lang('fleet.view')</a>
              @endif
              <br>
              {!! Form::file('profile_image',null,['class' => 'form-control']) !!}
            </div>

          </div>
        </div>
        <div class="col-md-12">
          {!! Form::submit(__('fleet.update'), ['class' => 'fleet-btn']) !!}
        </div>
        {!! Form::close() !!}
    </x-fleet.panel>
  </div>
</div>

@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#group_id').select2({placeholder: "@lang('fleet.selectGroup')",allowClear:true});
    $('#role_id').select2({placeholder: "@lang('fleet.role')"});
    //Flat green color scheme for iCheck
    // $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
    //   checkboxClass: 'icheckbox_flat-green',
    //   radioClass   : 'iradio_flat-green'
    // });
  });
</script>
@endsection