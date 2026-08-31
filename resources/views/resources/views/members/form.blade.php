@extends('layouts.app')
@section('title', 'Add Committee Member')
@section('content')

<div class="panel" style="max-width:520px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('members.store') }}">
            @csrf

            <div class="form-row">
                <label>Name<span class="req">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required data-filter="letters">
            </div>
            <div class="form-row">
                <label>Email<span class="req">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-row">
                <label>Phone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" data-filter="phone">
            </div>

            <p style="color:var(--n-slate, var(--md-on-surface-variant)); font-size:0.85rem; margin: 4px 0 16px;">
                We'll email them a link to set their own password — no need to set one here.
            </p>

            <button type="submit" class="btn btn-primary">Add Member</button>
            <a href="{{ route('members.index') }}" class="btn">Cancel</a>
        </form>
    </div>
</div>

@endsection
