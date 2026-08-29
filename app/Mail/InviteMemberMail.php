<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $setPasswordUrl, public string $committeeName, public string $communityName)
    {
    }

    public function build()
    {
        return $this->subject('You\'ve been added to '.$this->communityName.' on Oudaa')
            ->markdown('emails.invite-member');
    }
}
