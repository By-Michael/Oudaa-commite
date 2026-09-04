<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\TenantSetting;
use App\Services\PhpMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    /**
     * Any signed-in committee member can see who else has access and
     * add a new one. There's no separate admin role in v1 — every
     * committee member is trusted equally, but every action they take
     * is attributed to them individually in the audit log.
     */
    public function index()
    {
        $members = Committee::orderBy('name')->paginate(15);

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.form');
    }

    /**
     * New members never set a password here. They're created with a
     * random, unusable one (nobody knows it, so it can't be logged in
     * with) and immediately emailed a set-password link — the exact
     * same token mechanism ForgotPasswordController uses, so it's one
     * flow to reason about instead of two.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:committees,email'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $committee = Committee::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make(Str::random(40)),
        ]);

        $this->sendSetPasswordEmail($request, $committee);

        return redirect()->route('members.index')->with('status', __('Committee member added. We\'ve emailed them a link to set their password.'));
    }

    private function sendSetPasswordEmail(Request $request, Committee $committee): void
    {
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->where('email', $committee->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $committee->email,
            'token' => Hash::make($plainToken),
            'created_at' => now(),
        ]);

        $setPasswordUrl = route('password.reset', [
            'tenant' => $request->route('tenant'),
            'token' => $plainToken,
            'email' => $committee->email,
        ]);

        $communityName = TenantSetting::current()?->community_name ?? 'your community';

        app(PhpMailerService::class)->send(
            to: $committee->email,
            subject: 'You\'ve been added to '.$communityName.' on Oudaa',
            view: 'emails.invite-member',
            data: [
                'setPasswordUrl' => $setPasswordUrl,
                'committeeName' => $committee->name,
                'communityName' => $communityName,
            ],
        );
    }
}
