<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Expense;
use App\Models\Fund;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Support\Export\Exportable;

class ExpenseController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        $expenses = $this->filtered($request)
            ->with(['fund', 'project', 'employee'])
            ->latest('incurred_at')->latest('id')
            ->paginate(15)
            ->withQueryString();

        $funds = Fund::orderBy('name')->get();
        $totalCount = Expense::count();

        return view('expenses.index', compact('expenses', 'funds', 'totalCount'));
    }

    private function filtered(Request $request)
    {
        return Expense::query()
            ->when($request->fund_id, fn ($q) => $q->where('fund_id', $request->fund_id))
            ->when($request->project_id, fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->employee_id, fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->when($request->date_from, fn ($q) => $q->whereDate('incurred_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('incurred_at', '<=', $request->date_to));
    }

    private function filterSummary(Request $request): array
    {
        $lines = [];
        if ($request->fund_id) $lines[] = 'Fund: '.(Fund::find($request->fund_id)->name ?? '#'.$request->fund_id);
        if ($request->project_id) $lines[] = 'Project: '.(Project::find($request->project_id)->name ?? '#'.$request->project_id);
        if ($request->employee_id) $lines[] = 'Employee: '.(Employee::find($request->employee_id)->name ?? '#'.$request->employee_id);
        if ($request->category) $lines[] = "Category: {$request->category}";
        if ($request->date_from) $lines[] = "From: {$request->date_from}";
        if ($request->date_to) $lines[] = "To: {$request->date_to}";

        return $lines;
    }

    private function exportRows(Request $request): array
    {
        $headers = ['Date', 'Category', 'Fund', 'Project', 'Employee', 'Vendor', 'Amount', 'Note'];

        $rows = $this->filtered($request)->with(['fund', 'project', 'employee'])
            ->latest('incurred_at')->get()
            ->map(fn (Expense $e) => [
                $e->incurred_at?->format('Y-m-d'), $e->category, $e->fund->name ?? '—',
                $e->project->name ?? '—', $e->employee->name ?? '—', $e->vendor ?: '—',
                money($e->amount), $e->note ?: '—',
            ])->all();

        return [$headers, $rows];
    }

    public function exportExcelIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportExcel('Expenses', $headers, $rows, 'expenses', $this->filterSummary($request));
    }

    public function exportPdfIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportPdf('Expenses', $headers, $rows, 'expenses', $this->filterSummary($request), 'landscape');
    }

    public function create()
    {
        $funds = Fund::active()->orderBy('name')->get();
        $projects = Project::active()->orderBy('name')->get();
        $employees = Employee::active()->orderBy('name')->get();

        return view('expenses.form', compact('funds', 'projects', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fund_id' => ['required', 'exists:funds,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'category' => ['required', 'string', 'max:255'],
            'category_other' => ['required_if:category,Other', 'nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'incurred_at' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'fund_id.required' => 'Every expense must be linked to a fund so it actually reduces that fund\'s balance.',
            'category_other.required_if' => 'Enter a category name since you selected "Other".',
            'receipt.mimes' => 'Receipt must be a JPG, PNG, or PDF file.',
            'receipt.max' => 'Receipt must be smaller than 5 MB.',
        ]);

        if (empty($data['project_id']) && empty($data['note'])) {
            return back()->withErrors([
                'note' => 'A note is required when no project is linked, so there\'s a record of what this expense was for.',
            ])->withInput();
        }

        // "Other" swaps the category value for whatever the user typed,
        // so the stored category is always a real, readable label.
        if ($data['category'] === 'Other' && ! empty($data['category_other'])) {
            $data['category'] = $data['category_other'];
        }
        unset($data['category_other']);

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }
        unset($data['receipt']);

        // If a project is selected, the expense's fund must match the
        // project's own fund — this removes the possibility of an
        // expense being attributed to a project whose spend then
        // disagrees with which fund it actually came out of.
        if (! empty($data['project_id'])) {
            $project = Project::find($data['project_id']);
            if ($project && $project->fund_id) {
                $data['fund_id'] = $project->fund_id;
            }
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('status', 'Expense recorded.');
    }
}
