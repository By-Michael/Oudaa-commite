@extends('layouts.wizard')

@section('title', 'Check your email — Oudaa')

@section('content')

<div class="text-center">
    <div style="width:56px;height:56px;border-radius:50%;background:var(--n-bg-alt);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
        <i class="bi bi-envelope-check" style="font-size:1.5rem;color:var(--n-primary);"></i>
    </div>

    <h1>{{ __("We're setting up :name", ['name' => $tenant->name]) }}</h1>
    <p class="sub">
        {{ __('A confirmation email is on its way to') }} <strong>{{ $tenant->owner_email }}</strong> with a link to
        {{ __('set your password. It usually takes less than a minute.') }}
    </p>

    <p class="text-slate" style="font-size:0.9rem;">
        {{ __('Your platform link will be:') }}<br>
        <strong>{{ rtrim(url('/'), '/') }}/{{ $tenant->slug }}</strong>
    </p>

    <a href="{{ route('landing.index') }}" class="btn btn-primary w-100 mt-3">Back to Home</a>
</div>

@endsection
