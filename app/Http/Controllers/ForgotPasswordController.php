<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Manual (non-Password-broker) forgot/reset password flow, scoped to the
 * current tenant's Committee table. Kept manual — rather than the built-in
 * Password facade — because that facade's default notification builds a
 * non-tenant-aware URL, and every route here needs the {tenant} slug.
 */
class ForgotPasswordController extends Controller
{
    // How long a reset link stays valid, in minutes.
    private const TOKEN_TTL_MINUTES = 60;

    public function showRequestForm(string $tenant)
    {
        return view('auth.forgot-password', ['tenant' => $tenant]);
    }

    public function sendResetLink(Request $request, string $tenant)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $committee = Committee::where('email', $request->input('email'))->first();

        // Always respond the same way whether or not the email is
        // registered, so this form can't be used to enumerate accounts.
        if ($committee) {
            $plainToken = Str::random(64);

            DB::table('password_reset_tokens')->where('email', $committee->email)->delete();
            DB::table('password_reset_tokens')->insert([
                'email' => $committee->email,
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]);

            $resetUrl = route('password.reset', [
                'tenant' => $tenant,
                'token' => $plainToken,
                'email' => $committee->email,
            ]);

            Mail::to($committee->email)->send(new ResetPasswordMail($resetUrl, $committee->name));
        }

        return back()->with('status', __('If that email is registered, a password reset link is on its way to it.'));
    }

    public function showResetForm(Request $request, string $tenant, string $token)
    {
        return view('auth.reset-password', [
            'tenant' => $tenant,
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request, string $tenant, string $token)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $row || ! Hash::check($token, $row->token)) {
            return back()->withErrors(['email' => __('This password reset link is invalid.')])->onlyInput('email');
        }

        if (now()->diffInMinutes($row->created_at) > self::TOKEN_TTL_MINUTES) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

            return back()->withErrors(['email' => __('This password reset link has expired. Please request a new one.')])->onlyInput('email');
        }

        $committee = Committee::where('email', $data['email'])->first();

        if (! $committee) {
            return back()->withErrors(['email' => __('This password reset link is invalid.')])->onlyInput('email');
        }

        $committee->update(['password' => Hash::make($data['password'])]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login', ['tenant' => $tenant])
            ->with('status', __('Your password has been reset. You can log in now.'));
    }
}
