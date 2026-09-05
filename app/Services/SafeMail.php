<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Thin wrapper around Mail::to()->send()/queue().
 *
 * Every call site in the app that sends email goes through here instead
 * of the Mail facade directly, so a failure (bad API key, Brevo outage,
 * unverified sender, rate limit, etc.) always:
 *
 *   1. Gets caught instead of bubbling up as a raw 500 to the user.
 *   2. Gets logged at 'error' level with enough context to diagnose —
 *      which mailable, which recipient, the exact exception — and,
 *      because 'error' is above LOG_DB_LEVEL's default of 'info', it's
 *      guaranteed to land in the log_entries table and show up in the
 *      admin dashboard's Logs panel, not just wherever stderr happens
 *      to be going.
 *   3. Returns a plain bool so the caller can decide what the *user*
 *      sees, instead of every mail call site reinventing its own
 *      try/catch (and inevitably forgetting one).
 *
 * Pass rethrow: true for call sites (like queued jobs) that already
 * have their own outer catch and need the exception to keep propagating
 * after it's been logged here.
 */
class SafeMail
{
    public static function send(Mailable $mailable, string $to, array $context = [], bool $rethrow = false): bool
    {
        return static::attempt(fn () => Mail::to($to)->send($mailable), $mailable, $to, $context, $rethrow);
    }

    /**
     * Dispatches via the mail queue. On this app's QUEUE_CONNECTION=sync
     * this runs — and can fail — inline, exactly like send(), so it gets
     * the same guarantees.
     */
    public static function queue(Mailable $mailable, string $to, array $context = [], bool $rethrow = false): bool
    {
        return static::attempt(fn () => Mail::to($to)->queue($mailable), $mailable, $to, $context, $rethrow);
    }

    private static function attempt(callable $fn, Mailable $mailable, string $to, array $context, bool $rethrow): bool
    {
        try {
            $fn();

            return true;
        } catch (Throwable $e) {
            Log::error('Mail send failed', array_merge([
                'mailable' => get_class($mailable),
                'to' => $to,
                'mailer' => config('mail.default'),
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ], $context));

            if ($rethrow) {
                throw $e;
            }

            return false;
        }
    }
}
