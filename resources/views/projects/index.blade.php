@extends('layouts.app')
@section('title', 'Projects')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <select name="fund_id" onchange="this.form.submit()">
            <option value="">{{ __('All funds') }}</option>
            @foreach ($funds as $fund)
                <option value="{{ $fund->id }}" @selected(request('fund_id') == $fund->id)>{{ $fund->name }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()">
            <option value="">{{ __('All statuses') }}</option>
            <option value="planned" @selected(request('status') == 'planned')>{{ __('Planned') }}</option>
            <option value="active" @selected(request('status') == 'active')>{{ __('Active') }}</option>
            <option value="completed" @selected(request('status') == 'completed')>{{ __('Completed') }}</option>
            <option value="archived" @selected(request('status') == 'archived')>{{ __('Archived') }}</option>
        </select>
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('projects.create') }}" class="js-modal-link btn btn-primary">{{ __('+ Add Project') }}</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($projects->isEmpty())
            <div class="empty">{{ __('No projects yet.') }}</div>
        @else
            <table>
                <thead>
                <tr><th>{{ __('Name') }}</th><th>{{ __('Fund') }}</th><th class="right">{{ __('Planned Budget') }}</th><th class="right">{{ __('Spent') }}</th><th class="right">{{ __('Remaining') }}</th><th>{{ __('Status') }}</th><th class="right">{{ __('Actions') }}</th></tr>
                </thead>
                <tbody>
                @foreach ($projects as $project)
                    <tr>
                        <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                        <td>{{ $project->fund->name ?? '—' }}</td>
                        <td class="right">{{ money($project->planned_budget) }}</td>
                        <td class="right">{{ money($project->spent()) }}</td>
                        <td class="right">{{ money($project->remaining()) }}</td>
                        <td><span class="badge badge-{{ $project->status === 'archived' ? 'archived' : 'active' }}">{{ __(ucfirst($project->status)) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('projects.edit', $project) }}" class="js-modal-link btn btn-sm">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('projects.toggle', $project) }}" style="display:inline"
                                  data-confirm="{{ $project->status === 'archived' ? __('Restore') : __('Archive') }} {{ addslashes($project->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $project->status !== 'archived' ? 'btn-danger' : '' }}">
                                    {{ $project->status === 'archived' ? __('Restore') : __('Archive') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="pagination">{{ $projects->links() }}</div>

@endsection
