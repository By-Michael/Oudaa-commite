@component('mail::message')
# New contact form message

**From:** {{ $data['full_name'] }} ({{ $data['email'] }})
@if (!empty($data['community_name']))
**Community:** {{ $data['community_name'] }}
@endif

---

{{ $data['message'] }}

---

Reply directly to this email to respond — it's addressed to {{ $data['email'] }}.
@endcomponent
