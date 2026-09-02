<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Forgot Password — Oudaa') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">
        <img src="{{ asset('images/logo-transparent.png') }}" alt="Oudaa" class="auth-logo">
        <p class="sub">{{ __('Enter the email on your committee account and we\'ll send you a link to reset your password.') }}</p>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->{{ __('any())') }}
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email', ['tenant' => $tenant]) }}">
            @csrf
            <div class="form-row">
                <label for="email">Email<span class="req">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">{{ __('Send reset link') }}</button>
        </form>

        <p class="sub" style="margin-top:16px;">
            <a href="{{ route('login', ['tenant' => $tenant]) }}">Back to sign in</a>
        </p>
    </div>
</div>
</body>
</html>
