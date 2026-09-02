@component('mail::message')
# {{ __('New contact form message') }}

**From:** {{ $data['full_name'] }} ({{ $data['email'] }})
@if (!empty($data['community_name']))
**{{ __('Community:') }}** {{ $data['community_name'] }}
@endif

---

{{ $data['message'] }}

---

{{ __("Reply directly to this email to respond — it's addressed to :email.", ['email' => $data['email']]) }}
@endcomponent
