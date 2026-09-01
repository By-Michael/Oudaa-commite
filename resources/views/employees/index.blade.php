@extends('layouts.app')
@section('title', 'Employees')
@section('content')

<x-list-header
    title="Employees"
    noun="employees"
    :shown="$employees->total()"
    :total="$totalCount"
    :export-excel="route('employees.export.excel', request()->query())"
    :export-pdf="route('employees.export.pdf', request()->query())"
    panel-id="filters-employees"
>
    <x-slot:icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M15 10h2"/><path d="M15 14h2"/><path d="M7 16h4"/></svg></x-slot:icon>

    <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;">
        <input type="text" name="search" placeholder="Search name, role, or ID number..." value="{{ request('search') }}">
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="terminated" @selected(request('status') === 'terminated')>Terminated</option>
        </select>
        <button class="btn btn-sm" type="submit">Apply</button>
    </form>
</x-list-header>

<div class="toolbar">
    <div class="toolbar-actions" style="margin-left:auto;">
        <a href="{{ route('employees.create') }}" class="js-modal-link btn btn-primary">+ Add Employee</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($employees->isEmpty())
            <div class="empty">No employees found.</div>
        @else
            <table>
                <thead>
                <tr>
                    <th>Name</th><th>ID Number</th><th>Role</th><th>Salary</th><th>Payment Date</th><th>Status</th><th class="right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($employees as $employee)
                    <tr>
                        <td><a href="{{ route('employees.show', $employee) }}">{{ $employee->name }}</a></td>
                        <td>{{ $employee->id_number }}</td>
                        <td>{{ $employee->role }}</td>
                        <td>{{ money($employee->salary) }}</td>
                        <td>{{ $employee->payment_date ? $employee->payment_date->format('jS \o\f each month') : '—' }}</td>
                        <td><span class="badge badge-{{ $employee->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($employee->status) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('employees.edit', $employee) }}" class="js-modal-link btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('employees.toggle', $employee) }}" style="display:inline"
                                  data-confirm="{{ $employee->status === 'active' ? 'Terminate' : 'Reactivate' }} {{ addslashes($employee->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $employee->status === 'active' ? 'btn-danger' : '' }}">
                                    {{ $employee->status === 'active' ? 'Terminate' : 'Reactivate' }}
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

<div class="pagination">{{ $employees->links() }}</div>

@endsection
