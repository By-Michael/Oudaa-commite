@component('mail::message')
# Welcome to the committee

Hi {{ $committeeName }},

You've been added as a committee member for **{{ $communityName }}** on Oudaa. Click the button below to set your password and get started.

@component('mail::button', ['url' => $setPasswordUrl])
Set your password
@endcomponent

This link is valid for 60 minutes and can only be used once.

If you weren't expecting this, you can safely ignore this email.

Thanks,<br>
The Oudaa Team
@endcomponent
