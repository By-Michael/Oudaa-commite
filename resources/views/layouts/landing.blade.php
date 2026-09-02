<!DOCTYPE html>
<html lang="{{ $currentLocale ?? 'en' }}">
<head>
  <script>
    // Applied before first paint to avoid a light/dark flash on load.
    (function () {
      var stored = localStorage.getItem('oudaa-theme');
      var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      if (theme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    })();
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Oudaa — Community Management, Simplified')</title>
  <meta name="description" content="@yield('meta_description', 'Oudaa is a SaaS platform for managing communities, condos and apartment buildings — residents, fees, funds, payments and more.')">
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
  @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-nexora fixed-top">
    <div class="container">
      <a class="navbar-brand navbar-brand-custom" href="{{ route('landing.index') }}">
        <img src="{{ asset('nexora-assets/img/oudaa-logo.png') }}" alt="{{ __('Oudaa') }}" style="height:34px;width:auto;">
      </a>
      <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="bar"></span><span class="bar"></span><span class="bar"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-1 mt-3 mt-lg-0">
          <li class="nav-item"><a class="nav-link nav2 active"  href="{{ route('landing.index') }}">{{ __('Home') }}</a></li>
          <li class="nav-item"><a class="nav-link nav2" href="{{ route('landing.about') }}">{{ __('About') }}</a></li>
          <li class="nav-item"><a class="nav-link nav2" href="{{ route('landing.services') }}">{{ __('Features') }}</a></li>
          <li class="nav-item"><a class="nav-link nav2" href="{{ route('landing.contact') }}">{{ __('Contact') }}</a></li>
          <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
            <div class="lang-toggle" role="group" aria-label="{{ __('Choose language') }}">
              <a href="{{ route('lang.switch', 'en') }}" class="lang-toggle-option {{ ($currentLocale ?? 'en') === 'en' ? 'active' : '' }}">EN</a>
              <a href="{{ route('lang.switch', 'am') }}" class="lang-toggle-option {{ ($currentLocale ?? 'en') === 'am' ? 'active' : '' }}">አማ</a>
            </div>
          </li>
          <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
            <button type="button" class="theme-toggle" id="themeToggle" aria-label="{{ __('Toggle dark mode') }}">
              <i class="bi bi-sun-fill"></i>
              <i class="bi bi-moon-stars-fill"></i>
            </button>
          </li>
          <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
            <a href="{{ route('onboarding.step1') }}" class="btn btn-primary btn-sm-custom w-100">{{ __('Create Platform') }}</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

@yield('content')

<footer class="footer-nexora">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6">
        
          <a class="navbar-brand-custom d-inline-block mb-3" href="{{ route('landing.index') }}">
            <img src="{{ asset('nexora-assets/img/oudaa-logo.png') }}" alt="Oudaa" style="height:32px;width:auto;">
          </a>
          <p class="text-white-50 mb-4" style="max-width:320px;">{{ __('Oudaa gives your committee one simple platform to manage residents, fees, funds, payments, projects and expenses.') }}</p>
          <div class="d-flex gap-2">
            <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-6 col-6">
          <h6>{{ __('Company') }}</h6>
          <a href="{{ route('landing.about') }}">{{ __('About Us') }}</a>
          <a href="{{ route('landing.services') }}">{{ __('Features') }}</a>
          <a href="{{ route('landing.contact') }}">{{ __('Contact') }}</a>
        </div>
        <div class="col-lg-2 col-md-6 col-6">
          <h6>{{ __('Features') }}</h6>
          <a href="{{ route('landing.service-details', 'residents') }}">{{ __('Residents') }}</a>
          <a href="{{ route('landing.service-details', 'fees') }}">{{ __('Fees') }}</a>
          <a href="{{ route('landing.service-details', 'payments') }}">{{ __('Payments') }}</a>
          <a href="{{ route('landing.service-details', 'funds') }}">{{ __('Funds') }}</a>
          <a href="{{ route('landing.service-details', 'projects') }}">{{ __('Projects') }}</a>
          <a href="{{ route('landing.service-details', 'expenses') }}">{{ __('Expenses') }}</a>
        </div>
        <div class="col-lg-4 col-md-6">
          <h6>{{ __('Get in touch') }}</h6>
          <p class="text-white-50 mb-2"><i class="bi bi-envelope me-2"></i><a href="mailto:m7020322@gmail.com" style="color:inherit;">m7020322@gmail.com</a></p>
          <p class="text-white-50 mb-3"><i class="bi bi-telephone me-2"></i><a href="tel:+251973069687" style="color:inherit;">+251 973 069 687</a></p>
          <a href="{{ route('onboarding.step1') }}" class="btn btn-primary btn-sm-custom">{{ __('Create Your Platform') }}</a>
        </div>
      </div>
      <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-center text-md-start">
        <p class="mb-0">&copy; {{ date('Y') }} Oudaa. {{ __('All rights reserved.') }}</p>
        <div class="d-flex gap-4">
          <a href="{{ route('landing.privacy') }}" class="mb-0">{{ __('Privacy Policy') }}</a>
          <a href="{{ route('landing.terms') }}" class="mb-0">{{ __('Terms of Service') }}</a>
        </div>
      </div>
    </div>
  </footer>

  <button class="back-to-top" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>

  <!-- Bootstrap Bundle JS -->
  <script src="{{ asset('js/ethiopian-date.js') }}?v={{ @filemtime(public_path('js/ethiopian-date.js')) }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('nexora-assets/js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
