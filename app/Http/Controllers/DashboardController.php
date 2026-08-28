<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Fund;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $funds = Fund::active()->get();
        $totalFundsBalance = $funds->sum(fn (Fund $fund) => $fund->balance());

        $totalCollected = (float) Payment::where('status', 'PAID')->sum('amount');
        $totalSpent = (float) Expense::sum('amount');

        $recentPayments = Payment::with(['resident', 'fee'])
            ->latest('paid_at')->latest('id')->take(6)->get();

        $recentExpenses = Expense::with('fund')
            ->latest('incurred_at')->latest('id')->take(6)->get();

        return view('dashboard.index', compact(
            'totalFundsBalance',
            'totalCollected',
            'totalSpent',
            'recentPayments',
            'recentExpenses',
            'funds',
        ));
    }
}
