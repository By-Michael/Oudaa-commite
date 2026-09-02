@extends('layouts.app')
@section('title', 'Employees')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <input type="text" name="search" placeholder="{{ __('Search name, role, or ID number...') }}" value="{{ request('search') }}">
        <button class="btn" type="submit">{{ __('Search') }}</button>
    </form>
    <div class="toolbar-actions">
        <a href="{{ route('employees.create') }}" class="js-modal-link btn btn-primary">+ Add Employee</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($employees->{{ __('isEmpty())') }}
            <div class="empty">No employees found.</div>
        @else
            <table>
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th><th>ID Number</th><th>Role</th><th>{{ __('Salary') }}</th><th>Payment Date</th><th>Status</th><th class="right">{{ __('Actions') }}</th>
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
                        <td><span class="badge badge-{{ $employee->status === 'active' ? 'active' : 'inactive' }}">{{ __(ucfirst($employee->status)) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('employees.edit', $employee) }}" class="js-modal-link btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('employees.toggle', $employee) }}" style="display:inline"
                                  data-confirm="{{ $employee->status === 'active' ? __('Terminate') : __('Reactivate') }} {{ addslashes($employee->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $employee->status === 'active' ? 'btn-danger' : '' }}">
                                    {{ $employee->status === 'active' ? __('Terminate') : __('Reactivate') }}
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
