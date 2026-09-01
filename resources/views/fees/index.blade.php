@extends('layouts.app')
@section('title', 'Fees')
@section('content')

<x-list-header
    title="Fees"
    noun="fees"
    :shown="$fees->total()"
    :total="$totalCount"
    :export-excel="route('fees.export.excel', request()->query())"
    :export-pdf="route('fees.export.pdf', request()->query())"
    panel-id="filters-fees"
>
    <x-slot:icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></x-slot:icon>

    <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;">
        <select name="fund_id">
            <option value="">All funds</option>
            @foreach ($funds as $fund)
                <option value="{{ $fund->id }}" @selected(request('fund_id') == $fund->id)>{{ $fund->name }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') == 'active')>Active</option>
            <option value="inactive" @selected(request('status') == 'inactive')>Inactive</option>
        </select>
        <select name="frequency">
            <option value="">All frequencies</option>
            <option value="monthly" @selected(request('frequency') == 'monthly')>Monthly</option>
            <option value="yearly" @selected(request('frequency') == 'yearly')>Yearly</option>
            <option value="one_time" @selected(request('frequency') == 'one_time')>One-time</option>
        </select>
        <button class="btn btn-sm" type="submit">Apply</button>
    </form>
</x-list-header>

<div class="toolbar">
    <div class="toolbar-actions" style="margin-left:auto;">
        <a href="{{ route('fees.create') }}" class="js-modal-link btn btn-primary">+ Add Fee</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($fees->isEmpty())
            <div class="empty">No fees defined yet.</div>
        @else
            <table>
                <thead>
                <tr><th>Name</th><th>Fund</th><th>Amount</th><th>Frequency</th><th>Status</th><th class="right">Actions</th></tr>
                </thead>
                <tbody>
                @foreach ($fees as $fee)
                    <tr>
                        <td>{{ $fee->name }}</td>
                        <td>{{ $fee->fund->name }}</td>
                        <td>{{ money($fee->amount) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $fee->frequency)) }}</td>
                        <td><span class="badge badge-{{ $fee->status }}">{{ ucfirst($fee->status) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('fees.unpaid', $fee) }}" class="btn btn-sm">Unpaid</a>
                            <a href="{{ route('fees.edit', $fee) }}" class="js-modal-link btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('fees.toggle', $fee) }}" style="display:inline"
                                  data-confirm="{{ $fee->status === 'active' ? 'Deactivate' : 'Activate' }} {{ addslashes($fee->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $fee->status === 'active' ? 'btn-danger' : '' }}">
                                    {{ $fee->status === 'active' ? 'Deactivate' : 'Activate' }}
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

<div class="pagination">{{ $fees->links() }}</div>

@endsection
