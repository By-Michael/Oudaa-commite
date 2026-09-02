@extends('layouts.app')
@section('title', __('Record Expense'))
@section('content')

<div class="panel" style="max-width:560px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-row">
                <label>Category<span class="req">*</span></label>
                <select name="category" id="category" required>
                    <option value="">{{ __('Select category…') }}</option>
                    @foreach (['Repairs', 'Utilities', 'Salary', 'Cleaning', 'Security', 'Landscaping', 'Insurance', 'Supplies', 'Other'] as $cat)
                        <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row" id="category-other-row" style="display:none;">
                <label>Specify Category<span class="req" id="category-other-required-mark" style="display:none;">*</span></label>
                <input type="text" name="category_other" id="category_other" value="{{ old('category_other') }}" placeholder="{{ __('Enter the category name') }}" data-filter="letters">
            </div>

            <div class="form-grid">
                <div class="form-row">
                    <label>Amount (ETB)<span class="req">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required data-filter="decimal">
                </div>
                <div class="form-row">
                    <label>{{ __('Date') }}<span class="req">*</span></label>
                    {!! eth_date_input('incurred_at', old('incurred_at', now()->toDateString())) !!}
                </div>
            </div>

            <div class="form-grid">
                <div class="form-row">
                    <label>Vendor</label>
                    <input type="text" name="vendor" value="{{ old('vendor') }}" data-filter="safe-text">
                </div>
                <div class="form-row">
                    <label>{{ __('Fund') }}<span class="req">*</span></label>
                    <select name="fund_id" id="fund_id" required>
                        <option value="">Select fund...</option>
                        @foreach ($funds as $fund)
                            <option value="{{ $fund->id }}" data-name="{{ $fund->name }}" @selected(old('fund_id') == $fund->id)>{{ $fund->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row" id="employee-row" style="display:none;">
                <label>{{ __('Employee') }}</label>
                <select name="employee_id" id="employee_id">
                    <option value="">Select employee…</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" data-salary="{{ $employee->salary }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }} — {{ $employee->role }}</option>
                    @endforeach
                </select>
                <p class="muted" id="employee-salary-hint" style="font-size:12px;margin-top:6px;"></p>
            </div>

            <div class="form-row">
                <label>{{ __('Project (optional)') }}</label>
                <select name="project_id" id="project_id">
                    <option value="">No linked project</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" data-fund-id="{{ $project->fund_id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
                <p class="muted" id="project-fund-hint" style="margin:6px 0 0;font-size:12px;"></p>
            </div>

            <div class="form-row">
                <label>{{ __('Note') }}<span class="req" id="note-required-mark" style="display:none;">*</span></label>
                <input type="text" name="note" id="note" value="{{ old('note') }}" data-filter="safe-text">
                <p class="muted" id="note-hint" style="font-size:12px;margin-top:6px;"></p>
            </div>

            <div class="form-row">
                <label>Receipt (optional)</label>
                <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                <p class="muted" style="font-size:12px;margin-top:6px;">{{ __('JPG, PNG, or PDF, up to 5 MB.') }}</p>
                @error('receipt') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Record Expense</button>
            <a href="{{ route('expenses.index') }}" class="btn">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>

<script>
// Category "Other" reveals a mandatory free-text field.
var categorySelect = document.getElementById('category');
var otherRow = document.getElementById('category-other-row');
var otherInput = document.getElementById('category_other');

function syncCategoryOther() {
    if (categorySelect.value === 'Other') {
        otherRow.style.display = '';
        otherInput.required = true;
        document.getElementById('category-other-required-mark').style.display = 'inline';
    } else {
        otherRow.style.display = 'none';
        otherInput.required = false;
        document.getElementById('category-other-required-mark').style.display = 'none';
    }
}
categorySelect.addEventListener('change', syncCategoryOther);
syncCategoryOther();

// {{ __('Category') }} "Salary" reveals the Employee picker and offers to prefill the amount.
var employeeRow = document.getElementById('employee-row');
var employeeSelect = document.getElementById('employee_id');
var employeeSalaryHint = document.getElementById('employee-salary-hint');
var amountField = document.querySelector('input[name="amount"]');

function syncEmployeeRow() {
    if (categorySelect.value === 'Salary') {
        employeeRow.style.display = '';
    } else {
        employeeRow.style.display = 'none';
        employeeSelect.value = '';
        employeeSalaryHint.textContent = '';
    }
}
categorySelect.addEventListener('change', syncEmployeeRow);
syncEmployeeRow();

employeeSelect.addEventListener('change', function () {
    var opt = this.options[this.selectedIndex];
    var salary = opt ? opt.dataset.salary : null;
    if (salary) {
        employeeSalaryHint.textContent = 'On record: ' + parseFloat(salary).toFixed(2) + '. Amount field is not auto-filled — confirm the actual amount paid.';
    } else {
        employeeSalaryHint.textContent = '';
    }
});

// {{ __('Note') }} becomes required whenever no project is linked.
var noteInput = document.getElementById('note');
var noteMark = document.getElementById('note-required-mark');
var noteHint = document.getElementById('note-hint');

function syncNoteRequirement() {
    var hasProject = !!document.getElementById('project_id').value;
    noteInput.required = !hasProject;
    noteMark.style.display = hasProject ? 'none' : 'inline';
    noteHint.textContent = hasProject
        ? ''
        : 'Required — there\'s no linked project, so a note is the only record of what this was for.';
}
document.getElementById('project_id').addEventListener('change', syncNoteRequirement);
syncNoteRequirement();
</script>

<script>
// When a project is selected, lock the fund to that project's fund —
// keeps a project's "spent" total and its fund's balance from ever
// disagreeing about where the money came from.
document.getElementById('project_id').addEventListener('change', function () {
    var fundSelect = document.getElementById('fund_id');
    var hint = document.getElementById('project-fund-hint');
    var opt = this.options[this.selectedIndex];
    var fundId = opt ? opt.getAttribute('data-fund-id') : '';

    if (this.value && fundId) {
        fundSelect.value = fundId;
        fundSelect.disabled = true;
        // Ensure the disabled select still submits its value.
        var hidden = document.getElementById('fund_id_hidden');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'fund_id';
            hidden.id = 'fund_id_hidden';
            fundSelect.parentNode.appendChild(hidden);
        }
        hidden.value = fundId;
        fundSelect.name = '';
        var fundName = fundSelect.options[fundSelect.selectedIndex].getAttribute('data-name');
        hint.textContent = 'Fund locked to "' + fundName + '" because this project is linked to that fund.';
    } else {
        fundSelect.disabled = false;
        fundSelect.name = 'fund_id';
        var hidden2 = document.getElementById('fund_id_hidden');
        if (hidden2) hidden2.remove();
        hint.textContent = this.value ? 'This project has no linked fund — choose one manually.' : '';
    }
});
</script>

@endsection
