<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.edit', ['committee' => Auth::user()]);
    }

    /**
     * Update name, email, phone. Email must stay unique across committees.
     */
    public function updateProfile(Request $request)
    {
        $committee = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('committees', 'email')->ignore($committee->id)],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $committee->update($data);

        return back()->with('status', 'Profile updated.');
    }

    /**
     * Update password. Requires the current password to confirm identity.
     */
    public function updatePassword(Request $request)
    {
        $committee = Auth::user();

        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if (! Hash::check($data['current_password'], $committee->password)) {
            return back()->withErrors([
                'current_password' => 'Your current password is incorrect.',
            ])->withInput($request->only('email'));
        }

        $committee->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('status', 'Password updated.');
    }
}
