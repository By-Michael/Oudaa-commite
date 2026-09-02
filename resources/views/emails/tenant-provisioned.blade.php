@component('mail::message')
# {{ __('Welcome to Oudaa, :name', ['name' => $tenant->name]) }}

{{ __('Your community platform has been created and is ready to be set up.') }}

- **{{ __('Community:') }}** {{ $tenant->name }}
- **Your platform link:** {{ url('/'.$tenant->slug) }}

{{ __('Before you can log in, set a password for your admin account.') }}

@component('mail::button', ['url' => $setupUrl])
{{ __('Set your password') }}
@endcomponent

{{ __('This link is valid for :days days and can only be used once.', ['days' => (int) (config('tenancy.setup_link_ttl_hours') / 24)]) }}

If you didn't request this, you can safely ignore this email.

{{ __('Thanks,') }}<br>
The Oudaa Team
@endcomponent
