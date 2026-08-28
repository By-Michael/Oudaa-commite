<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oudaa Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">
        <img src="{{ asset('images/logo-transparent.png') }}" alt="Oudaa" class="auth-logo">
        <p class="sub">Committee members only. Sign in to manage the panel.</p>

        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="form-row">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-row">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-row">
                <label style="display:inline-flex;align-items:center;gap:6px;text-transform:none;font-weight:400;">
                    <input type="checkbox" name="remember" style="width:auto;" value="1"> Remember me
                </label>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Sign in</button>
        </form>
    </div>
</div>
</body>
</html>
