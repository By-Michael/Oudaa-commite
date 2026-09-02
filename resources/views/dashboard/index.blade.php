@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="stats-grid">
    <div class="stat {{ $totalFundsBalance >= 0 ? 'pos' : 'neg' }}">
        <div class="label">{{ __('Total Funds Balance') }}</div>
        <div class="value">{{ money($totalFundsBalance) }}</div>
    </div>
    <div class="stat pos">
        <div class="label">Total Collected</div>
        <div class="value">{{ money($totalCollected) }}</div>
    </div>
    <div class="stat neg">
        <div class="label">{{ __('Total Spent') }}</div>
        <div class="value">{{ money($totalSpent) }}</div>
    </div>
    <div class="stat">
        <div class="label">Active Funds</div>
        <div class="value">{{ $funds->count() }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>{{ __('Fund Balances') }}</h2><a href="{{ route('funds.index') }}" class="btn btn-sm">Manage Funds</a></div>
    <div class="panel-body" style="padding:0;">
        @if ($funds->{{ __('isEmpty())') }}
            <div class="empty">No active funds yet.</div>
        @else
            <table>
                <thead><tr><th>{{ __('Fund') }}</th><th>Category</th><th class="right">Balance</th></tr></thead>
                <tbody>
                @foreach ($funds as $fund)
                    <tr>
                        <td>{{ $fund->name }}</td>
                        <td>{{ $fund->category ?: '—' }}</td>
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
        <div class="panel-head"><h2>{{ __('Recent Payments') }}</h2><a href="{{ route('payments.index') }}" class="btn btn-sm">View all</a></div>
        <div class="panel-body" style="padding:0;">
            @if ($recentPayments->{{ __('isEmpty())') }}
                <div class="empty">No payments recorded yet.</div>
            @else
                <table>
                    <thead><tr><th>{{ __('Resident') }}</th><th>Fee</th><th class="right">Amount</th><th>{{ __('Date') }}</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->resident->name }}</td>
                            <td>{{ $payment->fee->name ?? '—' }}</td>
                            <td class="right">{{ money($payment->amount) }}</td>
                            <td>{!! eth_date($payment->paid_at) !!}</td>
                            <td><span class="badge badge-{{ strtolower($payment->status) }}">{{ $payment->status }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Recent Expenses</h2><a href="{{ route('expenses.index') }}" class="btn btn-sm">{{ __('View all') }}</a></div>
        <div class="panel-body" style="padding:0;">
            @if ($recentExpenses->isEmpty())
                <div class="empty">{{ __('No expenses recorded yet.') }}</div>
            @else
                <table>
                    <thead><tr><th>Category</th><th>{{ __('Fund') }}</th><th class="right">Amount</th><th>{{ __('Date') }}</th></tr></thead>
                    <tbody>
                    @foreach ($recentExpenses as $expense)
                        <tr>
                            <td>{{ $expense->category }}</td>
                            <td>{{ $expense->fund->name ?? '—' }}</td>
                            <td class="right">{{ money($expense->amount) }}</td>
                            <td>{!! eth_date($expense->incurred_at) !!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@endsection
