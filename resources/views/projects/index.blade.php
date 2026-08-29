@extends('layouts.app')
@section('title', 'Projects')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <select name="fund_id" onchange="this.form.submit()">
            <option value="">All funds</option>
            @foreach ($funds as $fund)
                <option value="{{ $fund->id }}" @selected(request('fund_id') == $fund->id)>{{ $fund->name }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="planned" @selected(request('status') == 'planned')>Planned</option>
            <option value="active" @selected(request('status') == 'active')>Active</option>
            <option value="completed" @selected(request('status') == 'completed')>Completed</option>
            <option value="archived" @selected(request('status') == 'archived')>Archived</option>
        </select>
    </form>
    <a href="{{ route('projects.create') }}" class="js-modal-link btn btn-primary">+ Add Project</a>
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
