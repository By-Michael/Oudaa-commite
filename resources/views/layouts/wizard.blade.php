<!DOCTYPE html>
<html lang="en">
<head>
  <script>
    // Applied before first paint to avoid a light/dark flash on load,
    // and to carry the theme chosen on the landing page through the
    // whole onboarding flow (same localStorage key, same origin).
    (function () {
      var stored = localStorage.getItem('oudaa-theme');
      var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      if (theme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    })();
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Create your platform — Oudaa')</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="{{ asset('nexora-assets/css/custom.css') }}" rel="stylesheet">

  <style>
    body {
      background: var(--n-bg);
      min-height: 100vh;
      display: flex;
      align-items: center;
      font-family: var(--n-font-body);
    }
    .wizard-wrap { width: 100%; padding: 2.5rem 1rem; }
    .wizard-card {
      max-width: 520px;
      margin: 0 auto;
      background: var(--n-white);
      border: 1px solid var(--n-border);
      border-radius: var(--n-radius-lg);
      padding: 2.5rem;
      box-shadow: 0 20px 40px -20px rgba(11, 20, 55, 0.15);
    }
    .wizard-brand {
      display: block;
      text-align: center;
      font-family: var(--n-font-display);
      font-weight: 800;
      font-size: 1.5rem;
      color: var(--n-navy);
      text-decoration: none;
      margin-bottom: 1.75rem;
    }
    .wizard-brand span { color: var(--n-primary); }
    .wizard-steps {
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      margin-bottom: 1.75rem;
    }
    .wizard-steps .dot {
      width: 32px; height: 4px; border-radius: 999px;
      background: var(--n-border);
    }
    .wizard-steps .dot.active { background: var(--n-primary); }
    .wizard-steps .dot.done { background: var(--n-success); }
    .wizard-card h1 {
      font-family: var(--n-font-display);
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--n-navy);
      margin-bottom: 0.4rem;
    }
    .wizard-card .sub { color: var(--n-slate); margin-bottom: 1.75rem; }
    .form-control, .form-select {
      padding: 0.7rem 0.9rem;
      border-radius: var(--n-radius-sm);
      border: 1px solid var(--n-border);
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--n-primary);
      box-shadow: 0 0 0 3px rgba(21, 94, 239, 0.12);
    }
    .slug-preview {
      font-size: 0.9rem;
      margin-top: 0.5rem;
      min-height: 1.4rem;
    }
    .slug-preview.ok { color: var(--n-success); }
    .slug-preview.taken { color: #D92D20; }
    .wizard-back {
      display: inline-flex; align-items: center; gap: 0.35rem;
      color: var(--n-slate); text-decoration: none; font-size: 0.9rem;
      margin-bottom: 1rem;
    }
    .wizard-back:hover { color: var(--n-primary); }
  </style>
  @stack('styles')
</head>
<body>
  <div class="wizard-wrap">
    <div class="wizard-card">
      <a href="{{ route('landing.index') }}" class="wizard-brand">
        <img src="{{ asset('nexora-assets/img/oudaa-logo.png') }}" alt="Oudaa" style="height:36px;width:auto;">
      </a>
      @yield('content')
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
