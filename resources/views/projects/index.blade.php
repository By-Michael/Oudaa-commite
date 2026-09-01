@extends('layouts.app')
@section('title', 'Projects')
@section('content')

<x-list-header
    title="Projects"
    noun="projects"
    :shown="$projects->total()"
    :total="$totalCount"
    :export-excel="route('projects.export.excel', request()->query())"
    :export-pdf="route('projects.export.pdf', request()->query())"
    panel-id="filters-projects"
>
    <x-slot:icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></x-slot:icon>

    <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;">
        <select name="fund_id">
            <option value="">All funds</option>
            @foreach ($funds as $fund)
                <option value="{{ $fund->id }}" @selected(request('fund_id') == $fund->id)>{{ $fund->name }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">All statuses</option>
            <option value="planned" @selected(request('status') == 'planned')>Planned</option>
            <option value="active" @selected(request('status') == 'active')>Active</option>
            <option value="completed" @selected(request('status') == 'completed')>Completed</option>
            <option value="archived" @selected(request('status') == 'archived')>Archived</option>
        </select>
        <button class="btn btn-sm" type="submit">Apply</button>
    </form>
</x-list-header>

<div class="toolbar">
    <div class="toolbar-actions" style="margin-left:auto;">
        <a href="{{ route('projects.create') }}" class="js-modal-link btn btn-primary">+ Add Project</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($projects->isEmpty())
            <div class="empty">No projects yet.</div>
        @else
            <table>
                <thead>
                <tr><th>Name</th><th>Fund</th><th class="right">Planned Budget</th><th class="right">Spent</th><th class="right">Remaining</th><th>Status</th><th class="right">Actions</th></tr>
                </thead>
                <tbody>
                @foreach ($projects as $project)
                    <tr>
                        <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                        <td>{{ $project->fund->name ?? '—' }}</td>
                        <td class="right">{{ money($project->planned_budget) }}</td>
                        <td class="right">{{ money($project->spent()) }}</td>
                        <td class="right">{{ money($project->remaining()) }}</td>
                        <td><span class="badge badge-{{ $project->status === 'archived' ? 'archived' : 'active' }}">{{ ucfirst($project->status) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('projects.edit', $project) }}" class="js-modal-link btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('projects.toggle', $project) }}" style="display:inline"
                                  data-confirm="{{ $project->status === 'archived' ? 'Restore' : 'Archive' }} {{ addslashes($project->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $project->status !== 'archived' ? 'btn-danger' : '' }}">
                                    {{ $project->status === 'archived' ? 'Restore' : 'Archive' }}
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
