<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Fund;
use App\Models\Payment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? $request->date('from') : now()->startOfMonth();
        $to = $request->filled('to') ? $request->date('to') : now()->endOfDay();

        $payments = Payment::query()
            ->where('status', 'PAID')
            ->whereBetween('paid_at', [$from, $to]);

        $expenses = Expense::query()
            ->whereBetween('incurred_at', [$from, $to]);

        $totalCollected = (float) (clone $payments)->sum('amount');
        $totalSpent = (float) (clone $expenses)->sum('amount');
        $netChange = $totalCollected - $totalSpent;

        $collectedByFund = (clone $payments)
            ->selectRaw('fund_id, SUM(amount) as total')
            ->groupBy('fund_id')
            ->pluck('total', 'fund_id');

        $spentByFund = (clone $expenses)
            ->selectRaw('fund_id, SUM(amount) as total')
            ->groupBy('fund_id')
            ->pluck('total', 'fund_id');

        $funds = Fund::query()->orderBy('name')->get()->map(function (Fund $fund) use ($collectedByFund, $spentByFund) {
            $fund->period_collected = (float) ($collectedByFund[$fund->id] ?? 0);
            $fund->period_spent = (float) ($spentByFund[$fund->id] ?? 0);

            return $fund;
        });

        $expensesByCategory = (clone $expenses)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $paymentsByMethod = (clone $payments)
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        return view('reports.index', compact(
            'from',
            'to',
            'totalCollected',
            'totalSpent',
            'netChange',
            'funds',
            'expensesByCategory',
            'paymentsByMethod'
        ));
    }

    public function export(Request $request)
    {
        $from = $request->filled('from') ? $request->date('from') : now()->startOfMonth();
        $to = $request->filled('to') ? $request->date('to') : now()->endOfDay();

        $payments = Payment::with(['resident', 'fee', 'fund'])
            ->where('status', 'PAID')
            ->whereBetween('paid_at', [$from, $to])
            ->orderBy('paid_at')
            ->get();

        $expenses = Expense::with(['fund', 'project', 'employee'])
            ->whereBetween('incurred_at', [$from, $to])
            ->orderBy('incurred_at')
            ->get();

        $filename = 'report-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($payments, $expenses) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Type', 'Date', 'Fund', 'Category/Fee', 'Amount', 'Detail']);

            foreach ($payments as $payment) {
                fputcsv($out, [
                    'Payment',
                    $payment->paid_at->format('Y-m-d'),
                    $payment->fund->name ?? '',
                    $payment->fee->name ?? '',
                    $payment->amount,
                    $payment->resident->name ?? '',
                ]);
            }

            foreach ($expenses as $expense) {
                fputcsv($out, [
                    'Expense',
                    $expense->incurred_at->format('Y-m-d'),
                    $expense->fund->name ?? '',
                    $expense->category,
                    $expense->amount,
                    $expense->vendor ?? '',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
