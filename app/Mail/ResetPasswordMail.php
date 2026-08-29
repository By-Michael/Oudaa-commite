<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $resetUrl, public string $committeeName)
    {
    }

    public function build()
    {
        return $this->subject('Reset your Oudaa password')
            ->markdown('emails.reset-password');
    }
}
