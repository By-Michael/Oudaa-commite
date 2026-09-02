@extends('layouts.landing')

@section('title', __('Contact') . ' — Oudaa')
@section('meta_description', 'Get in touch with the Oudaa team — questions, support, or help getting your community set up.')

@section('content')
<header class="page-header">
    <div class="container">
      <h1 class="mb-3">{{ __('Get In Touch') }}</h1>
      <div class="breadcrumb-custom">
        <a href="{{ route('landing.index') }}">{{ __('Home') }}</a> <span>/</span> <span class="active">{{ __('Contact') }}</span>
      </div>
    </div>
  </header>

  <!-- CONTACT INFO CARDS -->
  <section class="section pb-0">
    <div class="container">
      <div class="row g-4 justify-content-center">
        <div class="col-md-5 reveal">
          <div class="card-premium text-center">
            <div class="icon-box icon-box-secondary mx-auto mb-3"><i class="bi bi-envelope"></i></div>
            <h3 class="card-title">{{ __('Email Us') }}</h3>
            <p class="mb-0">{{ __('General inquiries & support:') }}<br><a href="mailto:m7020322@gmail.com" class="text-primary-custom fw-semibold text-decoration-none">m7020322@gmail.com</a></p>
          </div>
        </div>
        <div class="col-md-5 reveal">
          <div class="card-premium text-center">
            <div class="icon-box icon-box-success mx-auto mb-3"><i class="bi bi-telephone"></i></div>
            <h3 class="card-title">{{ __('Call Us') }}</h3>
            <p class="mb-0">{{ __('Every day, during working hours') }}<br><a href="tel:+251973069687" class="text-primary-custom fw-semibold text-decoration-none">+251 973 069 687</a></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FORM -->
  <section class="section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-7 reveal">
          <span class="eyebrow">{{ __('Send a message') }}</span>
          <h2 class="section-title mb-4">{{ __('How can we help?') }}</h2>

          @if (session('status'))
            <div class="alert alert-success py-2">{{ session('status') }}</div>
          @endif
          @if ($errors->any())
            <div class="alert alert-danger py-2">
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form class="needs-validation" method="POST" action="{{ route('landing.contact.store') }}" novalidate>
            @csrf
            {{-- Honeypot: hidden from real visitors via CSS, bots tend to fill every field. --}}
            <input type="text" name="website" value="" autocomplete="off" tabindex="-1" style="position:absolute; left:-9999px;" aria-hidden="true">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="fullName">{{ __('Full Name') }}<span class="req">*</span></label>
                <input type="text" class="form-control" id="fullName" name="full_name" value="{{ old('full_name') }}" required data-filter="letters">
                <div class="invalid-feedback">{{ __('Please enter your name.') }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">{{ __('Email') }}<span class="req">*</span></label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                <div class="invalid-feedback">{{ __('Please enter a valid email address.') }}</div>
              </div>
              <div class="col-12">
                <label class="form-label" for="communityName">{{ __('Community Name (if you\'re already a customer)') }}</label>
                <input type="text" class="form-control" id="communityName" name="community_name" value="{{ old('community_name') }}" placeholder="{{ __('Optional') }}" data-filter="safe-text">
              </div>
              <div class="col-12">
                <label class="form-label" for="message">{{ __('Message') }}<span class="req">*</span></label>
                <textarea class="form-control" id="message" name="message" rows="5" placeholder="{{ __('Tell us what you need help with.') }}" required data-filter="safe-text">{{ old('message') }}</textarea>
                <div class="invalid-feedback">{{ __('Please share a few details.') }}</div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg-custom">{{ __('Send Message') }} <i class="bi bi-send ms-1"></i></button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section bg-soft">
    <div class="container">
      <div class="row section-header justify-content-center text-center">
        <div class="col-lg-7 reveal">
          <span class="eyebrow">{{ __('Before you reach out') }}</span>
          <h2 class="section-title">{{ __('Quick answers') }}</h2>
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-8 reveal">
          <div class="accordion accordion-custom" id="contactFaq">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cfaq1">{{ __('How soon will I hear back?') }}</button>
              </h2>
              <div id="cfaq1" class="accordion-collapse collapse show" data-bs-parent="#contactFaq">
                <div class="accordion-body">{{ __('We respond to all inquiries within one business day.') }}</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cfaq2">{{ __('I forgot my password — what do I do?') }}</button>
              </h2>
              <div id="cfaq2" class="accordion-collapse collapse" data-bs-parent="#contactFaq">
                <div class="accordion-body">{{ __('Email') }} us at m7020322@gmail.com with your community name and we'll help you regain access.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cfaq3">{{ __('Can I get a demo before signing up?') }}</button>
              </h2>
              <div id="cfaq3" class="accordion-collapse collapse" data-bs-parent="#contactFaq">
                <div class="accordion-body">{{ __('Signup is free and takes a couple of minutes — the fastest way to see Oudaa is to create your own platform and try it directly.') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
