<!DOCTYPE html>
<html lang="{{ $currentLocale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Dashboard')) — Oudaa</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <script>
        // Applied before first paint (and before app.css loads) so there's
        // no flash of the wrong theme on load. Reads the same key the
        // toggle button in the topbar writes to.
        (function () {
            var saved = localStorage.getItem('oudaa-theme');
            var theme = saved || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand"><img src="{{ asset('images/logo-transparent.png') }}" alt="Oudaa" class="brand-logo"></div>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">{{ __('Dashboard') }}</a>
            <a href="{{ route('residents.index') }}" class="{{ request()->routeIs('residents.*') ? 'active' : '' }}">{{ __('Residents') }}</a>
            <a href="{{ route('fees.index') }}" class="{{ request()->routeIs('fees.*') ? 'active' : '' }}">{{ __('Fees') }}</a>
            <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">{{ __('Payments') }}</a>
            <a href="{{ route('funds.index') }}" class="{{ request()->routeIs('funds.*') ? 'active' : '' }}">{{ __('Funds') }}</a>
            <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">{{ __('Projects') }}</a>
            <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">{{ __('Expenses') }}</a>
            <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">{{ __('Employees') }}</a>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">{{ __('Reports') }}</a>
            <a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}">{{ __('Audit Log') }}</a>
        </nav>
        <div class="foot">
            {{ __('Signed in as') }}<br><strong>{{ auth()->user()->name ?? __('Committee') }}</strong>
            <div style="margin-top:8px;">
                <a href="{{ route('help.index') }}" style="color:#D0C6E4;">{{ __('Help and support') }}</a>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">{{ __('Log out') }}</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="topbar-actions">
                <div class="lang-toggle" role="group" aria-label="{{ __('Choose language') }}">
                    <a href="{{ route('tenant.lang.switch', ['tenant' => request()->route('tenant'), 'locale' => 'en']) }}" class="lang-toggle-option {{ ($currentLocale ?? 'en') === 'en' ? 'active' : '' }}">EN</a>
                    <a href="{{ route('tenant.lang.switch', ['tenant' => request()->route('tenant'), 'locale' => 'am']) }}" class="lang-toggle-option {{ ($currentLocale ?? 'en') === 'am' ? 'active' : '' }}">አማ</a>
                </div>
                <button type="button" class="date-system-toggle" data-date-toggle aria-pressed="false" title="{{ __('Switch between Gregorian and Ethiopian calendar dates') }}">
                    <span class="date-system-option date-system-gc">GC</span>
                    <span class="date-system-option date-system-ec">EC</span>
                </button>
                <button type="button" class="icon-btn" id="themeToggle" aria-label="{{ __('Toggle dark mode') }}">
                    <svg class="theme-icon theme-icon-sun" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                    <svg class="theme-icon theme-icon-moon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                </button>
                <button type="button" class="icon-btn" aria-label="{{ __('Notifications') }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </button>
                <div class="account-menu">
                    <button type="button" class="account-trigger" id="accountTrigger" aria-haspopup="true" aria-expanded="false">
                        <span class="avatar-circle">{{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}</span>
                        <svg class="chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="account-popup" id="accountPopup">
                        <a href="{{ route('settings.edit') }}">{{ __('Settings') }}</a>
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

            @auth
                @php
                    $pendingAdminConsent = \App\Models\AdminConsentRequest::where('committee_id', auth()->id())
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now())
                        ->latest()
                        ->first();
                @endphp
                @if ($pendingAdminConsent)
                    <div class="alert" style="border:1px solid #B99FE0;background:#F4EEFB;padding:14px 16px;border-radius:10px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
                        <div>
                            <strong>{{ __('Platform support is requesting access to your account.') }}</strong>
                            <div class="muted" style="font-size:13px;margin-top:2px">
                                {{ __('Reason given: \':reason\'. They will not be able to act on your behalf unless you approve this.', ['reason' => $pendingAdminConsent->reason]) }}
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;flex-shrink:0">
                            <form method="POST" action="{{ route('admin-consent.respond', ['tenant' => request()->route('tenant'), 'token' => $pendingAdminConsent->token]) }}">
                                @csrf
                                <input type="hidden" name="decision" value="denied">
                                <button type="submit" class="btn btn-sm">{{ __('Deny') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin-consent.respond', ['tenant' => request()->route('tenant'), 'token' => $pendingAdminConsent->token]) }}">
                                @csrf
                                <input type="hidden" name="decision" value="approved">
                                <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                            </form>
                        </div>
                    </div>
                @endif
            @endauth

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
<script>
    // Dark / light theme toggle. Persists to localStorage under the same
    // key the inline <head> script reads on next load, so the choice
    // sticks across pages and reloads without a flash of the old theme.
    (function () {
        var STORAGE_KEY = 'oudaa-theme';
        var toggle = document.getElementById('themeToggle');
        if (!toggle) return;
        toggle.addEventListener('click', function () {
            var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try { localStorage.setItem(STORAGE_KEY, next); } catch (e) {}
        });
    })();
</script>
<script>
    // Silent session keep-alive: as long as this tab stays open, ping the
    // server every few minutes so the session never times out mid-work.
    // It only renews an already-valid session — it can't resurrect one
    // that's expired (e.g. laptop closed overnight), and logging out
    // still ends the session immediately as normal.
    (function () {
        var PING_INTERVAL_MS = 5 * 60 * 1000; // 5 minutes
        function ping() {
            fetch('{{ route('ping') }}', { credentials: 'same-origin', cache: 'no-store' }).catch(function () {});
        }
        setInterval(ping, PING_INTERVAL_MS);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') ping();
        });
    })();
</script>
<script>
    // Strings app.js needs but can't run through Blade's __() directly.
    window.i18n = {
        savingLabel: @json(__('Saving…')),
        saveErrorMessage: @json(__('Could not save — check your connection and try again.')),
        confirmFallback: @json(__('Are you sure?')),
    };
</script>
<script src="{{ asset('js/ethiopian-date.js') }}?v={{ @filemtime(public_path('js/ethiopian-date.js')) }}"></script>
<script src="{{ asset('js/date-picker.js') }}?v={{ @filemtime(public_path('js/date-picker.js')) }}"></script>
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
