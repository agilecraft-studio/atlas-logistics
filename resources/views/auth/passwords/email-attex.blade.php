@extends('layouts.auth-attex', ['page_title' => __('passwords.reset_password')])
@section('content')
<div class="text-center w-75 m-auto">
    <h4 class="text-dark-50 text-center mt-0 fw-bold">@lang('passwords.reset_password')</h4>
    <p class="text-muted mb-4">@lang('passwords.reset_password')</p>
</div>
@if(session('status'))
<div class="alert alert-success" role="alert">{{ session('status') }}</div>
@endif
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">@lang('passwords.email')</label>
        <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="@lang('passwords.email')">
        @if($errors->has('email'))
        <span class="invalid-feedback" role="alert"><strong>{{ $errors->first('email') }}</strong></span>
        @endif
    </div>
    <div class="mb-0 text-center d-grid">
        <button class="btn btn-primary" type="submit">@lang('passwords.send_reset_link')</button>
    </div>
</form>
<div class="row mt-3">
    <div class="col-12 text-center">
        <p class="text-muted">Back to <a href="{{ route('login') }}" class="text-muted ms-1"><b>@lang('passwords.sign_in')</b></a></p>
    </div>
</div>
@endsection
