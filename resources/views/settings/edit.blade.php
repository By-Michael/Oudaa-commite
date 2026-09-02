@extends('layouts.app')
@section('title', 'Settings')
@section('content')

<div class="panel">
    <div class="panel-head">
        <h2>{{ __('Committee Members') }}</h2>
        <a href="{{ route('members.index') }}" class="btn btn-sm">Manage Committee Members</a>
    </div>
</div>

<div class="form-grid">
    <div class="panel">
        <div class="panel-head"><h2>{{ __('Profile') }}</h2></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('settings.profile') }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <label>Name<span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $committee->name) }}" required data-filter="letters">
                </div>
                <div class="form-row">
                    <label>{{ __('Email') }}<span class="req">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $committee->email) }}" required>
                </div>
                <div class="form-row">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $committee->phone) }}" placeholder="{{ __('e.g. +251 9xx xxx xxx') }}" data-filter="phone">
                </div>

                <button type="submit" class="btn btn-primary">Save Profile</button>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>{{ __('Date Format') }}</h2></div>
        <div class="panel-body">
            <p class="muted" style="margin-top:0;margin-bottom:14px;">{{ __('Choose which calendar system dates are shown in across the app. This applies on this device.') }}</p>
            <div class="date-system-toggle-settings">
                <button type="button" class="date-system-toggle" data-date-toggle aria-pressed="false" title="{{ __('Switch between Gregorian and Ethiopian calendar dates') }}">
                    <span class="date-system-option date-system-gc">{{ __('Gregorian (GC)') }}</span>
                    <span class="date-system-option date-system-ec">{{ __('Ethiopian (EC)') }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>{{ __('Change Password') }}</h2></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('settings.password') }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <label>Current Password<span class="req">*</span></label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-row">
                    <label>{{ __('New Password') }}<span class="req">*</span></label>
                    <input type="password" name="password" required minlength="8">
                </div>
                <div class="form-row">
                    <label>Confirm New Password<span class="req">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="8">
                </div>

                <button type="submit" class="btn btn-primary">{{ __('Update Password') }}</button>
            </form>
        </div>
    </div>
</div>

@endsection
