@extends('layouts.app')
@section('title', 'Expenses')
@section('content')

<x-list-header
    title="Expenses"
    noun="expenses"
    :shown="$expenses->total()"
    :total="$totalCount"
    :export-excel="route('expenses.export.excel', request()->query())"
    :export-pdf="route('expenses.export.pdf', request()->query())"
    panel-id="filters-expenses"
>
    <x-slot:icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></x-slot:icon>

    <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;">
        <select name="fund_id">
            <option value="">All funds</option>
            @foreach ($funds as $fund)
                <option value="{{ $fund->id }}" @selected(request('fund_id') == $fund->id)>{{ $fund->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}">
        <input type="date" name="date_to" value="{{ request('date_to') }}">
        <button class="btn btn-sm" type="submit">Apply</button>
    </form>
</x-list-header>

<div class="toolbar">
    <div class="toolbar-actions" style="margin-left:auto;">
        <a href="{{ route('expenses.create') }}" class="js-modal-link btn btn-primary">+ Record Expense</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($expenses->isEmpty())
            <div class="empty">No expenses recorded yet.</div>
        @else
            <table>
                <thead>
                <tr><th>Date</th><th>Category</th><th>Vendor</th><th>Fund</th><th>Project</th><th>Employee</th><th class="right">Amount</th><th>Note</th><th>Receipt</th></tr>
                </thead>
                <tbody>
                @foreach ($expenses as $expense)
                    <tr>
                        <td>{{ $expense->incurred_at->format('Y-m-d') }}</td>
                        <td>{{ $expense->category }}</td>
                        <td>{{ $expense->vendor ?: '—' }}</td>
                        <td>{{ $expense->fund->name ?? '—' }}</td>
                        <td>{{ $expense->project->name ?? '—' }}</td>
                        <td>{{ $expense->employee->name ?? '—' }}</td>
                        <td class="right">{{ money($expense->amount) }}</td>
                        <td>{{ $expense->note ?: '—' }}</td>
                        <td>
                            @if ($expense->receiptUrl())
                                <a href="{{ $expense->receiptUrl() }}" target="_blank" rel="noopener">View</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="pagination">{{ $expenses->links() }}</div>

@endsection
