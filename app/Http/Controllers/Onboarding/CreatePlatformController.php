<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Jobs\ProvisionTenant;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The 3-step "Create your platform" wizard from the landing page:
 *   1. community name
 *   2. url slug (live-checked) + community type
 *   3. owner email -> creates the Tenant row and queues provisioning
 *
 * State between steps is kept in the session under 'onboarding.*'
 * rather than round-tripping every field through hidden inputs, so a
 * user can navigate back a step without losing what they already typed.
 */
class CreatePlatformController extends Controller
{
    public function step1()
    {
        return view('onboarding.step1', [
            'name' => Session::get('onboarding.name'),
        ]);
    }

    public function step1Store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        Session::put('onboarding.name', $data['name']);

        // Pre-fill a suggested slug so step 2 doesn't open on a blank field.
        if (! Session::has('onboarding.slug')) {
            Session::put('onboarding.slug', $this->suggestSlug($data['name']));
        }

        return redirect()->route('onboarding.step2');
    }

    public function step2()
    {
        if (! Session::has('onboarding.name')) {
            return redirect()->route('onboarding.step1');
        }

        return view('onboarding.step2', [
            'slug' => Session::get('onboarding.slug'),
            'communityType' => Session::get('onboarding.community_type', 'normal'),
        ]);
    }

    public function step2Store(Request $request)
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'community_type' => ['required', Rule::in(['normal', 'condo'])],
        ]);

        if (! $this->slugAvailable($data['slug'])) {
            return back()->withErrors([
                'slug' => 'That link is already taken. Try another one.',
            ])->withInput();
        }

        Session::put('onboarding.slug', $data['slug']);
        Session::put('onboarding.community_type', $data['community_type']);

        return redirect()->route('onboarding.step3');
    }

    public function step3()
    {
        if (! Session::has('onboarding.slug')) {
            return redirect()->route('onboarding.step1');
        }

        return view('onboarding.step3', [
            'name' => Session::get('onboarding.name'),
            'slug' => Session::get('onboarding.slug'),
        ]);
    }

    public function step3Store(Request $request)
    {
        if (! Session::has('onboarding.slug')) {
            return redirect()->route('onboarding.step1');
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $slug = Session::get('onboarding.slug');

        // Re-check availability at submit time too — someone else could
        // have taken this slug between step 2 and now.
        if (! $this->slugAvailable($slug)) {
            Session::forget('onboarding.slug');

            return redirect()->route('onboarding.step2')->withErrors([
                'slug' => 'That link was just taken by someone else. Please choose another.',
            ]);
        }

        $tenant = Tenant::create([
            'name' => Session::get('onboarding.name'),
            'slug' => $slug,
            'community_type' => Session::get('onboarding.community_type', 'normal'),
            'owner_email' => $data['email'],
            'status' => 'pending_setup',
        ]);

        // Still a single process / single instance — no queue worker
        // required. afterResponse() sends the redirect to the browser
        // first, then PHP keeps running in the background (within the
        // same request's process) to provision the tenant and send the
        // welcome email. That means a slow/hung SMTP connection can no
        // longer block the page load, even with QUEUE_CONNECTION=sync.
        ProvisionTenant::dispatch($tenant->id)->afterResponse();

        Session::forget(['onboarding.name', 'onboarding.slug', 'onboarding.community_type']);

        return redirect()->route('onboarding.thank-you', ['tenant' => $tenant->slug]);
    }

    public function thankYou(Request $request)
    {
        $tenant = Tenant::where('slug', $request->route('tenant'))->firstOrFail();

        return view('onboarding.thank-you', ['tenant' => $tenant]);
    }

    /**
     * AJAX endpoint the step-2 form calls as the user types, so they
     * see "taken" / "available" before they even submit.
     */
    public function checkSlug(Request $request)
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:60'],
        ]);

        $slug = Str::slug($data['slug']);
        $available = $slug !== '' && $this->slugAvailable($slug);

        return response()->json([
            'slug' => $slug,
            'available' => $available,
            'suggestion' => $available ? null : $this->suggestSlug($slug, true),
        ]);
    }

    protected function slugAvailable(string $slug): bool
    {
        if (in_array($slug, config('tenancy.reserved_slugs'), true)) {
            return false;
        }

        return ! Tenant::where('slug', $slug)->exists();
    }

    /**
     * "green valley" -> "green-valley", or "green-valley-2",
     * "green-valley-3"... if that's already taken.
     */
    protected function suggestSlug(string $seed, bool $alreadySlugified = false): string
    {
        $base = $alreadySlugified ? $seed : Str::slug($seed);
        $slug = $base;
        $suffix = 2;

        while (! $this->slugAvailable($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
