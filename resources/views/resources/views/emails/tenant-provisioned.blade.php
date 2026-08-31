@component('mail::message')
# Welcome to Oudaa, {{ $tenant->name }}

Your community platform has been created and is ready to be set up.

- **Community:** {{ $tenant->name }}
- **Your platform link:** {{ url('/'.$tenant->slug) }}

Before you can log in, set a password for your admin account.

@component('mail::button', ['url' => $setupUrl])
Set your password
@endcomponent

This link is valid for {{ (int) (config('tenancy.setup_link_ttl_hours') / 24) }} days and can only be used once.

If you didn't request this, you can safely ignore this email.

Thanks,<br>
The Oudaa Team
@endcomponent
