@extends('layouts.app')
@section('title', $resident->exists ? 'Edit Resident' : 'Add Resident')
@section('content')

<div class="panel" style="max-width:560px;">
    <div class="panel-body">
        <form method="POST" action="{{ $resident->exists ? route('residents.update', $resident) : route('residents.store') }}">
            @csrf
            @if ($resident->{{ __('exists)') }} @method('PUT') @endif

            <div class="form-grid">
                <div class="form-row">
                    <label>Name<span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $resident->name) }}" required data-filter="letters">
                </div>
                <div class="form-row">
                    <label>{{ __('Unit Number') }}<span class="req">*</span></label>
                    <input type="text" name="unit_number" value="{{ old('unit_number', $resident->unit_number) }}" required data-filter="alnum">
                </div>
            </div>
            <div class="form-grid">
                @if ($isCondo)
                <div class="form-row">
                    <label>Block Number (optional)</label>
                    <input type="text" name="block_number" value="{{ old('block_number', $resident->block_number) }}" data-filter="alnum">
                </div>
                @endif
                <div class="form-row">
                    <label>{{ __('ID Number') }}<span class="req">*</span></label>
                    <input type="text" name="id_number" value="{{ old('id_number', $resident->id_number) }}" required data-filter="alnum">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $resident->phone) }}" data-filter="phone">
                </div>
                <div class="form-row">
                    <label>{{ __('Email (optional)') }}</label>
                    <input type="email" name="email" value="{{ old('email', $resident->email) }}">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label>Occupancy</label>
                    <select name="occupancy">
                        <option value="owner" @selected(old('occupancy', $resident->occupancy) === 'owner')>{{ __('Owner') }}</option>
                        <option value="renter" @selected(old('occupancy', $resident->occupancy) === 'renter')>Tenant</option>
                    </select>
                </div>
                @if ($resident->{{ __('exists)') }}
                <div class="form-row">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" @selected(old('status', $resident->status) === 'active')>{{ __('Active') }}</option>
                        <option value="inactive" @selected(old('status', $resident->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ route('residents.index') }}" class="btn">Cancel</a>
        </form>
    </div>
</div>

@endsection
