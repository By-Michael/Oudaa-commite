@extends('layouts.landing')

@section('title', $feature['title'].' — Oudaa')
@section('meta_description', $feature['intro'])

@section('content')
<header class="page-header">
    <div class="container">
      <h1 class="mb-3">{{ $feature['title'] }}</h1>
      <div class="breadcrumb-custom">
        <a href="{{ route('landing.index') }}">{{ __('Home') }}</a> <span>/</span> <a href="{{ route('landing.services') }}">Features</a> <span>/</span> <span class="active">{{ $feature['title'] }}</span>
      </div>
    </div>
  </header>

  <section class="section">
    <div class="container">
      <div class="row g-5">
        <!-- MAIN CONTENT -->
        <div class="col-lg-8 reveal">
          <div class="icon-box icon-box-lg icon-box-primary mb-4"><i class="bi {{ $feature['icon'] }}"></i></div>
          <span class="eyebrow">{{ __('Feature overview') }}</span>
          <h2 class="mb-3">{{ $feature['intro'] }}</h2>
          <p class="mb-4">{{ $feature['body'] }}</p>

          <h3 class="mb-3 mt-5">What's included</h3>
          <div class="row g-3 mb-4">
            @foreach ($feature['points'] as $point)
              <div class="col-md-6">
                <div class="feature-grid-item">
                  <div class="icon-box icon-box-primary"><i class="bi {{ $point['icon'] }}"></i></div>
                  <div><strong class="text-navy d-block mb-1">{{ $point['title'] }}</strong><span class="small">{{ $point['desc'] }}</span></div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-4 reveal">
          <div class="widget">
            <h5>{{ __('All Features') }}</h5>
            <ul class="widget-link-list">
              @foreach ($allFeatures as $slug => $item)
                <li>
                  <a href="{{ route('landing.service-details', $slug) }}"
                     class="text-decoration-none {{ $slug === $feature['slug'] ? 'text-primary-custom fw-bold' : 'text-navy' }}">
                    {{ $item['title'] }}
                  </a>
                  <i class="bi bi-arrow-right text-primary-custom"></i>
                </li>
              @endforeach
            </ul>
          </div>
          <div class="widget bg-gradient-dark text-white border-0">
            <div class="icon-box icon-box-white mb-3"><i class="bi bi-lightning-charge-fill"></i></div>
            <h5 class="text-white">Ready to try it?</h5>
            <p class="text-white-50 small mb-3">{{ __('Free to get started — no credit card, no sales call.') }}</p>
            <a href="{{ route('onboarding.step1') }}" class="btn btn-primary w-100">{{ __('Create Your Platform') }}</a>
          </div>
          <div class="widget">
            <h5>Questions?</h5>
            <p class="small text-slate mb-3">{{ __('Reach out and we\'ll help you get set up.') }}</p>
            <a href="{{ route('landing.contact') }}" class="card-link-arrow d-inline-flex">{{ __('Contact us') }} <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
