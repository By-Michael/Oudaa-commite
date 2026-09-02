@extends('layouts.landing')

@section('title', __('About Oudaa'))
@section('meta_description', "Oudaa is a simple, focused platform built to help community committees manage residents, fees, funds and expenses without the spreadsheet chaos.")

@section('content')
<!-- PAGE HEADER -->
  <header class="page-header">
    <div class="container">
      <h1 class="mb-3">About Oudaa</h1>
      <div class="breadcrumb-custom">
        <a href="{{ route('landing.index') }}">{{ __('Home') }}</a> <span>/</span> <span class="active">About</span>
      </div>
    </div>
  </header>

  <!-- STORY -->
  <section class="section">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6 reveal">
          <img src="https://images.unsplash.com/photo-1571946080923-a81668948f52?w=700&h=560&fit=crop" class="rounded-img w-100" alt="{{ __('Bole district, Addis Ababa, Ethiopia') }}" style="aspect-ratio:5/4; object-fit:cover;">
        </div>
        <div class="col-lg-6 reveal">
          <span class="eyebrow">{{ __('Our mission') }}</span>
          <h2 class="section-title">Community management, without the chaos</h2>
          <p class="section-subtitle mb-3">{{ __('Most residential communities — whether it\'s a small group of houses or a full apartment building — are still run on a patchwork of spreadsheets, paper receipts and group chats. It works, until a fund balance doesn\'t add up or nobody remembers who paid last month\'s fee.') }}</p>
          <p class="mb-4">{{ __('Oudaa exists to fix that: one platform where a committee can manage residents, fees, payments, funds, projects and expenses, with every number in one place that the whole committee can trust.') }}</p>
          <div class="row g-3">
            <div class="col-6">
              <div class="d-flex align-items-center gap-3">
                <div class="icon-box icon-box-primary"><i class="bi bi-shield-lock"></i></div>
                <div><strong class="text-navy d-block">{{ __('Private by design') }}</strong><small class="text-slate">Your data, isolated per community</small></div>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center gap-3">
                <div class="icon-box icon-box-success"><i class="bi bi-lightning-charge"></i></div>
                <div><strong class="text-navy d-block">{{ __('Ready in minutes') }}</strong><small class="text-slate">No setup calls required</small></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- VALUES -->
  <section class="section bg-soft">
    <div class="container">
      <div class="row section-header justify-content-center text-center">
        <div class="col-lg-7 reveal">
          <span class="eyebrow">{{ __('What drives us') }}</span>
          <h2 class="section-title">The principles behind Oudaa</h2>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-3 reveal">
          <div class="card-premium text-center">
            <div class="icon-box icon-box-primary mx-auto mb-3"><i class="bi bi-bullseye"></i></div>
            <h3 class="card-title">{{ __('Simple over feature-heavy') }}</h3>
            <p>We'd rather nail residents, fees, funds and expenses than bury them under features nobody uses.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="card-premium text-center">
            <div class="icon-box icon-box-secondary mx-auto mb-3"><i class="bi bi-eye"></i></div>
            <h3 class="card-title">{{ __('One shared source of truth') }}</h3>
            <p>Every committee member sees the same numbers — no more conflicting spreadsheets.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="card-premium text-center">
            <div class="icon-box icon-box-success mx-auto mb-3"><i class="bi bi-shield-check"></i></div>
            <h3 class="card-title">{{ __('Your community\'s data is yours') }}</h3>
            <p>Every community gets its own isolated database — nothing shared, nothing mixed up.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="card-premium text-center">
            <div class="icon-box icon-box-primary mx-auto mb-3"><i class="bi bi-arrow-repeat"></i></div>
            <h3 class="card-title">{{ __('Built to grow with you') }}</h3>
            <p>Whether it's five houses or a full apartment building, Oudaa adapts to your community.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="section pt-0">
    <div class="container">
      <div class="cta-section reveal">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h2 class="mb-2">{{ __('See it for yourself') }}</h2>
            <p class="mb-0 fs-5">Create your platform for free and try it with your own community.</p>
          </div>
          <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
            <a href="{{ route('onboarding.step1') }}" class="btn btn-primary btn-lg-custom">{{ __('Create Your Platform') }} <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
