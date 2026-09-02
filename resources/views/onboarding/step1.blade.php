@extends('layouts.wizard')

@section('title', 'Name your community — Oudaa')

@section('content')

<div class="wizard-steps">
    <span class="dot active"></span>
    <span class="dot"></span>
    <span class="dot"></span>
</div>

<h1>{{ __('What\'s your community called?') }}</h1>
<p class="sub">This is the name your residents and committee will see.</p>

@if ($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('onboarding.step1.store') }}">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">{{ __('Community name') }}<span class="req">*</span></label>
        <input
            type="text"
            id="name"
            name="name"
            class="form-control"
            placeholder="{{ __('e.g. Green Valley Residences') }}"
            value="{{ old('name', $name) }}"
            data-filter="safe-text"
            autofocus
            required
            minlength="2"
            maxlength="100"
        >
    </div>

    <button type="submit" class="btn btn-primary w-100">Continue <i class="bi bi-arrow-right"></i></button>
</form>

@endsection
