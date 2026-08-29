<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\TenantSetting;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $residents = Resident::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('unit_number', 'like', "%{$request->search}%")
                ->orWhere('block_number', 'like', "%{$request->search}%")
                ->orWhere('id_number', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->orderBy('unit_number')
            ->orderBy('block_number')
            ->orderBy('id_number')
            ->orderBy('phone')
            ->paginate(15)
            ->withQueryString();

        return view('residents.index', compact('residents'));
    }

    public function create()
    {
        return view('residents.form', [
            'resident' => new Resident(),
            'isCondo' => $this->isCondo(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = 'active'; // new residents always start active — status is only ever changed later, via edit.
        Resident::create($data);

        return redirect()->route('residents.index')->with('status', 'Resident added.');
    }

    public function edit(Request $request)
    {
        $resident = Resident::findOrFail($request->route('resident'));

        return view('residents.form', [
            'resident' => $resident,
            'isCondo' => $this->isCondo(),
        ]);
    }

    public function update(Request $request)
    {
        $resident = Resident::findOrFail($request->route('resident'));
        $data = $this->validated($request, $resident);
        $data['status'] = $request->input('status', $resident->status); // status only settable here, on edit
        $resident->update($data);

        return redirect()->route('residents.index')->with('status', 'Resident updated.');
    }

    /**
     * Only condo/apartment communities organize residents into blocks —
     * for a "normal" community the field is irrelevant, so we hide it
     * in the form and strip it out on save rather than just hiding it
     * cosmetically (see validated()).
     */
    private function isCondo(): bool
    {
        return TenantSetting::current()?->isCondo() ?? false;
    }

    /**
     * No delete for residents — deactivate instead so payment history stays intact.
     */
    public function deactivate(Request $request)
    {
        $resident = Resident::findOrFail($request->route('resident'));
        $resident->update(['status' => $resident->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'Resident status updated.');
    }

    private function validated(Request $request, ?Resident $resident = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:100', 'unique:residents,id_number,'.($resident?->id ?? 'NULL').',id'],
            'unit_number' => ['required', 'string', 'max:50'],
            'block_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'occupancy' => ['required', 'in:owner,renter'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        if (! $this->isCondo()) {
            $data['block_number'] = null;
        }

        return $data;
    }
}
