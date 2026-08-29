@extends('layouts.app')
@section('title', $project->exists ? 'Edit Project' : 'Add Project')
@section('content')

<div class="panel" style="max-width:560px;">
    <div class="panel-body">
        <form method="POST" action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}">
            @csrf
            @if ($project->exists) @method('PUT') @endif

            <div class="form-row">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required>
            </div>
            <div class="form-row">
                <label>Description</label>
                <textarea name="description" rows="3">{{ old('description', $project->description) }}</textarea>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label>Linked Fund</label>
                    <select name="fund_id" required>
                        <option value="">Select a fund…</option>
                        @foreach ($funds as $fund)
                            <option value="{{ $fund->id }}" @selected(old('fund_id', $project->fund_id) == $fund->id)>{{ $fund->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <label>Planned Budget (ETB)</label>
                    <input type="number" step="0.01" min="0" name="planned_budget" value="{{ old('planned_budget', $project->planned_budget) }}" required>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label>Start Date (optional)</label>
                    <input type="date" name="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}">
                </div>
                <div class="form-row">
                    <label>End Date (optional)</label>
                    <input type="date" name="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="form-row">
                <label>Status</label>
                <select name="status">
                    @foreach (['planned' => 'Planned', 'active' => 'Active', 'completed' => 'Completed', 'archived' => 'Archived'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $project->status ?? 'planned') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('projects.index') }}" class="btn">Cancel</a>
        </form>
    </div>
</div>

@endsection
