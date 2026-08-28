@extends('layouts.app')
@section('title', $fund->exists ? 'Edit Fund' : 'Add Fund')
@section('content')

<div class="panel" style="max-width:560px;">
    <div class="panel-body">
        <form method="POST" action="{{ $fund->exists ? route('funds.update', $fund) : route('funds.store') }}">
            @csrf
            @if ($fund->exists) @method('PUT') @endif

            <div class="form-row">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $fund->name) }}" required>
            </div>
            <div class="form-row">
                <label>Category</label>
                <input type="text" name="category" value="{{ old('category', $fund->category) }}" placeholder="e.g. Maintenance, Reserve, Security">
            </div>
            <div class="form-row">
                <label>Description</label>
                <textarea name="description" rows="3">{{ old('description', $fund->description) }}</textarea>
            </div>
            <div class="form-row">
                <label>Status</label>
                <select name="status">
                    <option value="active" @selected(old('status', $fund->status ?? 'active') === 'active')>Active</option>
                    <option value="archived" @selected(old('status', $fund->status) === 'archived')>Archived</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('funds.index') }}" class="btn">Cancel</a>
        </form>
    </div>
</div>

@endsection
