@extends('layouts.auth-attex', ['page_title' => __('passwords.reset_password')])
@section('content')
<div class="text-center w-75 m-auto">
    <h4 class="text-dark-50 text-center mt-0 fw-bold">@lang('passwords.reset_password')</h4>
    <p class="text-muted mb-4">@lang('passwords.reset_password')</p>
</div>
@if(session('status'))
<div class="alert alert-success" role="alert">{{ session('status') }}</div>
@endif
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
        <label for="email" class="form-label">@lang('passwords.email')</label>
        <input id="email" type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus readonly>
        @if($errors->has('email'))
        <span class="invalid-feedback" role="alert"><strong>{{ $errors->first('email') }}</strong></span>
        @endif
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">@lang('passwords.password')</label>
        <input id="password" type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" required placeholder="@lang('passwords.password')">
        @if($errors->has('password'))
        <span class="invalid-feedback" role="alert"><strong>{{ $errors->first('password') }}</strong></span>
        @endif
    </div>
    <div class="mb-3">
        <label for="password-confirm" class="form-label">@lang('passwords.confirm_password')</label>
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="@lang('passwords.confirm_password')">
    </div>
    <div class="mb-0 text-center d-grid">
        <button type="submit" class="btn btn-primary">@lang('passwords.reset_password')</button>
    </div>
</form>
@endsection
