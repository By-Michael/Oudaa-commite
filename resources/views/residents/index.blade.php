@extends('layouts.app')
@section('title', 'Residents')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <input type="text" name="search" placeholder="Search name, unit, block, or ID number..." value="{{ request('search') }}">
        <button class="btn" type="submit">Search</button>
    </form>
    <a href="{{ route('residents.create') }}" class="btn btn-primary">+ Add Resident</a>
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
                        <td>{{ ucfirst($resident->occupancy) }}</td>
                        <td><span class="badge badge-{{ $resident->status }}">{{ ucfirst($resident->status) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('residents.edit', $resident) }}" class="btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('residents.toggle', $resident) }}" style="display:inline"
                                  onsubmit="return confirm('{{ $resident->status === 'active' ? 'Deactivate' : 'Activate' }} {{ addslashes($resident->name) }}?');">
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
