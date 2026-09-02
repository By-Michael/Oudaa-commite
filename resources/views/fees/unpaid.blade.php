@extends('layouts.app')
@section('title', 'Unpaid — ' . $fee->name)
@section('content')

<div class="panel">
    <div class="panel-head">
        <h2>{{ __(':fee — Current period: :period', ['fee' => $fee->name, 'period' => $periodKey]) }}</h2>
        <a href="{{ route('fees.index') }}" class="btn btn-sm">{{ __('Back to Fees') }}</a>
    </div>
    <div class="panel-body" style="padding:0;">
        @if ($unpaidResidents->isEmpty())
            <div class="empty">{{ __('Every active resident has a PAID payment for this fee this period.') }}</div>
        @else
            <table>
                <thead><tr><th>{{ __('Unit') }}</th><th>{{ __('Name') }}</th><th>{{ __('ID Number') }}</th><th>{{ __('Phone') }}</th></tr></thead>
                <tbody>
                @foreach ($unpaidResidents as $resident)
                    <tr>
                        <td>{{ $resident->unit_number }}</td>
                        <td>{{ $resident->name }}</td>
                        <td>{{ $resident->id_number ?: '—' }}</td>
                        <td>{{ $resident->phone ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<p class="muted">{{ __(':count active resident(s) have not paid this fee for the current period.', ['count' => $unpaidResidents->count()]) }}</p>

@endsection
