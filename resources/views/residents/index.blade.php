@extends('layouts.app')
@section('title', 'Residents')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <input type="text" name="search" placeholder="{{ __('Search name, unit, block, or ID number...') }}" value="{{ request('search') }}">
        <button class="btn" type="submit">{{ __('Search') }}</button>
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('residents.create') }}" class="js-modal-link btn btn-primary">+ Add Resident</a>
        <a href="{{ route('residents.bulk-import.form') }}" class="js-modal-link btn">{{ __('⬆ Bulk Import') }}</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($residents->isEmpty())
            <div class="empty">{{ __('No residents found.') }}</div>
        @else
            <table>
                <thead>
                <tr>
                    <th>Name</th><th>{{ __('Unit') }}</th><th>Block</th><th>ID Number</th><th>{{ __('Phone') }}</th><th>Email</th><th>Occupancy</th><th>{{ __('Status') }}</th><th class="right">Actions</th>
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
                        <td><span class="badge badge-{{ $resident->status }}">{{ __(ucfirst($resident->status)) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('residents.edit', $resident) }}" class="js-modal-link btn btn-sm">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('residents.toggle', $resident) }}" style="display:inline"
                                  data-confirm="{{ $resident->status === 'active' ? __('Deactivate') : __('Activate') }} {{ addslashes($resident->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $resident->status === 'active' ? 'btn-danger' : '' }}">
                                    {{ $resident->status === 'active' ? __('Deactivate') : __('Activate') }}
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
