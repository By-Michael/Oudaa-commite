@component('mail::message')
# {{ __('Welcome to the committee') }}

{{ __('Hi :name,', ['name' => $committeeName]) }}

{{ __("You've been added as a committee member for **:community** on Oudaa. Click the button below to set your password and get started.", ['community' => $communityName]) }}

@component('mail::button', ['url' => $setPasswordUrl])
Set your password
@endcomponent

{{ __('This link is valid for 60 minutes and can only be used once.') }}

{{ __('If you weren\'t expecting this, you can safely ignore this email.') }}

{{ __('Thanks,') }}<br>
The Oudaa Team
@endcomponent
