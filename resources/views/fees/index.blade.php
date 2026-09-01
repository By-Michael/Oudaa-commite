@extends('layouts.app')
@section('title', 'Fees')
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
            <option value="active" @selected(request('status') == 'active')>Active</option>
            <option value="inactive" @selected(request('status') == 'inactive')>Inactive</option>
        </select>
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('fees.export.excel', request()->query()) }}" class="btn">⬇ Excel</a>
        <a href="{{ route('fees.export.pdf', request()->query()) }}" class="btn">⬇ PDF</a>
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
