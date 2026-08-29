@extends('layouts.landing')

@section('title', 'Oudaa — Community Management, Simplified')
@section('meta_description', 'Oudaa is a SaaS platform for managing communities, condos and apartment buildings — residents, fees, funds, payments, projects and expenses, all in one place.')

@section('content')
<!-- HERO -->
  <header class="hero">
    <div class="container">
      <div class="row align-items-center gy-5">
        <div class="col-lg-6 hero-content">
          <span class="eyebrow">Free to get started</span>
          <h1 class="mb-4">Run your community without the <span class="text-primary-custom">spreadsheets and paperwork</span>.</h1>
          <p class="fs-5 mb-4" style="max-width: 540px;">Oudaa gives your committee one simple platform to manage residents, collect fees, track funds, and record every payment and expense — replacing the scattered spreadsheets and paper files most communities still run on.</p>
          <div class="d-flex flex-wrap gap-3 mb-4 justify-content-lg-start justify-content-center">
            <a href="{{ route('onboarding.step1') }}" class="btn btn-primary btn-lg-custom">Create Your Platform <i class="bi bi-arrow-right ms-1"></i></a>
            <a href="{{ route('landing.services') }}" class="btn btn-light-custom btn-lg-custom">See What It Does</a>
          </div>
          <p class="text-slate mb-0" style="font-size:0.9rem;"><i class="bi bi-check-circle-fill text-success me-1"></i>No credit card required &nbsp; · &nbsp; Ready in a couple of minutes</p>
        </div>
        <div class="col-lg-6">
          <div class="hero-visual position-relative mx-auto" style="max-width: 480px;">
            <div class="hero-mock-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                  <span style="width:10px;height:10px;border-radius:50%;background:#FF5F56;display:block;"></span>
                  <span style="width:10px;height:10px;border-radius:50%;background:#FFBD2E;display:block;"></span>
                  <span style="width:10px;height:10px;border-radius:50%;background:#27C93F;display:block;"></span>
                </div>
                <span class="badge-soft">Dashboard</span>
              </div>
              <div class="mock-bar" style="width: 90%;"></div>
              <div class="mock-bar" style="width: 65%;"></div>
              <div class="row g-2 my-3">
                <div class="col-6">
                  <div class="p-3 rounded-3" style="background: var(--n-bg-alt);">
                    <i class="bi bi-people text-primary-custom fs-4"></i>
                    <div class="fw-bold text-navy mt-2 font-display">128</div>
                    <small class="text-slate">Residents</small>
                  </div>
                </div>
                <div class="col-6">
                  <div class="p-3 rounded-3" style="background: #EAF1FB;">
                    <i class="bi bi-piggy-bank text-primary-custom fs-4" style="color:var(--n-secondary) !important;"></i>
                    <div class="fw-bold text-navy mt-2 font-display">4 Funds</div>
                    <small class="text-slate">Tracked live</small>
                  </div>
                </div>
              </div>
              <div class="mock-bar" style="width: 100%; height: 70px; border-radius: 10px; background: linear-gradient(90deg, var(--n-bg-alt), #EAF1FB);"></div>
            </div>
            <div class="hero-float-badge badge-1 d-none d-sm-flex">
              <div class="icon-box icon-box-success" style="width:36px;height:36px;border-radius:10px;font-size:1.1rem;"><i class="bi bi-check-lg"></i></div>
              <div>Payment Recorded<br><small class="text-slate fw-normal">Auto-linked to resident</small></div>
            </div>
            <div class="hero-float-badge badge-2 d-none d-sm-flex">
              <div class="icon-box icon-box-primary" style="width:36px;height:36px;border-radius:10px;font-size:1.1rem;"><i class="bi bi-envelope-check"></i></div>
              <div>Setup Link Sent<br><small class="text-slate fw-normal">to your admin email</small></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- FEATURES OVERVIEW -->
  <section class="section bg-soft">
    <div class="container">
      <div class="row section-header align-items-end">
        <div class="col-lg-7 reveal">
          <span class="eyebrow">What's inside</span>
          <h2 class="section-title">Everything a committee needs, in one place</h2>
          <p class="section-subtitle">No more chasing spreadsheets across three people's laptops — one dashboard for the whole committee.</p>
        </div>
        <div class="col-lg-5 text-lg-end mt-3 mt-lg-0 reveal">
          <a href="{{ route('landing.services') }}" class="card-link-arrow">See all features <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-3 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-primary mb-4"><i class="bi bi-people"></i></div>
            <h3 class="card-title">Residents</h3>
            <p class="mb-3">Keep a single, always up-to-date record of every household, unit and ID — searchable in seconds.</p>
            <a href="{{ route('landing.service-details', 'residents') }}" class="card-link-arrow">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-secondary mb-4"><i class="bi bi-receipt"></i></div>
            <h3 class="card-title">Fees &amp; Payments</h3>
            <p class="mb-3">Set recurring or one-off fees, then track exactly who's paid, who hasn't, and record payments as they come in.</p>
            <a href="{{ route('landing.service-details', 'fees') }}" class="card-link-arrow">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-success mb-4"><i class="bi bi-piggy-bank"></i></div>
            <h3 class="card-title">Funds</h3>
            <p class="mb-3">Organize money into separate funds — maintenance, reserve, events — so balances never get mixed up.</p>
            <a href="{{ route('landing.service-details', 'funds') }}" class="card-link-arrow">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="card-premium">
            <div class="icon-box icon-box-primary mb-4"><i class="bi bi-kanban"></i></div>
            <h3 class="card-title">Projects &amp; Expenses</h3>
            <p class="mb-3">Plan community projects against a fund's budget and log every expense against the right project.</p>
            <a href="{{ route('landing.service-details', 'projects') }}" class="card-link-arrow">Learn more <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT / WHY US -->
  <section class="section">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6 reveal">
          <div class="position-relative">
            <img src="{{ asset('nexora-assets/img/villa-community.jpg') }}" class="rounded-img w-100" alt="Villa community in Addis Ababa, Ethiopia" style="aspect-ratio: 5/4; object-fit:cover;">
            <div class="hero-float-badge badge-2 d-none d-sm-flex" style="position:absolute; bottom:-1.5rem; left:-1.5rem; right:auto;">
              <div class="icon-box icon-box-primary" style="width:36px;height:36px;border-radius:10px;font-size:1.1rem;"><i class="bi bi-lightning-charge-fill"></i></div>
              <div>Ready in minutes<br><small class="text-slate fw-normal">No setup calls needed</small></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 reveal">
          <span class="eyebrow">Why Oudaa</span>
          <h2 class="section-title">Built for how committees actually work</h2>
          <p class="section-subtitle mb-4">Most communities run on a mix of spreadsheets, WhatsApp messages and paper receipts — which works, until someone forgets who paid, or a fund balance doesn't add up. Oudaa replaces that with one shared source of truth every committee member can trust.</p>
          <ul class="list-check mb-4">
            <li><i class="bi bi-check"></i><div><strong class="text-navy">Works for any community</strong> — houses/villas or condo/apartment buildings with block numbers.</div></li>
            <li><i class="bi bi-check"></i><div><strong class="text-navy">Every committee member sees the same numbers</strong> — no more "let me check my spreadsheet."</div></li>
            <li><i class="bi bi-check"></i><div><strong class="text-navy">Free to start</strong> — sign up, get your own platform, and try it with your real community.</div></li>
          </ul>
          <a href="{{ route('landing.about') }}" class="btn btn-primary btn-lg-custom">More About Us</a>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="section bg-soft">
    <div class="container">
      <div class="row section-header justify-content-center text-center">
        <div class="col-lg-7 reveal">
          <span class="eyebrow">How it works</span>
          <h2 class="section-title">From signup to your first login, in four steps</h2>
          <p class="section-subtitle mx-auto">No installs, no sales calls — just a short form and an email.</p>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-3 reveal">
          <div class="process-step">
            <span class="process-number">01</span>
            <h3 class="card-title">Name it</h3>
            <p>Tell us your community's name and whether it's a normal community or a condo/apartment building.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="process-step">
            <span class="process-number">02</span>
            <h3 class="card-title">Pick your link</h3>
            <p>Choose your platform's web address — this is what your committee will use to log in from now on.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="process-step">
            <span class="process-number">03</span>
            <h3 class="card-title">Check your email</h3>
            <p>We build your platform and email you a link to set your admin password — usually within a minute.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3 reveal">
          <div class="process-step">
            <span class="process-number">04</span>
            <h3 class="card-title">Start managing</h3>
            <p>Add residents, set fees, and start tracking funds and payments right away.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="section">
    <div class="container">
      <div class="cta-section reveal">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h2 class="mb-2">Ready to get your community organized?</h2>
            <p class="mb-0 fs-5">Create your platform for free — no credit card, no sales call.</p>
          </div>
          <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
            <a href="{{ route('onboarding.step1') }}" class="btn btn-primary btn-lg-custom">Create Your Platform <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
@endsection
