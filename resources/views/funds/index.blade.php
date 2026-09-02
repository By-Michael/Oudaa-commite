@extends('layouts.app')
@section('title', 'Funds')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <select name="status" onchange="this.form.submit()">
            <option value="">{{ __('All statuses') }}</option>
            <option value="active" @selected(request('status') == 'active')>{{ __('Active') }}</option>
            <option value="archived" @selected(request('status') == 'archived')>{{ __('Archived') }}</option>
        </select>
        @if ($categories->isNotEmpty())
            <select name="category" onchange="this.form.submit()">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') == $category)>{{ $category }}</option>
                @endforeach
            </select>
        @endif
    </form>
    <div class="toolbar-actions">
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
                <tr><th>{{ __('Name') }}</th><th>{{ __('Category') }}</th><th>{{ __('Description') }}</th><th class="right">{{ __('Balance') }}</th><th>{{ __('Status') }}</th><th class="right">{{ __('Actions') }}</th></tr>
                </thead>
                <tbody>
                @foreach ($funds as $fund)
                    <tr>
                        <td>{{ $fund->name }}</td>
                        <td>{{ $fund->category ?: '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($fund->description, 50) ?: '—' }}</td>
                        <td class="right">{{ money($fund->balance()) }}</td>
                        <td><span class="badge badge-{{ $fund->status === 'active' ? 'active' : 'archived' }}">{{ __(ucfirst($fund->status)) }}</span></td>
                        <td class="right actions-cell">
                            <a href="{{ route('funds.edit', $fund) }}" class="js-modal-link btn btn-sm">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('funds.toggle', $fund) }}" style="display:inline"
                                  data-confirm="{{ $fund->status === 'active' ? __('Archive') : __('Restore') }} {{ addslashes($fund->name) }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $fund->status === 'active' ? 'btn-danger' : '' }}">
                                    {{ $fund->status === 'active' ? __('Archive') : __('Restore') }}
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
