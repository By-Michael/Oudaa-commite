<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Fund;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['resident', 'fee', 'fund'])
            ->when($request->fee_id, fn ($q) => $q->where('fee_id', $request->fee_id))
            ->when($request->resident_id, fn ($q) => $q->where('resident_id', $request->resident_id))
            ->latest('paid_at')->latest('id')
            ->paginate(15)
            ->withQueryString();

        $fees = Fee::orderBy('name')->get();
        $residents = Resident::active()->orderBy('unit_number')->get();

        return view('payments.index', compact('payments', 'fees', 'residents'));
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

    public function edit(Payment $payment)
    {
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
    public function update(Request $request, Payment $payment)
    {
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
