@extends('layouts.wizard')

@section('title', __('Set your password') . ' — Oudaa')

@section('content')

<h1>{{ __('Set your password') }}</h1>
<p class="sub">
    {{ __('Welcome to') }} <strong>{{ $tenant->name }}</strong>. Choose a password for your admin account
    (<strong>{{ $tenant->owner_email }}</strong>) to finish setting up your platform.
</p>

@if ($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ $storeUrl }}">
    @csrf

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('New password') }}<span class="req">*</span></label>
        <input type="password" id="password" name="password" class="form-control" minlength="8" required autofocus>
        <div class="form-text text-slate">At least 8 characters.</div>
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">{{ __('Confirm password') }}<span class="req">*</span></label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Set Password &amp; Continue</button>
</form>

@endsection
