<?php

namespace App\Jobs;

use App\Mail\TenantProvisioned;
use App\Models\Central\Tenant;
use App\Models\TenantSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Everything that has to happen between "user finished the signup
 * wizard" and "community is ready to be logged into":
 *
 *   1. create a brand new SQLite file for this tenant
 *   2. run every tenant migration against it (same migrations that
 *      already run for the single-tenant version of this app)
 *   3. seed the tenant_settings row (community name/type)
 *   4. generate a one-time "set your password" link and email it
 *
 * Queued rather than run inline so signup responds instantly instead
 * of making the user wait on migrations + an outbound email.
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
            $this->createDatabaseFile($tenant->db_path);
            $this->pointTenantConnectionAt($tenant->db_path);

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true,
            ]);

            TenantSetting::create([
                'community_name' => $tenant->name,
                'community_type' => $tenant->community_type,
            ]);

            $rawToken = Str::random(48);

            $tenant->update([
                'status' => 'pending_setup',
                'setup_token' => Hash::make($rawToken),
                'setup_token_expires_at' => now()->addHours((int) config('tenancy.setup_link_ttl_hours')),
            ]);

            Mail::to($tenant->owner_email)->queue(
                new TenantProvisioned($tenant, $rawToken)
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

    protected function createDatabaseFile(string $path): void
    {
        File::ensureDirectoryExists(dirname($path));

        if (! File::exists($path)) {
            File::put($path, '');
        }
    }

    protected function pointTenantConnectionAt(string $path): void
    {
        Config::set('database.connections.tenant.database', $path);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
