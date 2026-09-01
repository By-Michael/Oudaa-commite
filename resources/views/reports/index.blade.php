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

<div class="panel">
    <div class="panel-head"><h2>Filtered Exports</h2></div>
    <div class="panel-body">
        <p class="muted" style="margin-top:0;">
            Pick filters for a dataset, then export just that selection to Excel or PDF.
        </p>

        <div class="form-grid">
            {{-- Residents --}}
            <form method="GET" action="" class="export-block">
                <h3>Residents</h3>
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
                <div class="export-actions">
                    <button type="submit" formaction="{{ route('residents.export.excel') }}" class="btn btn-sm">Excel</button>
                    <button type="submit" formaction="{{ route('residents.export.pdf') }}" class="btn btn-sm">PDF</button>
                </div>
            </form>

            {{-- Fees --}}
            <form method="GET" action="" class="export-block">
                <h3>Fees</h3>
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
                <div class="export-actions">
                    <button type="submit" formaction="{{ route('fees.export.excel') }}" class="btn btn-sm">Excel</button>
                    <button type="submit" formaction="{{ route('fees.export.pdf') }}" class="btn btn-sm">PDF</button>
                </div>
            </form>

            {{-- Payments --}}
            <form method="GET" action="" class="export-block">
                <h3>Payments</h3>
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
                <div class="export-actions">
                    <button type="submit" formaction="{{ route('payments.export.excel') }}" class="btn btn-sm">Excel</button>
                    <button type="submit" formaction="{{ route('payments.export.pdf') }}" class="btn btn-sm">PDF</button>
                </div>
            </form>

            {{-- Funds --}}
            <form method="GET" action="" class="export-block">
                <h3>Funds</h3>
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
                <div class="export-actions">
                    <button type="submit" formaction="{{ route('funds.export.excel') }}" class="btn btn-sm">Excel</button>
                    <button type="submit" formaction="{{ route('funds.export.pdf') }}" class="btn btn-sm">PDF</button>
                </div>
            </form>

            {{-- Expenses --}}
            <form method="GET" action="" class="export-block">
                <h3>Expenses</h3>
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
                <div class="export-actions">
                    <button type="submit" formaction="{{ route('expenses.export.excel') }}" class="btn btn-sm">Excel</button>
                    <button type="submit" formaction="{{ route('expenses.export.pdf') }}" class="btn btn-sm">PDF</button>
                </div>
            </form>

            {{-- Employees --}}
            <form method="GET" action="" class="export-block">
                <h3>Employees</h3>
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="terminated">Terminated</option>
                </select>
                <div class="export-actions">
                    <button type="submit" formaction="{{ route('employees.export.excel') }}" class="btn btn-sm">Excel</button>
                    <button type="submit" formaction="{{ route('employees.export.pdf') }}" class="btn btn-sm">PDF</button>
                </div>
            </form>

            {{-- Projects --}}
            <form method="GET" action="" class="export-block">
                <h3>Projects</h3>
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
                <div class="export-actions">
                    <button type="submit" formaction="{{ route('projects.export.excel') }}" class="btn btn-sm">Excel</button>
                    <button type="submit" formaction="{{ route('projects.export.pdf') }}" class="btn btn-sm">PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .export-block { display:flex; flex-direction:column; gap:8px; padding:14px; border:1px solid var(--md-outline-variant); border-radius:10px; }
    .export-block h3 { margin:0 0 4px; font-size:15px; }
    .export-block select, .export-block input { width:100%; }
    .export-actions { display:flex; gap:8px; margin-top:4px; }
</style>

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
