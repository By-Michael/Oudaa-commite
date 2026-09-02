<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Oudaa Login') }}</title>
    <script>
        // Applied before first paint (and before app.css loads) so there's
        // no flash of the wrong theme on load. Reads the same key set on
        // the landing page and carried through the whole onboarding flow.
        (function () {
            var saved = localStorage.getItem('oudaa-theme');
            var theme = saved || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">
        <img src="{{ asset('images/logo-transparent.png') }}" alt="Oudaa" class="auth-logo">
        <p class="sub">{{ __('Committee members only. Sign in to manage the panel.') }}</p>

        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" id="login-form">
            @csrf
            <div class="form-row">
                <label for="email">{{ __('Email') }}<span class="req">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-row">
                <label for="password">{{ __('Password') }}<span class="req">*</span></label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-row" style="display:flex;align-items:center;justify-content:space-between;">
                <label style="display:inline-flex;align-items:center;gap:6px;text-transform:none;font-weight:400;">
                    <input type="checkbox" id="remember" name="remember" style="width:auto;" value="1" @checked(old('remember'))> {{ __('Remember me') }}
                </label>
                <a href="{{ route('password.request', ['tenant' => request()->route('tenant')]) }}" style="font-size:13px;">{{ __('Forgot password?') }}</a>
            </div>
            <button type="submit" class="btn btn-primary" id="login-submit" data-signing-in-label="{{ __('Signing in…') }}" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;">
                <span class="spinner" id="login-spinner" style="display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;"></span>
                <span id="login-submit-label">{{ __('Sign in') }}</span>
            </button>
        </form>
    </div>
</div>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<script>
    document.getElementById('login-form').addEventListener('submit', function () {
        var btn = document.getElementById('login-submit');
        var spinner = document.getElementById('login-spinner');
        var label = document.getElementById('login-submit-label');
        if (btn.dataset.submitting === '1') return; // guard against double-submit
        btn.dataset.submitting = '1';
        btn.disabled = true;
        spinner.style.display = 'inline-block';
        label.textContent = btn.dataset.signingInLabel || 'Signing in…';
    });
</script>
</body>
</html>
