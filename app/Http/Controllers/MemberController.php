<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $members = Committee::orderBy('name')->get();

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:committees,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        Committee::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('members.index')->with('status', 'Committee member added.');
    }
}
