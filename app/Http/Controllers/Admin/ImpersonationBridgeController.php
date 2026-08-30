<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImpersonationBridgeController extends Controller
{
    public function redeem(Request $request, string $token)
    {
        $record = DB::table('admin_impersonation_tokens')->where('token', $token)->first();

        abort_if(! $record, 404, 'Invalid or already-used link.');
        abort_if($record->used_at, 410, 'This link has already been used.');
        abort_if(now()->greaterThan($record->expires_at), 410, 'This link has expired.');

        DB::table('admin_impersonation_tokens')->where('id', $record->id)->update(['used_at' => now()]);

        $user = Committee::find($record->committee_id);
        abort_unless($user, 404);

        \Log::channel('single')->warning('[GOD-ADMIN] Impersonation session started', [
            'committee_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        Auth::login($user);
        session(['impersonated_by_god_admin' => true]);

        return redirect('/')->with('status', 'Signed in.');
    }
}
