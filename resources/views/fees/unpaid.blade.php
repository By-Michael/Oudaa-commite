@extends('layouts.app')
@section('title', 'Unpaid — ' . $fee->name)
@section('content')

<div class="panel">
    <div class="panel-head">
        <h2>{{ $fee->name }} — Current period: {{ $periodKey }}</h2>
        <a href="{{ route('fees.index') }}" class="btn btn-sm">Back to Fees</a>
    </div>
    <div class="panel-body" style="padding:0;">
        @if ($unpaidResidents->isEmpty())
            <div class="empty">Every active resident has a PAID payment for this fee this period.</div>
        @else
            <table>
                <thead><tr><th>Unit</th><th>Name</th><th>ID Number</th><th>Phone</th></tr></thead>
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

<p class="muted">{{ $unpaidResidents->count() }} active resident(s) have not paid this fee for the current period.</p>

@endsection
