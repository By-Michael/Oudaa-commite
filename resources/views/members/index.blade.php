@extends('layouts.app')
@section('title', 'Committee Members')
@section('content')

<div class="toolbar">
    <p class="muted" style="margin:0;">{{ __('Everyone here can sign in and use the panel. Every action is attributed to the person who did it in the') }} <a href="{{ route('audit.index') }}">{{ __('Audit Log') }}</a>.</p>
    <a href="{{ route('members.create') }}" class="js-modal-link btn btn-primary">+ Add Committee Member</a>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        <table>
            <thead><tr><th>{{ __('Name') }}</th><th>Email</th><th>Phone</th><th>{{ __('Joined') }}</th></tr></thead>
            <tbody>
            @foreach ($members as $member)
                <tr>
                    <td>{{ $member->name }} @if ($member->id === auth()->id()) <span class="badge badge-active">{{ __('You') }}</span> @endif</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->phone ?: '—' }}</td>
                    <td>{!! eth_date($member->created_at) !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $members->links() }}</div>

@endsection
