<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — Oudaa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">
        <img src="{{ asset('images/logo-transparent.png') }}" alt="Oudaa" class="auth-logo">
        <p class="sub">Choose a new password for your committee account.</p>

        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update', ['tenant' => $tenant, 'token' => $token]) }}">
            @csrf
            <div class="form-row">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>
            </div>
            <div class="form-row">
                <label for="password">New password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            <div class="form-row">
                <label for="password_confirmation">Confirm new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Reset password</button>
        </form>

        <p class="sub" style="margin-top:16px;">
            <a href="{{ route('login', ['tenant' => $tenant]) }}">Back to sign in</a>
        </p>
    </div>
</div>
</body>
</html>
