@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="stats-grid">
    <div class="stat {{ $totalFundsBalance >= 0 ? 'pos' : 'neg' }}">
        <div class="label">Total Funds Balance</div>
        <div class="value">{{ number_format($totalFundsBalance, 2) }}</div>
    </div>
    <div class="stat pos">
        <div class="label">Total Collected</div>
        <div class="value">{{ number_format($totalCollected, 2) }}</div>
    </div>
    <div class="stat neg">
        <div class="label">Total Spent</div>
        <div class="value">{{ number_format($totalSpent, 2) }}</div>
    </div>
    <div class="stat">
        <div class="label">Active Funds</div>
        <div class="value">{{ $funds->count() }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Fund Balances</h2><a href="{{ route('funds.index') }}" class="btn btn-sm">Manage Funds</a></div>
    <div class="panel-body" style="padding:0;">
        @if ($funds->isEmpty())
            <div class="empty">No active funds yet.</div>
        @else
            <table>
                <thead><tr><th>Fund</th><th>Category</th><th class="right">Balance</th></tr></thead>
                <tbody>
                @foreach ($funds as $fund)
                    <tr>
                        <td>{{ $fund->name }}</td>
                        <td>{{ $fund->category ?: '—' }}</td>
                        <td class="right">{{ number_format($fund->balance(), 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="form-grid">
    <div class="panel">
        <div class="panel-head"><h2>Recent Payments</h2><a href="{{ route('payments.index') }}" class="btn btn-sm">View all</a></div>
        <div class="panel-body" style="padding:0;">
            @if ($recentPayments->isEmpty())
                <div class="empty">No payments recorded yet.</div>
            @else
                <table>
                    <thead><tr><th>Resident</th><th>Fee</th><th class="right">Amount</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->resident->name }}</td>
                            <td>{{ $payment->fee->name ?? '—' }}</td>
                            <td class="right">{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->paid_at->format('Y-m-d') }}</td>
                            <td><span class="badge badge-{{ strtolower($payment->status) }}">{{ $payment->status }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Recent Expenses</h2><a href="{{ route('expenses.index') }}" class="btn btn-sm">View all</a></div>
        <div class="panel-body" style="padding:0;">
            @if ($recentExpenses->isEmpty())
                <div class="empty">No expenses recorded yet.</div>
            @else
                <table>
                    <thead><tr><th>Category</th><th>Fund</th><th class="right">Amount</th><th>Date</th></tr></thead>
                    <tbody>
                    @foreach ($recentExpenses as $expense)
                        <tr>
                            <td>{{ $expense->category }}</td>
                            <td>{{ $expense->fund->name ?? '—' }}</td>
                            <td class="right">{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ $expense->incurred_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@endsection
