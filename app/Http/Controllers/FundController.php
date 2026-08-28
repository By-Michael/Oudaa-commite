<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\Http\Request;

class FundController extends Controller
{
    public function index(Request $request)
    {
        $funds = Fund::when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
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

    public function create()
    {
        return view('funds.form', ['fund' => new Fund()]);
    }

    public function store(Request $request)
    {
        Fund::create($this->validated($request));

        return redirect()->route('funds.index')->with('status', 'Fund created.');
    }

    public function edit(Fund $fund)
    {
        return view('funds.form', compact('fund'));
    }

    public function update(Request $request, Fund $fund)
    {
        $fund->update($this->validated($request));

        return redirect()->route('funds.index')->with('status', 'Fund updated.');
    }

    /**
     * No delete for funds — archive instead, same pattern as residents.
     */
    public function archive(Fund $fund)
    {
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
