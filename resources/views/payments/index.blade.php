@extends('layouts.app')
@section('title', 'Payments')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <select name="fee_id" onchange="this.form.submit()">
            <option value="">{{ __('All fees') }}</option>
            @foreach ($fees as $fee)
                <option value="{{ $fee->id }}" @selected(request('fee_id') == $fee->id)>{{ $fee->name }}</option>
            @endforeach
        </select>
        <select name="resident_id" onchange="this.form.submit()">
            <option value="">All residents</option>
            @foreach ($residents as $resident)
                <option value="{{ $resident->id }}" @selected(request('resident_id') == $resident->id)>{{ $resident->unit_number }} — {{ $resident->name }}</option>
            @endforeach
        </select>
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('payments.create') }}" class="js-modal-link btn btn-primary">{{ __('+ Record Payment') }}</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($payments->isEmpty())
            <div class="empty">{{ __('No payments recorded yet.') }}</div>
        @else
            <table>
                <thead>
                <tr><th>Date</th><th>{{ __('Resident') }}</th><th>Fee</th><th>Fund</th><th class="right">{{ __('Amount') }}</th><th>Method</th><th>Status</th><th>{{ __('Note') }}</th><th class="right">Actions</th></tr>
                </thead>
                <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td>{!! eth_date($payment->paid_at) !!}</td>
                        <td>{{ $payment->resident->name }} ({{ $payment->resident->unit_number }})</td>
                        <td>{{ $payment->fee->name ?? '—' }}</td>
                        <td>{{ $payment->fund->name ?? '—' }}</td>
                        <td class="right">{{ money($payment->amount) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                        <td><span class="badge badge-{{ strtolower($payment->status) }}">{{ $payment->status }}</span></td>
                        <td>{{ $payment->note ?: '—' }}</td>
                        <td class="right actions-cell"><a href="{{ route('payments.edit', $payment) }}" class="js-modal-link btn btn-sm">{{ __('Edit Status') }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="pagination">{{ $payments->links() }}</div>

@endsection
