@extends('layouts.auth-attex', ['page_title' => __('passwords.sign_in')])
@section('content')
<div class="text-center w-75 m-auto">
    <h4 class="text-dark-50 text-center mt-0 fw-bold">@lang('passwords.sign_in')</h4>
    <p class="text-muted mb-4">@lang('passwords.login_desc')</p>
</div>
@if($errors->any())
<div class="alert alert-danger" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if(Session::has('error'))
<div class="alert alert-danger" role="alert">{{ Session::get('error') }}</div>
@endif
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">@lang('passwords.email')</label>
        <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="@lang('passwords.email')">
        @if($errors->has('email'))
        <span class="invalid-feedback" role="alert"><strong>{{ $errors->first('email') }}</strong></span>
        @endif
    </div>
    <div class="mb-3">
        <a href="{{ route('password.request') }}" class="text-muted float-end fs-12">@lang('passwords.forgot_password')</a>
        <label for="password" class="form-label">@lang('passwords.password')</label>
        <div class="input-group input-group-merge">
            <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" required placeholder="@lang('passwords.password')">
            <div class="input-group-text" data-password="false"><span class="password-eye"></span></div>
            @if($errors->has('password'))
            <span class="invalid-feedback" role="alert"><strong>{{ $errors->first('password') }}</strong></span>
            @endif
        </div>
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">@lang('passwords.remember')</label>
        </div>
    </div>
    <div class="mb-0 text-center d-grid">
        <button class="btn btn-primary" type="submit">@lang('passwords.sign_in')</button>
    </div>
</form>
@endsection
