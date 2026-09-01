<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Fund;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;
use App\Support\Export\Exportable;

class FeeController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        $fees = $this->filtered($request)
            ->with('fund')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $funds = Fund::orderBy('name')->get();
        $totalCount = Fee::count();

        return view('fees.index', compact('fees', 'funds', 'totalCount'));
    }

    private function filtered(Request $request)
    {
        return Fee::query()
            ->when($request->fund_id, fn ($q) => $q->where('fund_id', $request->fund_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->frequency, fn ($q) => $q->where('frequency', $request->frequency))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));
    }

    private function filterSummary(Request $request): array
    {
        $lines = [];
        if ($request->fund_id) $lines[] = 'Fund: '.(Fund::find($request->fund_id)->name ?? '#'.$request->fund_id);
        if ($request->status) $lines[] = "Status: {$request->status}";
        if ($request->frequency) $lines[] = "Frequency: {$request->frequency}";
        if ($request->date_from) $lines[] = "From: {$request->date_from}";
        if ($request->date_to) $lines[] = "To: {$request->date_to}";

        return $lines;
    }

    private function exportRows(Request $request): array
    {
        $headers = ['Name', 'Fund', 'Amount', 'Frequency', 'Recurrence Day', 'Status'];

        $rows = $this->filtered($request)->with('fund')->orderBy('name')->get()
            ->map(fn (Fee $f) => [
                $f->name, $f->fund->name ?? '—', money($f->amount),
                ucfirst(str_replace('_', ' ', $f->frequency)), $f->recurrenceDay(), ucfirst($f->status),
            ])->all();

        return [$headers, $rows];
    }

    public function exportExcelIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportExcel('Fees', $headers, $rows, 'fees', $this->filterSummary($request));
    }

    public function exportPdfIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportPdf('Fees', $headers, $rows, 'fees', $this->filterSummary($request));
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

    public function edit(Request $request)
    {
        $fee = Fee::findOrFail($request->route('fee'));
        $funds = Fund::active()->orderBy('name')->get();

        return view('fees.form', compact('fee', 'funds'));
    }

    public function update(Request $request)
    {
        $fee = Fee::findOrFail($request->route('fee'));
        $data = $this->validated($request);
        $data['status'] = $request->input('status', $fee->status); // status only settable here, on edit

        if (empty($data['recurrence_day'])) {
            $data['recurrence_day'] = $fee->recurrence_day ?? now()->day;
        }

        $fee->update($data);

        return redirect()->route('fees.index')->with('status', 'Fee updated.');
    }

    public function deactivate(Request $request)
    {
        $fee = Fee::findOrFail($request->route('fee'));
        $fee->update(['status' => $fee->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'Fee status updated.');
    }

    /**
     * Unpaid summary for a single fee: active residents who have no PAID
     * payment against this fee for the fee's current period.
     */
    public function unpaid(Request $request)
    {
        // Pulled explicitly by route parameter NAME via $request->route('fee')
        // rather than a bound method argument (Fee $fee, or even a plain
        // string $fee). On this {tenant}/fees/{fee}/... route, Laravel's
        // reflection-based method-parameter resolution was handing the
        // controller the WRONG route segment -- the tenant slug landed in
        // the argument meant for the fee ID (confirmed via temporary
        // logging: route_param_fee came through as the tenant's slug).
        // $request->route('name') looks the value up by its literal route
        // parameter name and sidesteps whatever reflection mismatch was
        // causing that, at the cost of being one line more explicit.
        $fee = Fee::findOrFail($request->route('fee'));

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
