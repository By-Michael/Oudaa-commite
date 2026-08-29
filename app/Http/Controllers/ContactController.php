<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        Mail::to(env('MAIL_SUPPORT_ADDRESS', 'm7020322@gmail.com'))
            ->queue(new ContactMessageReceived($data));

        return redirect()
            ->route('landing.contact')
            ->with('status', "Thanks — we've received your message and will get back to you within one business day.");
    }
}
