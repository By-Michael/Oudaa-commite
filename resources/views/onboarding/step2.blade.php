@extends('layouts.wizard')

@section('title', 'Choose your link — Oudaa')

@section('content')

<a href="{{ route('onboarding.step1') }}" class="wizard-back"><i class="bi bi-arrow-left"></i> Back</a>

<div class="wizard-steps">
    <span class="dot done"></span>
    <span class="dot active"></span>
    <span class="dot"></span>
</div>

<h1>Pick your platform link &amp; type</h1>
<p class="sub">This is the web address your committee and residents will use to log in.</p>

@if ($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('onboarding.step2.store') }}" id="step2-form">
    @csrf

    <div class="mb-2">
        <label for="slug" class="form-label">Your platform link</label>
        <div class="input-group">
            <span class="input-group-text" style="background:var(--n-bg-alt); color:var(--n-slate); border-radius: var(--n-radius-sm) 0 0 var(--n-radius-sm); font-size:0.85rem;">
                {{ rtrim(url('/'), '/') }}/
            </span>
            <input
                type="text"
                id="slug"
                name="slug"
                class="form-control"
                value="{{ old('slug', $slug) }}"
                pattern="[a-z0-9]+(-[a-z0-9]+)*"
                autocomplete="off"
                required
            >
        </div>
        <div id="slug-feedback" class="slug-preview"></div>
    </div>

    <div class="mb-4 mt-3">
        <label class="form-label d-block">Community type</label>

        <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="community_type" id="type-normal" value="normal"
                @checked(old('community_type', $communityType) === 'normal')>
            <label class="form-check-label" for="type-normal">
                <strong>Normal community</strong>
                <div class="text-slate" style="font-size:0.85rem;">Houses/villas — residents identified by unit number only.</div>
            </label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="community_type" id="type-condo" value="condo"
                @checked(old('community_type', $communityType) === 'condo')>
            <label class="form-check-label" for="type-condo">
                <strong>Condo / Apartment building</strong>
                <div class="text-slate" style="font-size:0.85rem;">Adds a block number field when adding residents.</div>
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100" id="step2-submit">Continue <i class="bi bi-arrow-right"></i></button>
</form>

@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('slug');
    const feedback = document.getElementById('slug-feedback');
    const submitBtn = document.getElementById('step2-submit');
    const checkUrl = @json(route('onboarding.check-slug'));
    let timer = null;
    let lastCheckedOk = true; // don't block submit before first check finishes

    function normalize(value) {
        return value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const raw = input.value;
        feedback.textContent = 'Checking...';
        feedback.className = 'slug-preview';

        timer = setTimeout(function () {
            fetch(checkUrl + '?slug=' + encodeURIComponent(raw))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.available) {
                        feedback.textContent = '✓ ' + data.slug + ' is available';
                        feedback.className = 'slug-preview ok';
                        lastCheckedOk = true;
                    } else {
                        feedback.textContent = '✗ Taken — try "' + data.suggestion + '"';
                        feedback.className = 'slug-preview taken';
                        lastCheckedOk = false;
                    }
                })
                .catch(function () {
                    feedback.textContent = '';
                });
        }, 400);
    });
})();
</script>
@endpush
