@component('mail::message')
# Reset your password

Hi {{ $committeeName }},

We received a request to reset the password for your Oudaa committee account. If you made this request, click the button below to choose a new password.

@component('mail::button', ['url' => $resetUrl])
Reset password
@endcomponent

This link is valid for 60 minutes and can only be used once.

If you didn't request a password reset, you can safely ignore this email — your password will not be changed.

Thanks,<br>
The Oudaa Team
@endcomponent
