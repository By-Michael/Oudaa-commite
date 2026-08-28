<?php

namespace App\Mail;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TenantProvisioned extends Mailable
{
    use Queueable, SerializesModels;

    public string $setupUrl;

    public function __construct(public Tenant $tenant, string $rawToken)
    {
        $this->setupUrl = URL::temporarySignedRoute(
            'tenants.set-password.show',
            $tenant->setup_token_expires_at,
            ['tenant' => $tenant->slug, 'token' => $rawToken]
        );
    }

    public function build()
    {
        return $this->subject("Your {$this->tenant->name} platform is ready")
            ->markdown('emails.tenant-provisioned');
    }
}
