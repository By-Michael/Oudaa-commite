@extends('layouts.app')
@section('title', 'Update Payment Status')
@section('content')

<div class="panel" style="max-width:520px;">
    <div class="panel-body">
        <p class="muted">
            {{ $payment->resident->name }} ({{ $payment->resident->unit_number }}) —
            {{ $payment->fee->name ?? 'No linked fee' }} —
            {{ money($payment->amount) }}, paid {{ $payment->paid_at->format('Y-m-d') }}
        </p>
        <p class="muted" style="margin-top:-8px;font-size:12px;">
            Only status, fund, and note can be corrected here. To fix the resident, fee, or amount, void this entry and record a new payment instead.
        </p>

        <form method="POST" action="{{ route('payments.update', $payment) }}">
            @csrf
            @method('PUT')

            <div class="form-row">
                <label>Status</label>
                <select name="status">
                    <option value="PAID" @selected(old('status', $payment->status) === 'PAID')>Paid</option>
                    <option value="PENDING" @selected(old('status', $payment->status) === 'PENDING')>Pending</option>
                    <option value="VOID" @selected(old('status', $payment->status) === 'VOID')>Void</option>
                </select>
            </div>

            <div class="form-row">
                <label>Fund (required if Paid)</label>
                <select name="fund_id">
                    <option value="">No linked fund</option>
                    @foreach ($funds as $fund)
                        <option value="{{ $fund->id }}" @selected(old('fund_id', $payment->fund_id) == $fund->id)>{{ $fund->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <label>Note</label>
                <input type="text" name="note" value="{{ old('note', $payment->note) }}">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('payments.index') }}" class="btn">Cancel</a>
        </form>
    </div>
</div>

@endsection
