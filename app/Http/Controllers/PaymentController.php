<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Fund;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;
use App\Support\Export\Exportable;

class PaymentController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        $payments = $this->filtered($request)
            ->with(['resident', 'fee', 'fund'])
            ->latest('paid_at')->latest('id')
            ->paginate(15)
            ->withQueryString();

        $fees = Fee::orderBy('name')->get();
        $residents = Resident::active()->orderBy('unit_number')->get();

        return view('payments.index', compact('payments', 'fees', 'residents'));
    }

    private function filtered(Request $request)
    {
        return Payment::query()
            ->when($request->fee_id, fn ($q) => $q->where('fee_id', $request->fee_id))
            ->when($request->fund_id, fn ($q) => $q->where('fund_id', $request->fund_id))
            ->when($request->resident_id, fn ($q) => $q->where('resident_id', $request->resident_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->method, fn ($q) => $q->where('method', $request->method))
            ->when($request->date_from, fn ($q) => $q->whereDate('paid_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('paid_at', '<=', $request->date_to));
    }

    private function filterSummary(Request $request): array
    {
        $lines = [];
        if ($request->fee_id) $lines[] = 'Fee: '.(Fee::find($request->fee_id)->name ?? '#'.$request->fee_id);
        if ($request->fund_id) $lines[] = 'Fund: '.(Fund::find($request->fund_id)->name ?? '#'.$request->fund_id);
        if ($request->resident_id) $lines[] = 'Resident: '.(Resident::find($request->resident_id)->name ?? '#'.$request->resident_id);
        if ($request->status) $lines[] = "Status: {$request->status}";
        if ($request->method) $lines[] = 'Method: '.ucfirst(str_replace('_', ' ', $request->method));
        if ($request->date_from) $lines[] = "From: {$request->date_from}";
        if ($request->date_to) $lines[] = "To: {$request->date_to}";

        return $lines;
    }

    private function exportRows(Request $request): array
    {
        $headers = ['Date', 'Resident', 'Unit', 'Fee', 'Fund', 'Amount', 'Method', 'Status', 'Note'];

        $rows = $this->filtered($request)->with(['resident', 'fee', 'fund'])
            ->latest('paid_at')->get()
            ->map(fn (Payment $p) => [
                $p->paid_at?->format('Y-m-d'),
                $p->resident->name ?? '—',
                $p->resident->unit_number ?? '—',
                $p->fee->name ?? '—',
                $p->fund->name ?? '—',
                money($p->amount),
                ucfirst(str_replace('_', ' ', $p->method)),
                $p->status,
                $p->note ?: '—',
            ])->all();

        return [$headers, $rows];
    }

    public function exportExcelIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportExcel('Payments', $headers, $rows, 'payments', $this->filterSummary($request));
    }

    public function exportPdfIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportPdf('Payments', $headers, $rows, 'payments', $this->filterSummary($request), 'landscape');
    }

    public function create()
    {
        // Inactive residents stay selectable here: someone who moved
        // out can still owe a payment from before they left, and we
        // never want to lose the ability to record that against them.
        $residents = Resident::orderByRaw("status = 'active' desc")->orderBy('unit_number')->get();
        $fees = Fee::active()->with('fund')->orderBy('name')->get();
        $funds = Fund::active()->orderBy('name')->get();

        return view('payments.form', compact('residents', 'fees', 'funds'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'resident_id' => ['required', 'exists:residents,id'],
            'fee_id' => ['nullable', 'exists:fees,id'],
            'fund_id' => ['nullable', 'exists:funds,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,bank_transfer,cheque,mobile_money,other'],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        // fee_id/fund_id are mutually exclusive in the UI, and the one
        // not chosen gets disabled client-side — disabled <select> fields
        // are never submitted at all, so the key can be fully absent from
        // $data here, not just empty. Normalize both to null up front so
        // nothing below has to guess whether the key exists.
        $data['fee_id'] = $data['fee_id'] ?? null;
        $data['fund_id'] = $data['fund_id'] ?? null;

        if (empty($data['fee_id']) && empty($data['fund_id'])) {
            return back()->withErrors([
                'fee_id' => 'Select a fee or a fund — a payment has to be attributed to one of them.',
            ])->withInput();
        }

        if (! empty($data['fee_id'])) {
            $fee = Fee::findOrFail($data['fee_id']);
            // Amount is locked to the fee's amount server-side too, so a
            // tampered client request can't override what's actually owed.
            $data['amount'] = $fee->amount;
            $data['fund_id'] = $data['fund_id'] ?: $fee->fund_id;
            $data['period_key'] = $fee->currentPeriodKey();
        }

        // New payments are always logged as PAID — status only ever
        // changes afterward, through the edit screen (e.g. a cheque
        // bounces, or a PENDING payment later clears).
        $data['status'] = 'PAID';

        Payment::create($data);

        return redirect()->route('payments.index')->with('status', 'Payment recorded.');
    }

    public function edit(Request $request)
    {
        $payment = Payment::findOrFail($request->route('payment'));
        $funds = Fund::active()->orderBy('name')->get();

        return view('payments.edit', compact('payment', 'funds'));
    }

    /**
     * Deliberately narrow: this only lets a committee member correct a
     * payment's status/fund/note after the fact (e.g. a cheque clears
     * a few days later, PENDING -> PAID). It never rewrites who paid,
     * what fee it was for, or the amount — those go through a new
     * payment entry instead, so the audit trail stays honest.
     */
    public function update(Request $request)
    {
        $payment = Payment::findOrFail($request->route('payment'));
        $data = $request->validate([
            'status' => ['required', 'in:PAID,PENDING,VOID'],
            'fund_id' => ['required_if:status,PAID', 'nullable', 'exists:funds,id'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'fund_id.required_if' => 'A fund is required to mark this payment as PAID.',
        ]);

        $payment->update($data);

        return redirect()->route('payments.index')->with('status', 'Payment status updated.');
    }
}
