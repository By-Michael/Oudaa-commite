<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Oudaa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand"><img src="{{ asset('images/logo-transparent.png') }}" alt="Oudaa" class="brand-logo"></div>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('residents.index') }}" class="{{ request()->routeIs('residents.*') ? 'active' : '' }}">Residents</a>
            <a href="{{ route('fees.index') }}" class="{{ request()->routeIs('fees.*') ? 'active' : '' }}">Fees</a>
            <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">Payments</a>
            <a href="{{ route('funds.index') }}" class="{{ request()->routeIs('funds.*') ? 'active' : '' }}">Funds</a>
            <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">Projects</a>
            <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">Expenses</a>
            <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">Employees</a>
            <a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}">Audit Log</a>
        </nav>
        <div class="foot">
            Signed in as<br><strong>{{ auth()->user()->name ?? 'Committee' }}</strong>
            <div style="margin-top:8px;">
                <a href="#" style="color:#D0C6E4;">Help and support</a>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="topbar-actions">
                <button type="button" class="icon-btn" aria-label="Notifications">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </button>
                <div class="account-menu">
                    <button type="button" class="account-trigger" id="accountTrigger" aria-haspopup="true" aria-expanded="false">
                        <span class="avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}</span>
                        <svg class="chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="account-popup" id="accountPopup">
                        <a href="{{ route('settings.edit') }}">Settings</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
<script>
    (function () {
        var trigger = document.getElementById('accountTrigger');
        var popup = document.getElementById('accountPopup');
        if (!trigger || !popup) return;
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = popup.classList.toggle('open');
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            if (!popup.contains(e.target) && !trigger.contains(e.target)) {
                popup.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    })();
</script>
</body>
</html>
