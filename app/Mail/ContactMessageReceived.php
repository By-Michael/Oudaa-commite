<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function build()
    {
        return $this
            ->subject('New contact form message'.($this->data['community_name'] ? ' — '.$this->data['community_name'] : ''))
            ->replyTo($this->data['email'], $this->data['full_name'])
            ->markdown('emails.contact-message');
    }
}
