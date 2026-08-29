@extends('layouts.wizard')

@section('title', 'Your email — Oudaa')

@section('content')

<a href="{{ route('onboarding.step2') }}" class="wizard-back"><i class="bi bi-arrow-left"></i> Back</a>

<div class="wizard-steps">
    <span class="dot done"></span>
    <span class="dot done"></span>
    <span class="dot active"></span>
</div>

<h1>Where should we send your login link?</h1>
<p class="sub">
    We'll set up <strong>{{ $name }}</strong> at
    <strong>{{ rtrim(url('/'), '/') }}/{{ $slug }}</strong> and email you a link to set your admin password.
</p>

@if ($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('onboarding.step3.store') }}">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Your email<span class="req">*</span></label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-control"
            placeholder="you@example.com"
            value="{{ old('email') }}"
            autofocus
            required
        >
    </div>

    <div class="mb-3 form-check">
        <input
            type="checkbox"
            id="accept_terms"
            name="accept_terms"
            class="form-check-input"
            value="1"
            {{ old('accept_terms') ? 'checked' : '' }}
            required
        >
        <label for="accept_terms" class="form-check-label">
            I agree to the <a href="{{ route('landing.privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>
            and <a href="{{ route('landing.terms') }}" target="_blank" rel="noopener">Terms of Service</a>.
        </label>
    </div>

    <button type="submit" class="btn btn-primary w-100" id="submit-btn" disabled>Create My Platform <i class="bi bi-arrow-right"></i></button>
</form>

<script>
    var acceptTerms = document.getElementById('accept_terms');
    var submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = !acceptTerms.checked;
    acceptTerms.addEventListener('change', function () {
        submitBtn.disabled = !this.checked;
    });
</script>

@endsection
