@extends('layouts.app')
@section('title', 'Funds')
@section('content')

<x-list-header
    title="Funds"
    noun="funds"
    :shown="$funds->total()"
    :total="$totalCount"
    :export-excel="route('funds.export.excel', request()->query())"
    :export-pdf="route('funds.export.pdf', request()->query())"
    panel-id="filters-funds"
>
    <x-slot:icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></x-slot:icon>

    <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;">
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') == 'active')>Active</option>
            <option value="archived" @selected(request('status') == 'archived')>Archived</option>
        </select>
        @if ($categories->isNotEmpty())
            <select name="category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') == $category)>{{ $category }}</option>
                @endforeach
            </select>
        @endif
        <button class="btn btn-sm" type="submit">Apply</button>
    </form>
</x-list-header>

<div class="toolbar">
    <div class="toolbar-actions" style="margin-left:auto;">
        <a href="{{ route('funds.create') }}" class="js-modal-link btn btn-primary">+ Add Fund</a>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if ($funds->isEmpty())
            <div class="empty">No funds created yet.</div>
        @else
            <table>
                <thead>
                <tr><th>Name</th><th>Category</th><th>Description</th><th class="right">Balance</th><th>Status</th><th class="right">Actions</th></tr>
                </thead>
                <tbody>
                @foreach ($funds as $fund)
                    <tr>
                        <td>{{ $fund->name }}</td>
                        <td>{{ $fund->category ?: '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($fund->description, 50) ?: '—' }}</td>
                        <td class="right">{{ money($fund->balance()) }}</td>
                        <td><span class="badge badge-{{ $fund->status === 'active' ? 'active' : 'archived' }}">{{ ucfirst($fund->status) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('funds.edit', $fund) }}" class="js-modal-link btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('funds.toggle', $fund) }}" style="display:inline"
                                  data-confirm="{{ $fund->status === 'active' ? 'Archive' : 'Restore' }} {{ addslashes($fund->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $fund->status === 'active' ? 'btn-danger' : '' }}">
                                    {{ $fund->status === 'active' ? 'Archive' : 'Restore' }}
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

<div class="pagination">{{ $funds->links() }}</div>

@endsection
