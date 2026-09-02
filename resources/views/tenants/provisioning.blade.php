@extends('layouts.wizard')

@section('title', 'Setting up — Oudaa')

@section('content')

<div class="text-center">
    <div style="width:56px;height:56px;border-radius:50%;background:var(--n-bg-alt);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
        <i class="bi bi-hourglass-split" style="font-size:1.5rem;color:var(--n-primary);"></i>
    </div>

    <h1>{{ __(':name is still being set up', ['name' => $tenant->name]) }}</h1>
    <p class="sub">
        {{ __('This usually takes less than a minute.') }} {{ __('Refresh') }} {{ __('this page in a moment, or check') }} <strong>{{ $tenant->owner_email }}</strong> {{ __('for a confirmation email.') }}
    </p>

    <button onclick="location.reload()" class="btn btn-primary w-100 mt-2">{{ __('Refresh') }}</button>
</div>

@endsection
