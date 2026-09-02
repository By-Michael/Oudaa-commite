@component('mail::message')
# {{ __('Reset your password') }}

{{ __('Hi :name,', ['name' => $committeeName]) }}

We received a request to reset the password for your Oudaa committee account. If you made this request, click the button below to choose a new password.

@component('mail::button', ['url' => $resetUrl])
{{ __('Reset password') }}
@endcomponent

This link is valid for 60 minutes and can only be used once.

{{ __('If you didn\'t request a password reset, you can safely ignore this email — your password will not be changed.') }}

{{ __('Thanks,') }}<br>
The Oudaa Team
@endcomponent
