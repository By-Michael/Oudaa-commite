@extends('layouts.app')
@section('title', 'Reports')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <label class="muted" style="align-self:center;">From
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" onchange="this.form.submit()">
        </label>
        <label class="muted" style="align-self:center;">To
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" onchange="this.form.submit()">
        </label>
    </form>
    <a href="{{ route('reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn btn-sm">Export CSV</a>
</div>

<div class="stats-grid">
    <div class="stat pos">
        <div class="label">Collected ({{ $from->format('M j') }} – {{ $to->format('M j') }})</div>
        <div class="value">{{ money($totalCollected) }}</div>
    </div>
    <div class="stat neg">
        <div class="label">Spent ({{ $from->format('M j') }} – {{ $to->format('M j') }})</div>
        <div class="value">{{ money($totalSpent) }}</div>
    </div>
    <div class="stat {{ $netChange >= 0 ? 'pos' : 'neg' }}">
        <div class="label">Net Change</div>
        <div class="value">{{ money($netChange) }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Fund Activity</h2></div>
    <div class="panel-body" style="padding:0;">
        @if ($funds->isEmpty())
            <div class="empty">No funds set up yet.</div>
        @else
            <table>
                <thead><tr><th>Fund</th><th class="right">Collected</th><th class="right">Spent</th><th class="right">Net</th><th class="right">Current Balance</th></tr></thead>
                <tbody>
                @foreach ($funds as $fund)
                    <tr>
                        <td>{{ $fund->name }}</td>
                        <td class="right">{{ money($fund->period_collected) }}</td>
                        <td class="right">{{ money($fund->period_spent) }}</td>
                        <td class="right">{{ money($fund->period_collected - $fund->period_spent) }}</td>
                        <td class="right">{{ money($fund->balance()) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="form-grid">
    <div class="panel">
        <div class="panel-head"><h2>Expenses by Category</h2></div>
        <div class="panel-body" style="padding:0;">
            @if ($expensesByCategory->isEmpty())
                <div class="empty">No expenses in this period.</div>
            @else
                <table>
                    <thead><tr><th>Category</th><th class="right">Count</th><th class="right">Total</th></tr></thead>
                    <tbody>
                    @foreach ($expensesByCategory as $row)
                        <tr>
                            <td>{{ $row->category }}</td>
                            <td class="right">{{ $row->count }}</td>
                            <td class="right">{{ money($row->total) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Payments by Method</h2></div>
        <div class="panel-body" style="padding:0;">
            @if ($paymentsByMethod->isEmpty())
                <div class="empty">No payments in this period.</div>
            @else
                <table>
                    <thead><tr><th>Method</th><th class="right">Count</th><th class="right">Total</th></tr></thead>
                    <tbody>
                    @foreach ($paymentsByMethod as $row)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $row->method)) }}</td>
                            <td class="right">{{ $row->count }}</td>
                            <td class="right">{{ money($row->total) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@endsection
