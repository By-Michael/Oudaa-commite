@extends('layouts.app')
@section('title', 'Residents')
@section('content')

<x-list-header
    title="Residents"
    noun="members"
    :shown="$residents->total()"
    :total="$totalCount"
    :export-excel="route('residents.export.excel', request()->query())"
    :export-pdf="route('residents.export.pdf', request()->query())"
    panel-id="filters-residents"
>
    <x-slot:icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></x-slot:icon>

    <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;">
        <input type="text" name="search" placeholder="Search name, unit, block, or ID number..." value="{{ request('search') }}">
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <select name="occupancy">
            <option value="">All occupancy</option>
            <option value="owner" @selected(request('occupancy') === 'owner')>Owner</option>
            <option value="renter" @selected(request('occupancy') === 'renter')>Renter</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}">
        <input type="date" name="date_to" value="{{ request('date_to') }}">
        <button class="btn btn-sm" type="submit">Apply</button>
    </form>
</x-list-header>

<div class="toolbar">
    <div class="toolbar-actions" style="margin-left:auto;">
        <a href="{{ route('residents.create') }}" class="js-modal-link btn btn-primary">+ Add Resident</a>
        <a href="{{ route('residents.bulk-import.form') }}" class="js-modal-link btn">⬆ Bulk Import</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($residents->isEmpty())
            <div class="empty">No residents found.</div>
        @else
            <table>
                <thead>
                <tr>
                    <th>Name</th><th>Unit</th><th>Block</th><th>ID Number</th><th>Phone</th><th>Email</th><th>Occupancy</th><th>Status</th><th class="right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($residents as $resident)
                    <tr>
                        <td>{{ $resident->name }}</td>
                        <td>{{ $resident->unit_number }}</td>
                        <td>{{ $resident->block_number ?: '—' }}</td>
                        <td>{{ $resident->id_number ?: '—' }}</td>
                        <td>{{ $resident->phone ?: '—' }}</td>
                        <td>{{ $resident->email ?: '—' }}</td>
                        <td>{{ $resident->occupancy === 'renter' ? 'Tenant' : ucfirst($resident->occupancy) }}</td>
                        <td><span class="badge badge-{{ $resident->status }}">{{ ucfirst($resident->status) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('residents.edit', $resident) }}" class="js-modal-link btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('residents.toggle', $resident) }}" style="display:inline"
                                  data-confirm="{{ $resident->status === 'active' ? 'Deactivate' : 'Activate' }} {{ addslashes($resident->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $resident->status === 'active' ? 'btn-danger' : '' }}">
                                    {{ $resident->status === 'active' ? 'Deactivate' : 'Activate' }}
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

<div class="pagination">{{ $residents->links() }}</div>

@endsection
