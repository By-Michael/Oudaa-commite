@extends('layouts.app')
@section('title', 'Employees')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <input type="text" name="search" placeholder="Search name, role, or ID number..." value="{{ request('search') }}">
        <button class="btn" type="submit">Search</button>
    </form>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">+ Add Employee</a>
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
                        <td>{{ number_format($employee->salary, 2) }}</td>
                        <td>{{ $employee->payment_date ? $employee->payment_date->format('jS \o\f each month') : '—' }}</td>
                        <td><span class="badge badge-{{ $employee->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($employee->status) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('employees.toggle', $employee) }}" style="display:inline">
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
