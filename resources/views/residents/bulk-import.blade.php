@extends('layouts.app')
@section('title', 'Bulk ' . __('Import') . ' Residents')
@section('content')

<div class="panel" style="max-width:560px;">
    <div class="panel-body">

        @if ($errors->has('bulk_import'))
            <div class="alert alert-error" style="margin-bottom:16px;">
                <strong>{{ count($errors->get('bulk_import')) === 1 ? __(":count row couldn't be imported. Nothing was added yet — fix these and re-upload:", ['count' => count($errors->get('bulk_import'))]) : __(":count rows couldn't be imported. Nothing was added yet — fix these and re-upload:", ['count' => count($errors->get('bulk_import'))]) }}</strong>
                <ul style="margin:8px 0 0; padding-left:18px;">
                    @foreach ($errors->get('bulk_import') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p style="margin-top:0; color:var(--n-slate, #667085);">
            Download the template, fill in one row per resident, then upload it here.
            {{ $isCondo ? 'Include the Block Number column since your community uses blocks.' : '' }}
        </p>

        <a href="{{ route('residents.bulk-import.template') }}" class="btn" style="margin-bottom:20px;">
            {{ __('⬇ Download Import Template') }}
        </a>

        <form method="POST" action="{{ route('residents.bulk-import') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-row" style="margin-bottom:12px;">
                <label>Filled-in Excel file (.xlsx)<span class="req">*</span></label>
                <input type="file" name="file" accept=".xlsx,.xls" required>
                @error('file') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary" data-loading-label="{{ __('Importing…') }}">{{ __('Import') }}</button>
            <a href="{{ route('residents.index') }}" class="btn">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>

@endsection
