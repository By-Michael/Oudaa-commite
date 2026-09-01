@extends('layouts.app')
@section('title', 'Expenses')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <select name="fund_id" onchange="this.form.submit()">
            <option value="">All funds</option>
            @foreach ($funds as $fund)
                <option value="{{ $fund->id }}" @selected(request('fund_id') == $fund->id)>{{ $fund->name }}</option>
            @endforeach
        </select>
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('expenses.export.excel', request()->query()) }}" class="btn">⬇ Excel</a>
        <a href="{{ route('expenses.export.pdf', request()->query()) }}" class="btn">⬇ PDF</a>
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
