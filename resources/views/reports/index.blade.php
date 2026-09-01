@extends('layouts.app')
@section('title', 'Reports')
@section('content')

<div class="toolbar">
    <form class="search-form" method="GET" action="{{ route('reports.index') }}">
        <label style="font-size:13px;color:var(--md-on-surface-variant);align-self:center;">From</label>
        <input type="date" name="from" value="{{ $from }}" onchange="this.form.submit()">
        <label style="font-size:13px;color:var(--md-on-surface-variant);align-self:center;">To</label>
        <input type="date" name="to" value="{{ $to }}" onchange="this.form.submit()">
    </form>
    <a href="{{ route('reports.summary.pdf', request()->only('from', 'to')) }}" class="btn btn-primary">Download Summary PDF</a>
</div>

<div class="form-grid">
    <div class="panel">
        <div class="panel-head"><h2>Payments Collected Over Time</h2></div>
        <div class="panel-body"><canvas id="chartPayments" height="220"></canvas></div>
    </div>
    <div class="panel">
        <div class="panel-head"><h2>Expenses by Category</h2></div>
        <div class="panel-body"><canvas id="chartExpenses" height="220"></canvas></div>
    </div>
</div>

<div class="form-grid">
    <div class="panel">
        <div class="panel-head"><h2>Fund Balances</h2></div>
        <div class="panel-body"><canvas id="chartFunds" height="220"></canvas></div>
    </div>
    <div class="panel">
        <div class="panel-head"><h2>Residents Breakdown</h2></div>
        <div class="panel-body"><canvas id="chartResidents" height="220"></canvas></div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Project Budget vs Spent</h2></div>
    <div class="panel-body"><canvas id="chartProjects" height="200"></canvas></div>
</div>

@php
    $exportIcon = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>';
    $filterIcon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>';
    $excelIcon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>';
    $pdfIcon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>';

    // [key, label, icon (reuses group/cash/etc), subtitle]
    $exportTypes = [
        'residents' => ['Residents', 'Export residents matching the filters below'],
        'fees'      => ['Fees', 'Export fee records matching the filters below'],
        'payments'  => ['Payments', 'Export payments matching the filters below'],
        'funds'     => ['Funds', 'Export funds matching the filters below'],
        'expenses'  => ['Expenses', 'Export expenses matching the filters below'],
        'employees' => ['Employees', 'Export employees matching the filters below'],
        'projects'  => ['Projects', 'Export projects matching the filters below'],
    ];
@endphp

<div class="panel">
    <div class="panel-head"><h2>Filtered Exports</h2></div>
    <div class="panel-body">
        <p class="muted" style="margin-top:0;">
            Pick filters for a dataset, then export just that selection to Excel or PDF.
        </p>

        <div class="export-grid">

            {{-- Residents --}}
            <div class="export-card">
                <form method="GET" class="export-card-form" id="form-residents">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $exportIcon !!}</span>
                            <div>
                                <h3>Residents</h3>
                                <div class="export-sub muted">{{ $exportTypes['residents'][1] }}</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('residents.export.excel') }}" class="btn btn-sm btn-excel">{!! $excelIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('residents.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $pdfIcon !!} PDF</button>
                        </div>
                    </div>
                    <div class="export-card-body">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-residents">{!! $filterIcon !!} Filter</button>
                        <div class="export-filters" id="filters-residents">
                            <select name="status">
                                <option value="">All statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <select name="occupancy">
                                <option value="">All occupancy</option>
                                <option value="owner">Owner</option>
                                <option value="renter">Renter</option>
                            </select>
                            <input type="date" name="date_from" placeholder="From">
                            <input type="date" name="date_to" placeholder="To">
                        </div>
                    </div>
                </form>
            </div>

            {{-- Fees --}}
            <div class="export-card">
                <form method="GET" class="export-card-form" id="form-fees">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $exportIcon !!}</span>
                            <div>
                                <h3>Fees</h3>
                                <div class="export-sub muted">{{ $exportTypes['fees'][1] }}</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('fees.export.excel') }}" class="btn btn-sm btn-excel">{!! $excelIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('fees.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $pdfIcon !!} PDF</button>
                        </div>
                    </div>
                    <div class="export-card-body">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-fees">{!! $filterIcon !!} Filter</button>
                        <div class="export-filters" id="filters-fees">
                            <select name="fund_id">
                                <option value="">All funds</option>
                                @foreach ($funds as $fund)
                                    <option value="{{ $fund->id }}">{{ $fund->name }}</option>
                                @endforeach
                            </select>
                            <select name="status">
                                <option value="">All statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Payments --}}
            <div class="export-card">
                <form method="GET" class="export-card-form" id="form-payments">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $exportIcon !!}</span>
                            <div>
                                <h3>Payments</h3>
                                <div class="export-sub muted">{{ $exportTypes['payments'][1] }}</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('payments.export.excel') }}" class="btn btn-sm btn-excel">{!! $excelIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('payments.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $pdfIcon !!} PDF</button>
                        </div>
                    </div>
                    <div class="export-card-body">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-payments">{!! $filterIcon !!} Filter</button>
                        <div class="export-filters" id="filters-payments">
                            <select name="resident_id">
                                <option value="">All residents</option>
                                @foreach ($residents as $resident)
                                    <option value="{{ $resident->id }}">{{ $resident->unit_number }} — {{ $resident->name }}</option>
                                @endforeach
                            </select>
                            <select name="status">
                                <option value="">All statuses</option>
                                <option value="PAID">Paid</option>
                                <option value="PENDING">Pending</option>
                                <option value="VOID">Void</option>
                            </select>
                            <input type="date" name="date_from" placeholder="From">
                            <input type="date" name="date_to" placeholder="To">
                        </div>
                    </div>
                </form>
            </div>

            {{-- Funds --}}
            <div class="export-card">
                <form method="GET" class="export-card-form" id="form-funds">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $exportIcon !!}</span>
                            <div>
                                <h3>Funds</h3>
                                <div class="export-sub muted">{{ $exportTypes['funds'][1] }}</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('funds.export.excel') }}" class="btn btn-sm btn-excel">{!! $excelIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('funds.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $pdfIcon !!} PDF</button>
                        </div>
                    </div>
                    <div class="export-card-body">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-funds">{!! $filterIcon !!} Filter</button>
                        <div class="export-filters" id="filters-funds">
                            <select name="status">
                                <option value="">All statuses</option>
                                <option value="active">Active</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Expenses --}}
            <div class="export-card">
                <form method="GET" class="export-card-form" id="form-expenses">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $exportIcon !!}</span>
                            <div>
                                <h3>Expenses</h3>
                                <div class="export-sub muted">{{ $exportTypes['expenses'][1] }}</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('expenses.export.excel') }}" class="btn btn-sm btn-excel">{!! $excelIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('expenses.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $pdfIcon !!} PDF</button>
                        </div>
                    </div>
                    <div class="export-card-body">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-expenses">{!! $filterIcon !!} Filter</button>
                        <div class="export-filters" id="filters-expenses">
                            <select name="fund_id">
                                <option value="">All funds</option>
                                @foreach ($funds as $fund)
                                    <option value="{{ $fund->id }}">{{ $fund->name }}</option>
                                @endforeach
                            </select>
                            <select name="project_id">
                                <option value="">All projects</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <select name="employee_id">
                                <option value="">All employees</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            <input type="date" name="date_from" placeholder="From">
                            <input type="date" name="date_to" placeholder="To">
                        </div>
                    </div>
                </form>
            </div>

            {{-- Employees --}}
            <div class="export-card">
                <form method="GET" class="export-card-form" id="form-employees">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $exportIcon !!}</span>
                            <div>
                                <h3>Employees</h3>
                                <div class="export-sub muted">{{ $exportTypes['employees'][1] }}</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('employees.export.excel') }}" class="btn btn-sm btn-excel">{!! $excelIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('employees.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $pdfIcon !!} PDF</button>
                        </div>
                    </div>
                    <div class="export-card-body">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-employees">{!! $filterIcon !!} Filter</button>
                        <div class="export-filters" id="filters-employees">
                            <select name="status">
                                <option value="">All statuses</option>
                                <option value="active">Active</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Projects --}}
            <div class="export-card">
                <form method="GET" class="export-card-form" id="form-projects">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $exportIcon !!}</span>
                            <div>
                                <h3>Projects</h3>
                                <div class="export-sub muted">{{ $exportTypes['projects'][1] }}</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('projects.export.excel') }}" class="btn btn-sm btn-excel">{!! $excelIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('projects.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $pdfIcon !!} PDF</button>
                        </div>
                    </div>
                    <div class="export-card-body">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-projects">{!! $filterIcon !!} Filter</button>
                        <div class="export-filters" id="filters-projects">
                            <select name="fund_id">
                                <option value="">All funds</option>
                                @foreach ($funds as $fund)
                                    <option value="{{ $fund->id }}">{{ $fund->name }}</option>
                                @endforeach
                            </select>
                            <select name="status">
                                <option value="">All statuses</option>
                                <option value="planned">Planned</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
    .export-grid{
        display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:16px;
    }
    .export-card{
        border:1px solid var(--md-outline-variant); border-radius:var(--md-r-lg); overflow:hidden;
        background:var(--md-surface); display:flex; flex-direction:column;
    }
    .export-card-form{ display:flex; flex-direction:column; }
    .export-card-head{
        display:flex; align-items:center; justify-content:space-between; gap:10px;
        padding:14px 16px; border-bottom:1px solid var(--md-outline-variant);
    }
    .export-card-title{ display:flex; align-items:center; gap:10px; min-width:0; }
    .export-icon{
        width:38px; height:38px; flex-shrink:0; border-radius:10px;
        background:var(--md-primary-container); color:var(--md-primary);
        display:flex; align-items:center; justify-content:center;
    }
    .export-card-title h3{ margin:0; font-size:14px; }
    .export-sub{ font-size:11.5px; margin-top:2px; }
    .export-card-actions{ display:flex; gap:8px; flex-shrink:0; }
    .export-card-actions .btn{ display:inline-flex; align-items:center; gap:5px; padding:6px 12px; }
    .btn-excel{ color:#1B8354; border-color:rgba(27,131,84,.35); }
    .btn-excel:hover{ background:rgba(27,131,84,.08); color:#1B8354; }
    .btn-pdf{ color:#C0392B; border-color:rgba(192,57,43,.35); }
    .btn-pdf:hover{ background:rgba(192,57,43,.08); color:#C0392B; }
    .export-card-body{ padding:12px 16px 16px; }
    .filter-toggle{ display:inline-flex; align-items:center; gap:6px; }
    .filter-toggle.is-open{ background:var(--md-primary-container); color:var(--md-on-primary-container); border-color:transparent; }
    .export-filters{
        display:none; gap:10px; margin-top:12px; flex-wrap:wrap;
    }
    .export-filters.open{ display:flex; }
    .export-filters select, .export-filters input{ flex:1 1 150px; min-width:130px; }
</style>

<script>
(function () {
    document.querySelectorAll('.filter-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.target);
            if (!target) return;
            var open = target.classList.toggle('open');
            btn.classList.toggle('is-open', open);
        });
    });
})();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
(function () {
    var palette = ['#14919B', '#5B4488', '#E08E45', '#3E7CB1', '#B5495B', '#5C8001', '#8A6D3B'];

    new Chart(document.getElementById('chartPayments'), {
        type: 'line',
        data: {
            labels: @json($paymentsByMonth->pluck('ym')),
            datasets: [{
                label: 'Collected',
                data: @json($paymentsByMonth->pluck('total')),
                borderColor: palette[0],
                backgroundColor: palette[0] + '33',
                fill: true,
                tension: 0.3,
            }],
        },
        options: { responsive: true, plugins: { legend: { display: false } } },
    });

    new Chart(document.getElementById('chartExpenses'), {
        type: 'bar',
        data: {
            labels: @json($expensesByCategory->pluck('category')),
            datasets: [{
                label: 'Spent',
                data: @json($expensesByCategory->pluck('total')),
                backgroundColor: palette[2],
            }],
        },
        options: { responsive: true, plugins: { legend: { display: false } } },
    });

    new Chart(document.getElementById('chartFunds'), {
        type: 'bar',
        data: {
            labels: @json($fundBalances->pluck('name')),
            datasets: [{
                label: 'Balance',
                data: @json($fundBalances->pluck('balance')),
                backgroundColor: palette[1],
            }],
        },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } },
    });

    new Chart(document.getElementById('chartResidents'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Owner', 'Renter'],
            datasets: [{
                data: [
                    {{ $residentBreakdown['active'] }},
                    {{ $residentBreakdown['inactive'] }},
                    {{ $residentBreakdown['owner'] }},
                    {{ $residentBreakdown['renter'] }}
                ],
                backgroundColor: palette,
            }],
        },
        options: { responsive: true },
    });

    new Chart(document.getElementById('chartProjects'), {
        type: 'bar',
        data: {
            labels: @json($projectBudgets->pluck('name')),
            datasets: [
                { label: 'Planned Budget', data: @json($projectBudgets->pluck('planned')), backgroundColor: palette[3] },
                { label: 'Spent', data: @json($projectBudgets->pluck('spent')), backgroundColor: palette[4] },
            ],
        },
        options: { responsive: true },
    });
})();
</script>
@endsection
