<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Expense;
use App\Models\Fund;
use App\Models\Project;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with(['fund', 'project', 'employee'])
            ->when($request->fund_id, fn ($q) => $q->where('fund_id', $request->fund_id))
            ->latest('incurred_at')->latest('id')
            ->paginate(15)
            ->withQueryString();

        $funds = Fund::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'funds'));
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
