@extends('layouts.landing')

@section('title', __('Features') . ' — Oudaa')
@section('meta_description', "Everything Oudaa gives your committee: residents, fees, payments, funds, projects and expenses — all in one platform.")

@section('content')
<header class="page-header">
    <div class="container">
      <h1 class="mb-3">What Oudaa Does</h1>
      <div class="breadcrumb-custom">
        <a href="{{ route('landing.index') }}">{{ __('Home') }}</a> <span>/</span> <span class="active">Features</span>
      </div>
    </div>
  </header>

  <!-- FEATURES GRID -->
  <section class="section">
    <div class="container">
      <div class="row section-header justify-content-center text-center">
        <div class="col-lg-7 reveal">
          <span class="eyebrow">{{ __('One platform, six essentials') }}</span>
          <h2 class="section-title">Everything a committee actually uses</h2>
          <p class="section-subtitle mx-auto">{{ __('No bloated features you\'ll never touch — just the parts of running a community that used to live across spreadsheets, group chats and paper folders.') }}</p>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-4 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-lg icon-box-primary mb-4"><i class="bi bi-people"></i></div>
            <h3 class="card-title">{{ __('Residents') }}</h3>
            <p class="mb-3">One record per household — name, ID number, unit (and block, for condos), contact details and occupancy status, searchable instantly.</p>
            <a href="{{ route('landing.service-details', 'residents') }}" class="card-link-arrow">{{ __('Learn more') }} <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-lg icon-box-secondary mb-4"><i class="bi bi-receipt"></i></div>
            <h3 class="card-title">Fees</h3>
            <p class="mb-3">{{ __('Set up recurring or one-off fees for the whole community, and see at a glance who\'s paid and who\'s still due.') }}</p>
            <a href="{{ route('landing.service-details', 'fees') }}" class="card-link-arrow">{{ __('Learn more') }} <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-lg icon-box-success mb-4"><i class="bi bi-cash-coin"></i></div>
            <h3 class="card-title">Payments</h3>
            <p class="mb-3">{{ __('Record every payment against the resident and fee it belongs to — a clean, auditable paper trail with no guesswork.') }}</p>
            <a href="{{ route('landing.service-details', 'payments') }}" class="card-link-arrow">{{ __('Learn more') }} <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-lg icon-box-primary mb-4"><i class="bi bi-piggy-bank"></i></div>
            <h3 class="card-title">Funds</h3>
            <p class="mb-3">{{ __('Split community money into separate funds — maintenance, reserve, events, anything — so balances never blur together.') }}</p>
            <a href="{{ route('landing.service-details', 'funds') }}" class="card-link-arrow">{{ __('Learn more') }} <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-lg icon-box-secondary mb-4"><i class="bi bi-kanban"></i></div>
            <h3 class="card-title">Projects</h3>
            <p class="mb-3">{{ __('Plan community projects — repaving, repairs, upgrades — against a fund\'s budget, and track progress from planned to complete.') }}</p>
            <a href="{{ route('landing.service-details', 'projects') }}" class="card-link-arrow">{{ __('Learn more') }} <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-lg icon-box-success mb-4"><i class="bi bi-wallet2"></i></div>
            <h3 class="card-title">Expenses</h3>
            <p class="mb-3">{{ __('Log every outgoing cost against the right project or fund, so the committee always knows exactly where the money went.') }}</p>
            <a href="{{ route('landing.service-details', 'expenses') }}" class="card-link-arrow">{{ __('Learn more') }} <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- WHO IT'S FOR -->
  <section class="section bg-soft">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6 reveal">
          <span class="eyebrow">Built for</span>
          <h2 class="section-title">{{ __('Any community, big or small') }}</h2>
          <p class="section-subtitle mb-4">Whether you're a small HOA committee or managing a full apartment building, Oudaa adapts to your community type from the moment you sign up.</p>
          <ul class="list-check">
            <li><i class="bi bi-check"></i><div><strong class="text-navy">{{ __('Normal communities') }}</strong> — houses and villas, organized by unit.</div></li>
            <li><i class="bi bi-check"></i><div><strong class="text-navy">{{ __('Condos & apartment buildings') }}</strong> {{ __('— adds block numbers for every resident.') }}</div></li>
            <li><i class="bi bi-check"></i><div><strong class="text-navy">{{ __('Multiple committee members') }}</strong> — each with their own login, so every action is attributable.</div></li>
          </ul>
        </div>
        <div class="col-lg-6 reveal">
          <img src="https://images.unsplash.com/photo-1626598442658-ea6a1a5943df?w=700&h=560&fit=crop" class="rounded-img w-100" alt="{{ __('Streets of Addis Ababa, Ethiopia') }}" style="aspect-ratio:5/4; object-fit:cover;">
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section">
    <div class="container">
      <div class="row section-header justify-content-center text-center">
        <div class="col-lg-7 reveal">
          <span class="eyebrow">{{ __('Questions') }}</span>
          <h2 class="section-title">Frequently asked questions</h2>
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-8 reveal">
          <div class="accordion accordion-custom" id="servicesFaq">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">{{ __('How much does it cost?') }}</button>
              </h2>
              <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#servicesFaq">
                <div class="accordion-body">Oudaa is free to get started — create your platform and try it with your real community, no credit card required.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">{{ __('How long does setup take?') }}</button>
              </h2>
              <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#servicesFaq">
                <div class="accordion-body">A couple of minutes. Answer three short questions and we'll email you a link to set your password — your platform is ready as soon as you do.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">{{ __('Can residents log in themselves?') }}</button>
              </h2>
              <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#servicesFaq">
                <div class="accordion-body">{{ __('Not currently — Oudaa is built for the committee to manage records on residents\' behalf. Residents don\'t need their own account.') }}</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">{{ __('Is our community\'s data separate from others?') }}</button>
              </h2>
              <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#servicesFaq">
                <div class="accordion-body">{{ __('Yes — every community gets its own private database. Nothing is shared between communities.') }}</div>
              </div>
            </div>
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
            <h2 class="mb-2">{{ __('Ready to see it in action?') }}</h2>
            <p class="mb-0 fs-5">Create your platform for free and explore it with your own community.</p>
          </div>
          <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
            <a href="{{ route('onboarding.step1') }}" class="btn btn-primary btn-lg-custom">{{ __('Create Your Platform') }} <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
