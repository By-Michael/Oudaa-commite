@extends('layouts.app')
@section('title', 'Settings')
@section('content')

<div class="panel">
    <div class="panel-head">
        <h2>Committee Members</h2>
        <a href="{{ route('members.index') }}" class="btn btn-sm">Manage Committee Members</a>
    </div>
</div>

<div class="form-grid">
    <div class="panel">
        <div class="panel-head"><h2>Profile</h2></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('settings.profile') }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <label>Name<span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $committee->name) }}" required data-filter="letters">
                </div>
                <div class="form-row">
                    <label>Email<span class="req">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $committee->email) }}" required>
                </div>
                <div class="form-row">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $committee->phone) }}" placeholder="e.g. +251 9xx xxx xxx" data-filter="phone">
                </div>

                <button type="submit" class="btn btn-primary">Save Profile</button>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Change Password</h2></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('settings.password') }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <label>Current Password<span class="req">*</span></label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-row">
                    <label>New Password<span class="req">*</span></label>
                    <input type="password" name="password" required minlength="8">
                </div>
                <div class="form-row">
                    <label>Confirm New Password<span class="req">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="8">
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>

@endsection
