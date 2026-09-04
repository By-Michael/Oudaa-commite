@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET">
        <select name="subject_type" onchange="this.form.submit()">
            <option value="">{{ __('All types') }}</option>
            @foreach ($subjectTypes as $type)
                <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <select name="action" onchange="this.form.submit()">
            <option value="">{{ __('All actions') }}</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
            @endforeach
        </select>
    </form>
    <span class="muted">{{ __('Append-only — nothing here can be edited or removed.') }}</span>
</div>

<div class="panel" id="auditLogList">
    <div class="panel-body" style="padding:0;">
        @if ($logs->isEmpty())
            <div class="empty">No activity recorded yet.</div>
        @else
            <table>
                <thead><tr><th>{{ __('When') }}</th><th>{{ __('Committee Member') }}</th><th>{{ __('Action') }}</th><th>{{ __('Type') }}</th><th>{{ __('Details') }}</th></tr></thead>
                <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td>{!! eth_date($log->created_at, 'd M Y H:i') !!}</td>
                        <td>
                            {{ $log->committee_name ?? 'System' }}
                            @if ($log->via_god_admin)
                                <span class="badge badge-pending" title="{{ __('Made via impersonation, approved by the committee member') }}">
                                    {{ __('via Admin') }}{{ $log->god_admin_name ? ' ('.$log->god_admin_name.')' : '' }}
                                </span>
                            @endif
                        </td>
                        <td><span class="badge badge-{{ in_array($log->action, ['created','active','activated']) ? 'active' : (in_array($log->action, ['inactive','deactivated','archived']) ? 'inactive' : 'pending') }}">{{ ucfirst($log->action) }}</span></td>
                        <td>{{ $log->subject_type }}</td>
                        <td>{{ $log->description }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="pagination">{{ $logs->links() }}</div>

@push('scripts')
<script>
  // New entries (including changes made via admin impersonation) show
  // up as they're written, without needing to reload the page.
  initLivePoll('auditLogList', { interval: 5000 });
</script>
@endpush

@endsection
