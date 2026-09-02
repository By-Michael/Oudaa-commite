@extends('layouts.app')
@section('title', $employee->exists ? 'Edit Employee' : 'Add Employee')
@section('content')

<div class="panel" style="max-width:560px;">
    <div class="panel-body">
        <form method="POST" action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}">
            @csrf
            @if ($employee->{{ __('exists)') }} @method('PUT') @endif

            <div class="form-grid">
                <div class="form-row">
                    <label>Name<span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}" required data-filter="letters">
                </div>
                <div class="form-row">
                    <label>{{ __('ID Number') }}<span class="req">*</span></label>
                    <input type="text" name="id_number" value="{{ old('id_number', $employee->id_number) }}" required data-filter="alnum">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-row">
                    <label>Role<span class="req">*</span></label>
                    <input type="text" name="role" value="{{ old('role', $employee->role) }}" placeholder="{{ __('e.g. Security Guard, Cleaner, Groundskeeper') }}" required data-filter="letters">
                </div>
                <div class="form-row">
                    <label>{{ __('Salary (ETB)') }}<span class="req">*</span></label>
                    <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $employee->salary) }}" required data-filter="decimal">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-row">
                    <label>Payment Date (optional)</label>
                    {!! eth_date_input('payment_date', old('payment_date', optional($employee->payment_date)->toDateString())) !!}
                    <p class="muted" style="font-size:12px;margin-top:6px;">{{ __('The recurring date salary is expected to be paid — e.g. set any month\'s 28th to mean "the 28th of every month."') }}</p>
                </div>
                <div class="form-row">
                    <label>{{ __('Phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" data-filter="phone">
                </div>
            </div>

            @if ($employee->exists)
            <div class="form-row">
                <label>{{ __('Status') }}</label>
                <select name="status">
                    <option value="active" @selected(old('status', $employee->status) === 'active')>Active</option>
                    <option value="terminated" @selected(old('status', $employee->status) === 'terminated')>{{ __('Terminated') }}</option>
                </select>
            </div>
            @endif

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('employees.index') }}" class="btn">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>

@endsection
