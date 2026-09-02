@extends('layouts.app')
@section('title', $employee->name)
@section('content')

<div class="stats-grid">
    <div class="stat">
        <div class="label">{{ __('Role') }}</div>
        <div class="value" style="font-size:1.25rem;">{{ $employee->role }}</div>
    </div>
    <div class="stat">
        <div class="label">Salary</div>
        <div class="value">{{ money($employee->salary) }}</div>
    </div>
    <div class="stat neg">
        <div class="label">{{ __('Total Paid') }}</div>
        <div class="value">{{ money($employee->totalPaid()) }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Details</h2>
        <a href="{{ route('employees.edit', $employee) }}" class="js-modal-link btn btn-sm">{{ __('Edit') }}</a>
    </div>
    <div class="panel-body">
        <div class="form-grid">
            <div><span class="muted">ID Number</span><br>{{ $employee->id_number }}</div>
            <div><span class="muted">{{ __('Phone') }}</span><br>{{ $employee->phone ?: '—' }}</div>
            <div><span class="muted">Payment Date</span><br>{{ $employee->payment_date ? $employee->payment_date->format('jS \o\f each month') : '—' }}</div>
            <div><span class="muted">{{ __('Status') }}</span><br><span class="badge badge-{{ $employee->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($employee->status) }}</span></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Salary Payments</h2></div>
    <div class="panel-body" style="padding:0;">
        @if ($employee->expenses->{{ __('isEmpty())') }}
            <div class="empty">No salary payments logged yet.</div>
        @else
            <table>
                <thead><tr><th>{{ __('Date') }}</th><th>Amount</th><th>Note</th></tr></thead>
                <tbody>
                @foreach ($employee->expenses as $expense)
                    <tr>
                        <td>{!! eth_date($expense->incurred_at) !!}</td>
                        <td>{{ money($expense->amount) }}</td>
                        <td>{{ $expense->note ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<a href="{{ route('expenses.create') }}" class="js-modal-link btn btn-primary">{{ __('+ Log Salary Payment') }}</a>

@endsection
