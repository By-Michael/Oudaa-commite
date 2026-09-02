@extends('layouts.wizard')

@section('title', 'Setup issue — Oudaa')

@section('content')

<div class="text-center">
    <div style="width:56px;height:56px;border-radius:50%;background:#FEF3F2;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
        <i class="bi bi-exclamation-triangle" style="font-size:1.5rem;color:#D92D20;"></i>
    </div>

    <h1>{{ __('Something went wrong setting up :name', ['name' => $tenant->name]) }}</h1>
    <p class="sub">
        {{ __('Our team has been notified. Please contact') }}
        <a href="mailto:m7020322@gmail.com">m7020322@gmail.com</a> {{ __('and mention your community name so we can fix this for you.') }}
    </p>

    <a href="{{ route('landing.index') }}" class="btn btn-primary w-100 mt-2">{{ __('Back to Home') }}</a>
</div>

@endsection
