@extends('layouts.app')
@section('title', $project->name)
@section('content')

<div class="stats-grid">
    <div class="stat">
        <div class="label">Planned Budget</div>
        <div class="value">{{ number_format($project->planned_budget, 2) }}</div>
    </div>
    <div class="stat neg">
        <div class="label">Spent</div>
        <div class="value">{{ number_format($project->spent(), 2) }}</div>
    </div>
    <div class="stat {{ $project->remaining() >= 0 ? 'pos' : 'neg' }}">
        <div class="label">Remaining</div>
        <div class="value">{{ number_format($project->remaining(), 2) }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Details</h2><a href="{{ route('projects.edit', $project) }}" class="btn btn-sm">Edit</a></div>
    <div class="panel-body">
        <p>{{ $project->description ?: 'No description.' }}</p>
        <p class="muted">
            Linked fund:
            @if ($project->fund)
                <a href="{{ route('funds.edit', $project->fund) }}">{{ $project->fund->name }}</a>
                (fund balance: {{ number_format($project->fund->balance(), 2) }})
            @else
                none
            @endif
            &nbsp;·&nbsp; Status: <span class="badge badge-{{ $project->status === 'archived' ? 'archived' : 'active' }}">{{ ucfirst($project->status) }}</span>
            @if ($project->start_date || $project->end_date)
                &nbsp;·&nbsp; {{ $project->start_date?->format('Y-m-d') ?? 'No start date' }} → {{ $project->end_date?->format('Y-m-d') ?? 'No end date' }}
            @endif
        </p>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Expenses on this project</h2><a href="{{ route('expenses.create') }}" class="btn btn-sm">+ Record Expense</a></div>
    <div class="panel-body" style="padding:0;">
        @if ($project->expenses->isEmpty())
            <div class="empty">No expenses recorded against this project yet.</div>
        @else
            <table>
                <thead><tr><th>Date</th><th>Category</th><th>Vendor</th><th class="right">Amount</th><th>Note</th></tr></thead>
                <tbody>
                @foreach ($project->expenses as $expense)
                    <tr>
                        <td>{{ $expense->incurred_at->format('Y-m-d') }}</td>
                        <td>{{ $expense->category }}</td>
                        <td>{{ $expense->vendor ?: '—' }}</td>
                        <td class="right">{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->note ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<a href="{{ route('projects.index') }}" class="btn">Back to Projects</a>

@endsection
