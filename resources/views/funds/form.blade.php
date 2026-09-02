@extends('layouts.app')
@section('title', $fund->exists ? 'Edit Fund' : 'Add Fund')
@section('content')

<div class="panel" style="max-width:560px;">
    <div class="panel-body">
        <form method="POST" action="{{ $fund->exists ? route('funds.update', $fund) : route('funds.store') }}">
            @csrf
            @if ($fund->exists) @method('PUT') @endif

            <div class="form-row">
                <label>{{ __('Name') }}<span class="req">*</span></label>
                <input type="text" name="name" value="{{ old('name', $fund->name) }}" required data-filter="safe-text">
            </div>
            <div class="form-row">
                <label>{{ __('Category') }}</label>
                <input type="text" name="category" value="{{ old('category', $fund->category) }}" placeholder="{{ __('e.g. Maintenance, Reserve, Security') }}" data-filter="safe-text">
            </div>
            <div class="form-row">
                <label>{{ __('Description') }}</label>
                <textarea name="description" rows="3" data-filter="safe-text">{{ old('description', $fund->description) }}</textarea>
            </div>
            <div class="form-row">
                <label>{{ __('Status') }}</label>
                <select name="status">
                    <option value="active" @selected(old('status', $fund->status ?? 'active') === 'active')>{{ __('Active') }}</option>
                    <option value="archived" @selected(old('status', $fund->status) === 'archived')>{{ __('Archived') }}</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ route('funds.index') }}" class="btn">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>

@endsection
