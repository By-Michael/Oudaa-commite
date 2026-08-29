<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Fund;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $fees = Fee::with('fund')
            ->when($request->fund_id, fn ($q) => $q->where('fund_id', $request->fund_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $funds = Fund::orderBy('name')->get();

        return view('fees.index', compact('fees', 'funds'));
    }

    public function create()
    {
        $funds = Fund::active()->orderBy('name')->get();

        return view('fees.form', ['fee' => new Fee(), 'funds' => $funds]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = 'active'; // new fees always start active — status only changes later, via edit.

        if (empty($data['recurrence_day'])) {
            $data['recurrence_day'] = now()->day;
        }

        Fee::create($data);

        return redirect()->route('fees.index')->with('status', 'Fee created.');
    }

    public function edit(string $fee)
    {
        $fee = Fee::findOrFail($fee);
        $funds = Fund::active()->orderBy('name')->get();

        return view('fees.form', compact('fee', 'funds'));
    }

    public function update(Request $request, string $fee)
    {
        $fee = Fee::findOrFail($fee);
        $data = $this->validated($request);
        $data['status'] = $request->input('status', $fee->status); // status only settable here, on edit

        if (empty($data['recurrence_day'])) {
            $data['recurrence_day'] = $fee->recurrence_day ?? now()->day;
        }

        $fee->update($data);

        return redirect()->route('fees.index')->with('status', 'Fee updated.');
    }

    public function deactivate(string $fee)
    {
        $fee = Fee::findOrFail($fee);
        $fee->update(['status' => $fee->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'Fee status updated.');
    }

    /**
     * Unpaid summary for a single fee: active residents who have no PAID
     * payment against this fee for the fee's current period.
     */
    public function unpaid(string $fee)
    {
        // Resolved manually rather than via implicit route-model binding
        // (Fee $fee): that binding wasn't being applied for this route in
        // production, and the raw route segment arrived here as a plain
        // string instead of a Fee instance. findOrFail() still goes
        // through the BelongsToCommunity global scope, so this can't
        // fetch a fee belonging to a different community.
        $fee = Fee::findOrFail($fee);

        $periodKey = $fee->currentPeriodKey();

        $paidResidentIds = Payment::where('fee_id', $fee->id)
            ->where('status', 'PAID')
            ->where('period_key', $periodKey)
            ->pluck('resident_id');

        $unpaidResidents = Resident::active()
            ->whereNotIn('id', $paidResidentIds)
            ->orderBy('unit_number')
            ->get();

        return view('fees.unpaid', compact('fee', 'unpaidResidents', 'periodKey'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'fund_id' => ['required', 'exists:funds,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:monthly,quarterly,yearly,one_time'],
            'recurrence_day' => ['nullable', 'integer', 'between:1,31'],
        ]);
    }
}
