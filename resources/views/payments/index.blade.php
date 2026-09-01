@extends('layouts.app')
@section('title', 'Payments')
@section('content')

<x-list-header
    title="Payments"
    noun="payments"
    :shown="$payments->total()"
    :total="$totalCount"
    :export-excel="route('payments.export.excel', request()->query())"
    :export-pdf="route('payments.export.pdf', request()->query())"
    panel-id="filters-payments"
>
    <x-slot:icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></x-slot:icon>

    <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;">
        <select name="fee_id">
            <option value="">All fees</option>
            @foreach ($fees as $fee)
                <option value="{{ $fee->id }}" @selected(request('fee_id') == $fee->id)>{{ $fee->name }}</option>
            @endforeach
        </select>
        <select name="resident_id">
            <option value="">All residents</option>
            @foreach ($residents as $resident)
                <option value="{{ $resident->id }}" @selected(request('resident_id') == $resident->id)>{{ $resident->unit_number }} — {{ $resident->name }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">All statuses</option>
            <option value="PAID" @selected(request('status') === 'PAID')>Paid</option>
            <option value="PENDING" @selected(request('status') === 'PENDING')>Pending</option>
            <option value="VOID" @selected(request('status') === 'VOID')>Void</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}">
        <input type="date" name="date_to" value="{{ request('date_to') }}">
        <button class="btn btn-sm" type="submit">Apply</button>
    </form>
</x-list-header>

<div class="toolbar">
    <div class="toolbar-actions" style="margin-left:auto;">
        <a href="{{ route('payments.create') }}" class="js-modal-link btn btn-primary">+ Record Payment</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($payments->isEmpty())
            <div class="empty">No payments recorded yet.</div>
        @else
            <table>
                <thead>
                <tr><th>Date</th><th>Resident</th><th>Fee</th><th>Fund</th><th class="right">Amount</th><th>Method</th><th>Status</th><th>Note</th><th class="right">Actions</th></tr>
                </thead>
                <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at->format('Y-m-d') }}</td>
                        <td>{{ $payment->resident->name }} ({{ $payment->resident->unit_number }})</td>
                        <td>{{ $payment->fee->name ?? '—' }}</td>
                        <td>{{ $payment->fund->name ?? '—' }}</td>
                        <td class="right">{{ money($payment->amount) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                        <td><span class="badge badge-{{ strtolower($payment->status) }}">{{ $payment->status }}</span></td>
                        <td>{{ $payment->note ?: '—' }}</td>
                        <td class="right actions-cell"><a href="{{ route('payments.edit', $payment) }}" class="js-modal-link btn btn-sm">Edit Status</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="pagination">{{ $payments->links() }}</div>

@endsection
