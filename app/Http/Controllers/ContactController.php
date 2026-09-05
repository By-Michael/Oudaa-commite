<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Services\SafeMail;
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

        $sent = SafeMail::queue(
            new ContactMessageReceived($data),
            env('MAIL_SUPPORT_ADDRESS', 'm7020322@gmail.com'),
            ['context' => 'contact_form', 'submitter_email' => $data['email']]
        );

        if (! $sent) {
            return redirect()
                ->route('landing.contact')
                ->with('error', "Sorry — we couldn't send your message right now. Please try again in a bit, or email us directly.");
        }

        return redirect()
            ->route('landing.contact')
            ->with('status', "Thanks — we've received your message and will get back to you within one business day.");
    }
}
