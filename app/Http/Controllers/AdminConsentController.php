<?php

namespace App\Http\Controllers;

use App\Models\AdminConsentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminConsentController extends Controller
{
    /**
     * The committee member themself accepting or denying an access
     * request, from inside their own logged-in session — never from a
     * link an admin sent them. We only ever act on the record if it
     * belongs to the currently authenticated user.
     */
    public function respond(Request $request, string $token)
    {
        $consent = AdminConsentRequest::where('token', $token)->firstOrFail();

        abort_unless($consent->committee_id === auth()->id(), 403);

        $request->validate(['decision' => 'required|in:approved,denied']);

        if (! $consent->isPendingAndLive()) {
            return back()->with('status', 'That request is no longer active.');
        }

        $decision = $request->input('decision');

        $consent->update([
            'status' => $decision,
            'responded_at' => now(),
        ]);

        $this->notifyAdminApp($consent, $decision);

        \App\Models\AuditLog::record(
            'admin_access_'.$decision,
            'Committee',
            $consent->committee_id,
            'Admin dashboard access request '.$decision.' by '.(auth()->user()->name ?? 'committee member').'.'
        );

        return back()->with('status', $decision === 'approved'
            ? 'Access approved. The admin can now proceed.'
            : 'Access request denied.');
    }

    private function notifyAdminApp(AdminConsentRequest $consent, string $decision): void
    {
        $secret = config('services.admin_agent.secret');
        if (! $secret) {
            \Log::error('[GOD-ADMIN] Cannot notify admin app of consent decision: no shared secret configured.');
            return;
        }

        $bodyJson = json_encode([
            'decision' => $decision,
            'responded_by' => auth()->user()->name ?? auth()->user()->email ?? 'committee member',
        ]);

        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$bodyJson, $secret);

        try {
            $response = Http::withHeaders([
                'X-Admin-Timestamp' => $timestamp,
                'X-Admin-Signature' => $signature,
            ])->withBody($bodyJson, 'application/json')
                ->timeout(15)
                ->connectTimeout(8)
                ->post($consent->callback_url);

            if (! $response->successful()) {
                \Log::error('[GOD-ADMIN] Admin app rejected consent decision callback.', [
                    'consent_id' => $consent->id,
                    'callback_url' => $consent->callback_url,
                    'status' => $response->status(),
                    'body' => \Illuminate\Support\Str::limit($response->body(), 500),
                ]);
                return;
            }

            \Log::info('[GOD-ADMIN] Consent decision delivered to admin app.', [
                'consent_id' => $consent->id,
                'decision' => $decision,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[GOD-ADMIN] Failed to deliver consent decision to admin app (connection/timeout).', [
                'consent_id' => $consent->id,
                'callback_url' => $consent->callback_url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
