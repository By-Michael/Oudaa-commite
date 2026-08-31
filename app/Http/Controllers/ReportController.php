<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Expense;
use App\Models\Fee;
use App\Models\Fund;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Resident;
use App\Support\Export\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    use Exportable;

    /**
     * Reports dashboard: five charts, each chosen for the shape of the
     * data it's showing rather than picked at random —
     *   - Payments over time: a trend, so a line chart.
     *   - Expenses by category: a small set of comparable buckets, bar.
     *   - Fund balances: comparing named entities side by side, bar.
     *   - Residents by status/occupancy: parts of a whole, doughnut.
     *   - Project budget vs spent: two paired values per project, grouped bar.
     */
    public function index(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        // Grouped in PHP rather than SQL (GROUP BY DATE_FORMAT/strftime)
        // so this works the same whether the app is on MySQL or SQLite.
        $paymentsByMonth = Payment::where('status', 'PAID')
            ->whereBetween('paid_at', [$from, $to])
            ->get(['amount', 'paid_at'])
            ->groupBy(fn (Payment $p) => $p->paid_at->format('Y-m'))
            ->map(fn ($group, $ym) => ['ym' => $ym, 'total' => (float) $group->sum('amount')])
            ->sortKeys()->values();

        $expensesByCategory = Expense::whereBetween('incurred_at', [$from, $to])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')->orderByDesc('total')->get();

        $fundBalances = Fund::active()->get()->map(fn (Fund $f) => [
            'name' => $f->name,
            'balance' => $f->balance(),
        ])->sortByDesc('balance')->values();

        $residentBreakdown = [
            'active' => Resident::where('status', 'active')->count(),
            'inactive' => Resident::where('status', 'inactive')->count(),
            'owner' => Resident::where('occupancy', 'owner')->count(),
            'renter' => Resident::where('occupancy', 'renter')->count(),
        ];

        $projectBudgets = Project::active()->get()->map(fn (Project $p) => [
            'name' => $p->name,
            'planned' => (float) $p->planned_budget,
            'spent' => $p->spent(),
        ])->values();

        return view('reports.index', [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'paymentsByMonth' => $paymentsByMonth,
            'expensesByCategory' => $expensesByCategory,
            'fundBalances' => $fundBalances,
            'residentBreakdown' => $residentBreakdown,
            'projectBudgets' => $projectBudgets,
            'funds' => Fund::orderBy('name')->get(),
            'residents' => Resident::orderBy('unit_number')->get(),
            'employees' => Employee::orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'fees' => Fee::orderBy('name')->get(),
        ]);
    }

    /**
     * One consolidated PDF: totals and top breakdowns across every
     * dataset in the panel, for a single "hand this to the board" export.
     */
    public function summaryPdf(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $totalCollected = (float) Payment::where('status', 'PAID')->whereBetween('paid_at', [$from, $to])->sum('amount');
        $totalSpent = (float) Expense::whereBetween('incurred_at', [$from, $to])->sum('amount');
        $funds = Fund::active()->get();
        $totalFundsBalance = $funds->sum(fn (Fund $f) => $f->balance());

        $data = [
            'from' => $from,
            'to' => $to,
            'generatedAt' => now(),
            'totalCollected' => $totalCollected,
            'totalSpent' => $totalSpent,
            'totalFundsBalance' => $totalFundsBalance,
            'residentsActive' => Resident::where('status', 'active')->count(),
            'residentsTotal' => Resident::count(),
            'employeesActive' => Employee::where('status', 'active')->count(),
            'employeesTotal' => Employee::count(),
            'projectsActive' => Project::whereIn('status', ['planned', 'active'])->count(),
            'projectsTotal' => Project::count(),
            'funds' => $funds,
            'expensesByCategory' => Expense::whereBetween('incurred_at', [$from, $to])
                ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get(),
            'topProjects' => Project::active()->get()->sortByDesc(fn (Project $p) => $p->spent())->take(8),
            'recentPayments' => Payment::with(['resident', 'fee'])
                ->whereBetween('paid_at', [$from, $to])->latest('paid_at')->take(10)->get(),
            'recentExpenses' => Expense::with(['fund', 'project'])
                ->whereBetween('incurred_at', [$from, $to])->latest('incurred_at')->take(10)->get(),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.summary-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('summary-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.pdf');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->subMonths(11)->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }
}
