<?php

namespace App\Http\Controllers;

use App\Models\AdminConsentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminConsentController extends Controller
{
    /**
     * The committee member themself accepting or denying an access
     * request, from inside their own logged-in session — never from a
     * link an admin sent them. We only ever act on the record if it
     * belongs to the currently authenticated user.
     *
     * Every failure path below is logged with enough context (token,
     * user, tenant) to diagnose from Render/app logs alone, without
     * needing DB access — the generic-404-with-no-clues problem that
     * cost us a long debugging session earlier is exactly what this
     * is meant to prevent going forward.
     */
    public function respond(Request $request, string $token)
    {
        $logContext = [
            'token' => $token,
            'tenant' => $request->route('tenant'),
            'auth_id' => auth()->id(),
        ];

        try {
            $consent = AdminConsentRequest::where('token', $token)->first();
        } catch (\Throwable $e) {
            // DB/connection failure, not "no such row" — these need to be
            // told apart, since they mean very different things.
            Log::error('[GOD-ADMIN] DB error while looking up consent token.', $logContext + [
                'error' => $e->getMessage(),
            ]);

            return back()->with('status', 'Something went wrong on our end. Please try again in a moment.');
        }

        if (! $consent) {
            // This is the "no row matches this token at all" case — could
            // be a stale/expired-and-pruned link, a copy/paste error, or
            // (as we've seen before) genuinely never having reached this
            // route due to a bad deploy. Logging here means the next time
            // this happens, the answer is in the log line instead of a
            // multi-message debugging thread.
            Log::warning('[GOD-ADMIN] Consent respond attempted with unknown token.', $logContext);

            abort(404, 'This access request link is invalid or no longer exists.');
        }

        $logContext['consent_id'] = $consent->id;
        $logContext['committee_id'] = $consent->committee_id;

        if ($consent->committee_id !== auth()->id()) {
            Log::warning('[GOD-ADMIN] Consent respond attempted by a different user than it was issued to.', $logContext);

            abort(403, 'This access request was not issued to you.');
        }

        try {
            $request->validate(['decision' => 'required|in:approved,denied']);
        } catch (ValidationException $e) {
            Log::warning('[GOD-ADMIN] Consent respond had invalid/missing decision.', $logContext + [
                'errors' => $e->errors(),
            ]);

            throw $e; // let Laravel's normal validation-error redirect handle the user-facing side
        }

        if (! $consent->isPendingAndLive()) {
            Log::info('[GOD-ADMIN] Consent respond on an already-resolved/expired request.', $logContext + [
                'status' => $consent->status,
                'expires_at' => optional($consent->expires_at)->toDateTimeString(),
            ]);

            return back()->with('status', 'That request is no longer active.');
        }

        $decision = $request->input('decision');

        try {
            DB::transaction(function () use ($consent, $decision) {
                $consent->update([
                    'status' => $decision,
                    'responded_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('[GOD-ADMIN] Failed to persist consent decision.', $logContext + [
                'decision' => $decision,
                'error' => $e->getMessage(),
            ]);

            return back()->with('status', 'Something went wrong saving your response. Please try again.');
        }

        // The decision is saved regardless of whether the admin app can be
        // reached — notifyAdminApp already logs its own failures and never
        // throws, so a callback issue can't undo/hide the fact that the
        // committee member's decision was recorded.
        $this->notifyAdminApp($consent, $decision);

        try {
            \App\Models\AuditLog::record(
                'admin_access_'.$decision,
                'Committee',
                $consent->committee_id,
                'Admin dashboard access request '.$decision.' by '.(auth()->user()->name ?? 'committee member').'.'
            );
        } catch (\Throwable $e) {
            // Audit logging failing shouldn't mask a decision that was
            // already saved and already delivered — log and move on.
            Log::error('[GOD-ADMIN] Failed to write audit log for consent decision.', $logContext + [
                'decision' => $decision,
                'error' => $e->getMessage(),
            ]);
        }

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
