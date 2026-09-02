@extends('layouts.landing')

@section('title', __('Privacy Policy') . ' — Oudaa')
@section('meta_description', "How Oudaa collects, uses, and protects data for committees and the communities they manage.")

@section('content')
<header class="page-header">
    <div class="container">
      <h1 class="mb-3">{{ __('Privacy Policy') }}</h1>
      <div class="breadcrumb-custom">
        <a href="{{ route('landing.index') }}">{{ __('Home') }}</a> <span>/</span> <span class="active">{{ __('Privacy Policy') }}</span>
      </div>
    </div>
  </header>

  <section class="section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9 reveal">

          <p class="text-slate mb-5">{{ __('Last updated:') }} {!! eth_date(now(), 'F j, Y') !!}</p>

          <p>{{ __('This Privacy Policy explains how Oudaa ("Oudaa," "we," "us") handles information when a committee creates a platform and uses it to manage its community. By creating a platform or using Oudaa, you agree to the practices described here.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('1. Who this applies to') }}</h3>
          <p>Oudaa is used by community committees (administrators) to keep records about residents, fees, funds, payments, projects and expenses. This policy covers two kinds of people:</p>
          <ul class="list-check mb-4">
            <li><i class="bi bi-check"></i><div><strong class="text-navy">{{ __('Committee members') }}</strong> — people who sign up, create a platform, and log in to manage it.</div></li>
            <li><i class="bi bi-check"></i><div><strong class="text-navy">{{ __('Residents') }}</strong> — people whose records a committee enters into the platform. Residents do not create their own accounts or log in; their information is entered and managed by the committee.</div></li>
          </ul>

          <h3 class="mt-5 mb-3">{{ __('2. What we collect') }}</h3>
          <p><strong class="text-navy">From committee members (account holders):</strong> {{ __('name, email address, password (stored hashed, never in plain text), and the community/platform details provided during signup (community name, chosen subdomain, community type).') }}</p>
          <p><strong class="text-navy">{{ __('Resident records entered by a committee:') }}</strong> {{ __('name, ID number, unit and, where applicable, block number, contact details, and occupancy status (owner or tenant). This data belongs to the committee that entered it — Oudaa does not collect it directly from residents.') }}</p>
          <p><strong class="text-navy">{{ __('Financial records:') }}</strong> fees, payments, fund balances, projects and expenses that a committee logs. Oudaa does not process card payments and does not collect or store payment card numbers. Payments recorded in the platform reflect fees collected offline (cash, bank transfer, or however the committee actually collects money) — Oudaa is a record-keeping tool for that activity, not a payment processor.</p>
          <p><strong class="text-navy">{{ __('Automatically collected:') }}</strong> basic technical data such as IP address, browser type, and access logs, used for security and troubleshooting.</p>

          <h3 class="mt-5 mb-3">{{ __('3. Data isolation between communities') }}</h3>
          <p>{{ __('All communities\' data is stored in a single, shared database, with each record tagged to the specific community it belongs to. Access controls in the application enforce that data entered by one community is never visible to, or mixed with, another community\'s data. Committee members can only see the data that belongs to their own platform.') }}</p>

          <h3 class="mt-5 mb-3">{{ __('4. How we use data') }}</h3>
          <ul class="list-check mb-4">
            <li><i class="bi bi-check"></i><div>To create and operate each community's platform.</div></li>
            <li><i class="bi bi-check"></i><div>{{ __('To send account-related emails — password setup links, login-related notices, and replies to inquiries submitted through the Contact page.') }}</div></li>
            <li><i class="bi bi-check"></i><div>{{ __('To maintain the security, integrity and audit trail of records within a platform.') }}</div></li>
            <li><i class="bi bi-check"></i><div>{{ __('To respond to support requests.') }}</div></li>
          </ul>
          <p>We do not sell resident or committee data, and we do not use it for advertising.</p>

          <h3 class="mt-5 mb-3">{{ __('5. Who can see your data') }}</h3>
          <p>Resident and financial records are visible only to the committee members of that specific platform, according to whatever access each member has been given. Oudaa's operators may access underlying data only when necessary to provide support, maintain the platform, or comply with the law.</p>

          <h3 class="mt-5 mb-3">{{ __('6. Data retention') }}</h3>
          <p>Records — including residents, fees, funds, payments, projects and expenses — are kept for as long as a community's platform is active. Deactivating a resident or retiring a fee does not delete its history; committees can archive rather than permanently delete records so past activity stays auditable.</p>

          <h3 class="mt-5 mb-3">{{ __('7. Your rights') }}</h3>
          <p>If you are a resident and want to know what information a committee has recorded about you, or want it corrected, please contact that community's committee directly, as they control their own platform's data. If you are a committee member and want your account or platform data removed, or have any other request, contact us using the details below.</p>

          <h3 class="mt-5 mb-3">{{ __('8. Security') }}</h3>
          <p>We use reasonable technical and organizational measures to protect data — including application-level access controls that isolate each community's records and hashed passwords — but no system is completely secure, and we cannot guarantee absolute security.</p>

          <h3 class="mt-5 mb-3">{{ __('9. Changes to this policy') }}</h3>
          <p>We may update this policy as Oudaa evolves. Material changes will be reflected by updating the "Last updated" date above.</p>

          <h3 class="mt-5 mb-3">{{ __('10. Governing law') }}</h3>
          <p>This policy is governed by the laws of Ethiopia.</p>

          <h3 class="mt-5 mb-3">{{ __('11. Contact us') }}</h3>
          <p>Questions about this policy or your data can be sent to <a href="mailto:m7020322@gmail.com" class="text-primary-custom fw-semibold text-decoration-none">m7020322@gmail.com</a> {{ __('or') }} <a href="tel:+251973069687" class="text-primary-custom fw-semibold text-decoration-none">+251 973 069 687</a>.</p>

        </div>
      </div>
    </div>
  </section>
@endsection
