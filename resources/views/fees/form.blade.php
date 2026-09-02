@extends('layouts.app')
@section('title', $fee->exists ? 'Edit Fee' : 'Add Fee')
@section('content')

<div class="panel" style="max-width:560px;">
    <div class="panel-body">
        <form method="POST" action="{{ $fee->exists ? route('fees.update', $fee) : route('fees.store') }}">
            @csrf
            @if ($fee->{{ __('exists)') }} @method('PUT') @endif

            <div class="form-row">
                <label>Fee Name<span class="req">*</span></label>
                <input type="text" name="name" value="{{ old('name', $fee->name) }}" required data-filter="safe-text">
            </div>
            <div class="form-row">
                <label>{{ __('Linked Fund') }}<span class="req">*</span></label>
                <select name="fund_id" required>
                    <option value="">Select a fund…</option>
                    @foreach ($funds as $fund)
                        <option value="{{ $fund->id }}" @selected(old('fund_id', $fee->fund_id) == $fund->id)>{{ $fund->name }}</option>
                    @endforeach
                </select>
                <p class="muted" style="font-size:12px;margin-top:6px;">{{ __('Every payment collected under this fee will be attributed to this fund\'s balance.') }}</p>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label>{{ __('Amount (ETB)') }}<span class="req">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $fee->amount) }}" required data-filter="decimal">
                </div>
                <div class="form-row">
                    <label>Frequency</label>
                    <select name="frequency" id="frequency">
                        @foreach (['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly', 'one_time' => 'One-time'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('frequency', $fee->frequency ?? 'monthly') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row" id="recurrence-day-row">
                <label>{{ __('Recurs on Day of Month (optional)') }}</label>
                <input type="number" min="1" max="31" name="recurrence_day" id="recurrence_day"
                       value="{{ old('recurrence_day', $fee->recurrence_day) }}"
                       placeholder="e.g. 5" data-filter="digits">
                <p class="muted" style="font-size:12px;margin-top:6px;">
                    {{ __('The day of the month this fee recurs on (for monthly: due every month on this day; for quarterly/yearly: due on this day of the recurring month). Leave blank to use today\'s date.') }}
                </p>
            </div>
            @if ($fee->{{ __('exists)') }}
            <div class="form-row">
                <label>Status</label>
                <select name="status">
                    <option value="active" @selected(old('status', $fee->status ?? 'active') === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(old('status', $fee->status) === 'inactive')>Inactive</option>
                </select>
            </div>
            @endif

            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ route('fees.index') }}" class="btn">Cancel</a>
        </form>
    </div>
</div>

<script>
// Recurrence day doesn't apply to one-time fees.
var frequencySelect = document.getElementById('frequency');
var recurrenceRow = document.getElementById('recurrence-day-row');

function syncRecurrenceRow() {
    recurrenceRow.style.display = frequencySelect.value === 'one_time' ? 'none' : '';
}
frequencySelect.addEventListener('change', syncRecurrenceRow);
syncRecurrenceRow();
</script>

@endsection
