<?php

namespace App\Jobs;

use App\Mail\TenantProvisioned;
use App\Models\Central\Tenant;
use App\Models\TenantSetting;
use App\Services\SafeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Everything that has to happen between "user finished the signup
 * wizard" and "community is ready to be logged into":
 *
 *   1. seed the tenant_settings row (community name/type)
 *   2. generate a one-time "set your password" link and email it
 *
 * There used to be a step 0 here — create a SQLite file and run every
 * tenant migration against it. That's gone: every community now
 * shares the one already-migrated database (see the community_id
 * migration), so there's no schema to stand up per signup. That's
 * also *why* this can stay ShouldQueue but run happily on the 'sync'
 * driver (see .env.example) with no real worker process: there's no
 * slow migration step left to hide behind a queue, just an insert and
 * an email.
 */
class ProvisionTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $tenantId)
    {
    }

    public function handle(): void
    {
        $tenant = Tenant::findOrFail($this->tenantId);

        try {
            // Explicit community_id: BelongsToCommunity's auto-fill
            // only fires when CurrentCommunity is set by ResolveTenant
            // on a /{tenant}/... request, which this isn't — signup
            // happens on the unprefixed onboarding routes.
            TenantSetting::create([
                'community_id' => $tenant->id,
                'community_name' => $tenant->name,
                'community_type' => $tenant->community_type,
            ]);

            $rawToken = Str::random(48);

            $tenant->update([
                'status' => 'pending_setup',
                'setup_token' => Hash::make($rawToken),
                'setup_token_expires_at' => now()->addHours((int) config('tenancy.setup_link_ttl_hours')),
            ]);

            SafeMail::queue(
                new TenantProvisioned($tenant, $rawToken),
                $tenant->owner_email,
                ['context' => 'tenant_provisioned', 'tenant_id' => $tenant->id],
                rethrow: true
            );
        } catch (Throwable $e) {
            Log::error('Tenant provisioning failed', [
                'tenant_id' => $tenant->id,
                'slug' => $tenant->slug,
                'error' => $e->getMessage(),
            ]);

            $tenant->update([
                'status' => 'failed',
                'provisioning_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
