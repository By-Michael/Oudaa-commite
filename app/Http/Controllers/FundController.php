<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\Http\Request;
use App\Support\Export\Exportable;

class FundController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        $funds = $this->filtered($request)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Fund::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('funds.index', compact('funds', 'categories'));
    }

    private function filtered(Request $request)
    {
        return Fund::query()
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->when($request->fund_id, fn ($q) => $q->where('id', $request->fund_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));
    }

    private function filterSummary(Request $request): array
    {
        $lines = [];
        if ($request->status) $lines[] = "Status: {$request->status}";
        if ($request->category) $lines[] = "Category: {$request->category}";
        if ($request->fund_id) $lines[] = 'Fund #'.$request->fund_id;
        if ($request->date_from) $lines[] = "From: {$request->date_from}";
        if ($request->date_to) $lines[] = "To: {$request->date_to}";

        return $lines;
    }

    private function exportRows(Request $request): array
    {
        $headers = ['Name', 'Category', 'Status', 'Balance', 'Total Collected', 'Total Spent'];

        $rows = $this->filtered($request)->orderBy('name')->get()
            ->map(fn (Fund $f) => [
                $f->name, $f->category ?: '—', ucfirst($f->status),
                money($f->balance()),
                money($f->payments()->where('status', 'PAID')->sum('amount')),
                money($f->expenses()->sum('amount')),
            ])->all();

        return [$headers, $rows];
    }

    public function exportExcelIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportExcel('Funds', $headers, $rows, 'funds', $this->filterSummary($request));
    }

    public function exportPdfIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportPdf('Funds', $headers, $rows, 'funds', $this->filterSummary($request));
    }

    public function create()
    {
        return view('funds.form', ['fund' => new Fund()]);
    }

    public function store(Request $request)
    {
        Fund::create($this->validated($request));

        return redirect()->route('funds.index')->with('status', 'Fund created.');
    }

    public function edit(Request $request)
    {
        $fund = Fund::findOrFail($request->route('fund'));

        return view('funds.form', compact('fund'));
    }

    public function update(Request $request)
    {
        $fund = Fund::findOrFail($request->route('fund'));
        $fund->update($this->validated($request));

        return redirect()->route('funds.index')->with('status', 'Fund updated.');
    }

    /**
     * No delete for funds — archive instead, same pattern as residents.
     */
    public function archive(Request $request)
    {
        $fund = Fund::findOrFail($request->route('fund'));
        $fund->update(['status' => $fund->status === 'active' ? 'archived' : 'active']);

        return back()->with('status', 'Fund status updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,archived'],
        ]);
    }
}
