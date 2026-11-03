@extends('layouts.attex')

@section('page_title', __('fleet.change_details'))
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

		<x-fleet.panel :title="__('fleet.change_details') . ' : ' . $user_data->name" icon="bi-person-vcard">
			{!! Form::open(array("url"=>"admin/change-details/".$user_data->id,'files'=>true,'method'=>'POST'))!!}
			<input type="hidden" name="id" value="{{ $user_data->id}}">
			<div class="row">
				<div class="col-md-6 mb-3">
					{!! Form::label('name',__('fleet.name'),['class'=>"form-label"]) !!}
					{!! Form::text('name',$user_data->name,['class'=>"form-control",'required']) !!}
				</div>
				<div class="col-md-6 mb-3">
					{!! Form::label('email',__('fleet.email'),['class'=>"form-label"]) !!}
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-envelope"></i></span>
						{!! Form::email('email',$user_data->email,['class'=>"form-control",'required']) !!}
					</div>
				</div>
				<div class="col-md-6 mb-3">
					{!! Form::label('image', __('fleet.picture'), ['class' => 'form-label']) !!}
					@if($user_data->user_type == "D" && $user_data->getMeta('driver_image') != null)
						@if(starts_with($user_data->getMeta('driver_image'),'http'))
							@php($src = $user_data->getMeta('driver_image'))
						@else
							@php($src=asset('uploads/'.$user_data->getMeta('driver_image')))
						@endif
						<a href="{{ $src }}" target="_blank" class="d-block mb-2">@lang('fleet.view')</a>
					@elseif($user_data->user_type != "D" && $user_data->getMeta('profile_image') != null)
						<a href="{{ asset('uploads/'.$user_data->getMeta('profile_image')) }}" target="_blank" class="d-block mb-2">@lang('fleet.view')</a>
					@elseif($user_data->user_type == "C" && $user_data->getMeta('profile_pic') != null)
						@if(starts_with($user_data->getMeta('profile_pic'),'http'))
							@php($src = $user_data->getMeta('profile_pic'))
						@else
							@php($src=asset('uploads/'.$user_data->getMeta('profile_pic')))
						@endif
						<a href="{{ $src }}" target="_blank" class="d-block mb-2">@lang('fleet.view')</a>
					@endif
					{!! Form::file('image',null,['class' => 'form-control']) !!}
				</div>
				<div class="col-md-6 mb-3">
					<input type="hidden" name="language" value="English-en">
					{!! Form::label('theme',__('fleet.dark_mode'), ['class' => 'form-label']) !!}
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" role="switch" name="theme" value="dark-mode" id="theme" @if($user_data->theme=='dark-mode') checked @endif>
					</div>
				</div>
			</div>
			<div class="d-flex align-items-center gap-3">
				<input type="submit" class="fleet-btn" value="@lang('fleet.change_details')" />
				@if(session()->has('message'))
					<b class="update_message text-success mb-0">{{ session()->get('message') }}</b>
				@endif
			</div>
			{!! Form::close()!!}
		</x-fleet.panel>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<x-fleet.panel :title="__('fleet.change_password') . ' : ' . Auth::user()->name" icon="bi-key">
			{!! Form::open(array("url"=>"admin/changepassword/".Auth::user()->id))!!}
			<input type="hidden" name="id" value="{{ Auth::user()->id}}">
			<div class="row">
				<div class="col-md-6 mb-3">
					{!! Form::label('password',__('fleet.password'),['class'=>"form-label"]) !!}
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-lock"></i></span>
						<input type="password" name="password" class="form-control" required>
					</div>
				</div>
			</div>
			<input type="submit" class="fleet-btn" value="@lang('fleet.change_password')" />
			{!! Form::close()!!}
		</x-fleet.panel>
	</div>
</div>
@endsection

@section('scripts')
<script>
	$(document).ready(function () {
		setTimeout(function(){
			$('.update_message').remove();
		}, 5000);
	});
</script>
@endsection
