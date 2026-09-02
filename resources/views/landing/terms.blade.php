@extends('layouts.landing')

@section('title', __('Terms of Service') . ' — Oudaa')
@section('meta_description', "The terms that apply when a committee creates and uses an Oudaa platform.")

@section('content')
<header class="page-header">
    <div class="container">
      <h1 class="mb-3">{{ __('Terms of Service') }}</h1>
      <div class="breadcrumb-custom">
        <a href="{{ route('landing.index') }}">{{ __('Home') }}</a> <span>/</span> <span class="active">{{ __('Terms of Service') }}</span>
      </div>
    </div>
  </header>

  <section class="section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9 reveal">

          <p class="text-slate mb-5">{{ __('Last updated:') }} {!! eth_date(now(), 'F j, Y') !!}</p>

          <p>{{ __('These Terms of Service ("Terms") govern the creation and use of a community platform through Oudaa. By creating a platform, you agree to these Terms on behalf of yourself and the committee you represent.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('1. What Oudaa is') }}</h3>
          <p>{{ __('Oudaa is a record-keeping platform for community committees. It helps a committee track residents, fees, funds, payments, projects and expenses in one place. Oudaa is a tool for organizing this information — it does not collect money on a committee\'s behalf, does not process card payments, and is not a party to any fee arrangement between a committee and its residents.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('2. Creating a platform') }}</h3>
          <p>{{ __('To create a platform, you provide a community name, choose a subdomain link, select a community type (normal community, or condo/apartment building with block numbers), and provide an email address to set your admin password. You\'re responsible for keeping your login credentials secure and for all activity that happens under your account.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('3. Free to use') }}</h3>
          <p>{{ __('Oudaa is currently free to use, with no paid tiers or credit card required to create a platform. We may introduce paid plans or additional features in the future; if we do, we will communicate that clearly before any change affects an existing platform, and this section will be updated accordingly.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('4. Committee responsibilities') }}</h3>
          <p>{{ __('The committee is responsible for the accuracy of the data it enters — resident details, fees, payments, funds, projects and expenses. Oudaa does not verify this information; it only stores and organizes what the committee records. Committees are responsible for actually collecting fees and payments from residents through whatever means they use (cash, bank transfer, etc.) — Oudaa only records that activity.') }}</p>
          <p>{{ __('Committees are also responsible for how they use resident data, including obtaining any consent needed under applicable law to record residents\' personal information on the platform.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('5. Data isolation') }}</h3>
          <p>{{ __('Each community\'s platform runs on its own separate database. A committee\'s data is not shared with, or visible to, any other community using Oudaa.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('6. Acceptable use') }}</h3>
          <p>{{ __('You agree not to use Oudaa to store unlawful content, to misrepresent a community\'s identity, to attempt to access another community\'s platform or data without authorization, or to interfere with the platform\'s normal operation.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('7. Availability') }}</h3>
          <p>{{ __('We aim to keep Oudaa available and reliable, but we don\'t guarantee uninterrupted access. The platform may occasionally be unavailable for maintenance, updates, or reasons outside our control.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('8. Suspension or termination') }}</h3>
          <p>{{ __('We may suspend or terminate a platform that violates these Terms or is used unlawfully. A committee may also stop using its platform at any time; contact us if you\'d like your platform and its data removed.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('9. Disclaimer') }}</h3>
          <p>{{ __('Oudaa is provided "as is." While we take reasonable care in operating the platform, we are not liable for disputes between a committee and its residents, for the accuracy of data entered by a committee, or for losses arising from use of the platform, to the fullest extent permitted by law.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('10. Changes to these Terms') }}</h3>
          <p>{{ __('We may update these Terms as Oudaa evolves. Continued use of a platform after changes take effect means you accept the updated Terms.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('11. Governing law') }}</h3>
          <p>{{ __('These Terms are governed by the laws of Ethiopia.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('12. Contact us') }}</h3>
          <p>{{ __('Questions about these Terms can be sent to') }} <a href="mailto:m7020322@gmail.com" class="text-primary-custom fw-semibold text-decoration-none">m7020322@gmail.com</a> {{ __('or') }} <a href="tel:+251973069687" class="text-primary-custom fw-semibold text-decoration-none">+251 973 069 687</a>.</p>

        </div>
      </div>
    </div>
  </section>
@endsection
