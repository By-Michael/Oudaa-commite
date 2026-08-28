@extends('layouts.app')
@section('title', 'Add Committee Member')
@section('content')

<div class="panel" style="max-width:520px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('members.store') }}">
            @csrf

            <div class="form-row">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-row">
                <label>Phone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}">
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="8">
                </div>
                <div class="form-row">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" required minlength="8">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Add Member</button>
            <a href="{{ route('members.index') }}" class="btn">Cancel</a>
        </form>
    </div>
</div>

@endsection
