@extends('layouts.app')
@section('title', $project->name)
@section('content')

<div class="stats-grid">
    <div class="stat">
        <div class="label">{{ __('Planned Budget') }}</div>
        <div class="value">{{ money($project->planned_budget) }}</div>
    </div>
    <div class="stat neg">
        <div class="label">Spent</div>
        <div class="value">{{ money($project->spent()) }}</div>
    </div>
    <div class="stat {{ $project->remaining() >= 0 ? 'pos' : 'neg' }}">
        <div class="label">{{ __('Remaining') }}</div>
        <div class="value">{{ money($project->remaining()) }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Details</h2><a href="{{ route('projects.edit', $project) }}" class="js-modal-link btn btn-sm">{{ __('Edit') }}</a></div>
    <div class="panel-body">
        <p>{{ $project->description ?: 'No description.' }}</p>
        <p class="muted">
            Linked fund:
            @if ($project->fund)
                <a href="{{ route('funds.edit', $project->{{ __('fund)') }} }}">{{ $project->fund->name }}</a>
                (fund balance: {{ money($project->fund->balance()) }})
            @else
                none
            @endif
            &nbsp;·&nbsp; Status: <span class="badge badge-{{ $project->status === 'archived' ? 'archived' : 'active' }}">{{ ucfirst($project->status) }}</span>
            @if ($project->start_date || $project->end_date)
                &nbsp;·&nbsp; {!! $project->start_date ? eth_date($project->start_date) : __('No start date') !!} → {!! $project->end_date ? eth_date($project->end_date) : __('No end date') !!}
            @endif
        </p>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Expenses on this project</h2><a href="{{ route('expenses.create') }}" class="js-modal-link btn btn-sm">{{ __('+ Record Expense') }}</a></div>
    <div class="panel-body" style="padding:0;">
        @if ($project->expenses->isEmpty())
            <div class="empty">{{ __('No expenses recorded against this project yet.') }}</div>
        @else
            <table>
                <thead><tr><th>{{ __('Date') }}</th><th>Category</th><th>Vendor</th><th class="right">{{ __('Amount') }}</th><th>Note</th></tr></thead>
                <tbody>
                @foreach ($project->expenses as $expense)
                    <tr>
                        <td>{!! eth_date($expense->incurred_at) !!}</td>
                        <td>{{ $expense->category }}</td>
                        <td>{{ $expense->vendor ?: '—' }}</td>
                        <td class="right">{{ money($expense->amount) }}</td>
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
