<?php

namespace App\Http\Controllers;

use App\Jobs\SendPhpMailerEmail;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'community_name' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
            // Honeypot: real users never see/fill this field (hidden via CSS).
            // Any value here means it was almost certainly filled by a bot.
            'website' => ['prohibited'],
        ]);

        SendPhpMailerEmail::dispatch(
            to: env('MAIL_SUPPORT_ADDRESS', 'm7020322@gmail.com'),
            subject: 'New contact form message'.($data['community_name'] ? ' — '.$data['community_name'] : ''),
            view: 'emails.contact-message',
            data: ['data' => $data],
            replyToEmail: $data['email'],
            replyToName: $data['full_name'],
        );

        return redirect()
            ->route('landing.contact')
            ->with('status', "Thanks — we've received your message and will get back to you within one business day.");
    }
}
