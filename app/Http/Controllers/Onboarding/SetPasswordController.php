<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Committee;
use App\Support\CurrentCommunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

/**
 * The page behind the signed link emailed by ProvisionTenant. Sets the
 * password for the very first (admin) Committee account for this
 * community, then flips the tenant to 'active'.
 *
 * Note this runs OUTSIDE the {tenant}/... route group (no ResolveTenant
 * middleware) because at this point the person isn't logged into a
 * tenant yet — they're proving ownership via the signed URL. So it
 * sets CurrentCommunity manually instead of relying on that middleware,
 * which is what makes the Committee::updateOrCreate() below land on
 * (and stay scoped to) the right community.
 */
class SetPasswordController extends Controller
{
    public function show(Request $request, string $tenant, string $token)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This link is invalid or has expired.');
        }

        $tenantModel = $this->validTenantOrAbort($tenant, $token);

        // The GET link's signature is only valid for this route+params
        // combo. The form must POST to a separately signed URL for the
        // 'store' route, otherwise the 'signed' middleware there will
        // reject the submission as an invalid signature.
        $storeUrl = URL::temporarySignedRoute(
            'tenants.set-password.store',
            now()->addMinutes(30),
            ['tenant' => $tenantModel->slug, 'token' => $token]
        );

        return view('onboarding.set-password', [
            'tenant' => $tenantModel,
            'token' => $token,
            'storeUrl' => $storeUrl,
        ]);
    }

    public function store(Request $request, string $tenant, string $token)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This link is invalid or has expired.');
        }

        $tenantModel = $this->validTenantOrAbort($tenant, $token);

        $data = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        app(CurrentCommunity::class)->set($tenantModel->id);

        Committee::updateOrCreate(
            ['email' => $tenantModel->owner_email],
            [
                'name' => $tenantModel->name.' Admin',
                'password' => Hash::make($data['password']),
            ]
        );

        $tenantModel->update([
            'status' => 'active',
            'setup_token' => null,
            'setup_token_expires_at' => null,
        ]);

        return redirect()->route('login', ['tenant' => $tenantModel->slug])
            ->with('status', __('Password set — you can log in now.'));
    }

    protected function validTenantOrAbort(string $slug, string $rawToken): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->first();

        if (! $tenant || ! $tenant->setup_token) {
            abort(404, 'This setup link has already been used.');
        }

        if ($tenant->setup_token_expires_at?->isPast()) {
            abort(403, 'This setup link has expired. Contact support to get a new one.');
        }

        if (! Hash::check($rawToken, $tenant->setup_token)) {
            abort(403, 'This link is invalid.');
        }

        return $tenant;
    }
}
