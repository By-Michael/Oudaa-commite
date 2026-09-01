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
    $filterIcon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>';
    $downloadIcon = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>';
    $groupIcon = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';

    $money = fn ($n) => number_format((float) $n, 2);
@endphp

<div class="panel">
    <div class="panel-head"><h2>Filtered Exports</h2></div>
    <div class="panel-body">
        <p class="muted" style="margin-top:0;">
            Pick filters for a dataset, review the matching records below, then export just that selection to Excel or PDF.
        </p>

        <div class="export-stack">

            {{-- Residents --}}
            <div class="export-card">
                <form method="GET" action="{{ route('reports.index') }}" class="export-card-form" id="form-residents">
                    <input type="hidden" name="block" value="residents">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $groupIcon !!}</span>
                            <div>
                                <h3>Residents</h3>
                                <div class="export-sub muted">{{ $residentsTable->total() }} of {{ \App\Models\Resident::count() }} members match the current filters</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('residents.export.excel') }}" class="btn btn-sm btn-excel">{!! $downloadIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('residents.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $downloadIcon !!} PDF</button>
                        </div>
                    </div>

                    <div class="export-card-toolbar">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-residents">{!! $filterIcon !!} Filter</button>
                    </div>

                    <div class="export-filters" id="filters-residents">
                        <select name="status">
                            <option value="">All statuses</option>
                            <option value="active" @selected($block === 'residents' && request('status') === 'active')>Active</option>
                            <option value="inactive" @selected($block === 'residents' && request('status') === 'inactive')>Inactive</option>
                        </select>
                        <select name="occupancy">
                            <option value="">All occupancy</option>
                            <option value="owner" @selected($block === 'residents' && request('occupancy') === 'owner')>Owner</option>
                            <option value="renter" @selected($block === 'residents' && request('occupancy') === 'renter')>Renter</option>
                        </select>
                        <input type="date" name="date_from" placeholder="From" value="{{ $block === 'residents' ? request('date_from') : '' }}">
                        <input type="date" name="date_to" placeholder="To" value="{{ $block === 'residents' ? request('date_to') : '' }}">
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>

                    <div class="export-table-wrap">
                        @if ($residentsTable->isEmpty())
                            <div class="empty">No residents found.</div>
                        @else
                            <table>
                                <thead><tr><th>Name</th><th>Unit</th><th>Email</th><th>Status</th><th>Joined</th></tr></thead>
                                <tbody>
                                @foreach ($residentsTable as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->unit_number }}</td>
                                        <td>{{ $row->email ?: '—' }}</td>
                                        <td><span class="badge badge-{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                                        <td>{{ $row->created_at?->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </form>
                <div class="pagination export-pagination">{{ $residentsTable->onEachSide(1)->links('vendor.pagination.custom') }}</div>
            </div>

            {{-- Fees --}}
            <div class="export-card">
                <form method="GET" action="{{ route('reports.index') }}" class="export-card-form" id="form-fees">
                    <input type="hidden" name="block" value="fees">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $groupIcon !!}</span>
                            <div>
                                <h3>Fees</h3>
                                <div class="export-sub muted">{{ $feesTable->total() }} of {{ \App\Models\Fee::count() }} fees match the current filters</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('fees.export.excel') }}" class="btn btn-sm btn-excel">{!! $downloadIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('fees.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $downloadIcon !!} PDF</button>
                        </div>
                    </div>

                    <div class="export-card-toolbar">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-fees">{!! $filterIcon !!} Filter</button>
                    </div>

                    <div class="export-filters" id="filters-fees">
                        <select name="fund_id">
                            <option value="">All funds</option>
                            @foreach ($funds as $fund)
                                <option value="{{ $fund->id }}" @selected($block === 'fees' && (string) request('fund_id') === (string) $fund->id)>{{ $fund->name }}</option>
                            @endforeach
                        </select>
                        <select name="status">
                            <option value="">All statuses</option>
                            <option value="active" @selected($block === 'fees' && request('status') === 'active')>Active</option>
                            <option value="inactive" @selected($block === 'fees' && request('status') === 'inactive')>Inactive</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>

                    <div class="export-table-wrap">
                        @if ($feesTable->isEmpty())
                            <div class="empty">No fees found.</div>
                        @else
                            <table>
                                <thead><tr><th>Name</th><th>Fund</th><th>Amount</th><th>Frequency</th><th>Status</th></tr></thead>
                                <tbody>
                                @foreach ($feesTable as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->fund->name ?? '—' }}</td>
                                        <td>{{ $money($row->amount) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $row->frequency)) }}</td>
                                        <td><span class="badge badge-{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </form>
                <div class="pagination export-pagination">{{ $feesTable->onEachSide(1)->links('vendor.pagination.custom') }}</div>
            </div>

            {{-- Payments --}}
            <div class="export-card">
                <form method="GET" action="{{ route('reports.index') }}" class="export-card-form" id="form-payments">
                    <input type="hidden" name="block" value="payments">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $groupIcon !!}</span>
                            <div>
                                <h3>Payments</h3>
                                <div class="export-sub muted">{{ $paymentsTable->total() }} of {{ \App\Models\Payment::count() }} payments match the current filters</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('payments.export.excel') }}" class="btn btn-sm btn-excel">{!! $downloadIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('payments.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $downloadIcon !!} PDF</button>
                        </div>
                    </div>

                    <div class="export-card-toolbar">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-payments">{!! $filterIcon !!} Filter</button>
                    </div>

                    <div class="export-filters" id="filters-payments">
                        <select name="resident_id">
                            <option value="">All residents</option>
                            @foreach ($residents as $resident)
                                <option value="{{ $resident->id }}" @selected($block === 'payments' && (string) request('resident_id') === (string) $resident->id)>{{ $resident->unit_number }} — {{ $resident->name }}</option>
                            @endforeach
                        </select>
                        <select name="status">
                            <option value="">All statuses</option>
                            <option value="PAID" @selected($block === 'payments' && request('status') === 'PAID')>Paid</option>
                            <option value="PENDING" @selected($block === 'payments' && request('status') === 'PENDING')>Pending</option>
                            <option value="VOID" @selected($block === 'payments' && request('status') === 'VOID')>Void</option>
                        </select>
                        <input type="date" name="date_from" placeholder="From" value="{{ $block === 'payments' ? request('date_from') : '' }}">
                        <input type="date" name="date_to" placeholder="To" value="{{ $block === 'payments' ? request('date_to') : '' }}">
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>

                    <div class="export-table-wrap">
                        @if ($paymentsTable->isEmpty())
                            <div class="empty">No payments found.</div>
                        @else
                            <table>
                                <thead><tr><th>Resident</th><th>Fee</th><th>Amount</th><th>Status</th><th>Paid At</th></tr></thead>
                                <tbody>
                                @foreach ($paymentsTable as $row)
                                    <tr>
                                        <td>{{ $row->resident->name ?? '—' }}</td>
                                        <td>{{ $row->fee->name ?? '—' }}</td>
                                        <td>{{ $money($row->amount) }}</td>
                                        <td><span class="badge badge-{{ strtolower($row->status) }}">{{ ucfirst(strtolower($row->status)) }}</span></td>
                                        <td>{{ $row->paid_at?->format('d M Y') ?: '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </form>
                <div class="pagination export-pagination">{{ $paymentsTable->onEachSide(1)->links('vendor.pagination.custom') }}</div>
            </div>

            {{-- Funds --}}
            <div class="export-card">
                <form method="GET" action="{{ route('reports.index') }}" class="export-card-form" id="form-funds">
                    <input type="hidden" name="block" value="funds">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $groupIcon !!}</span>
                            <div>
                                <h3>Funds</h3>
                                <div class="export-sub muted">{{ $fundsTable->total() }} of {{ \App\Models\Fund::count() }} funds match the current filters</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('funds.export.excel') }}" class="btn btn-sm btn-excel">{!! $downloadIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('funds.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $downloadIcon !!} PDF</button>
                        </div>
                    </div>

                    <div class="export-card-toolbar">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-funds">{!! $filterIcon !!} Filter</button>
                    </div>

                    <div class="export-filters" id="filters-funds">
                        <select name="status">
                            <option value="">All statuses</option>
                            <option value="active" @selected($block === 'funds' && request('status') === 'active')>Active</option>
                            <option value="archived" @selected($block === 'funds' && request('status') === 'archived')>Archived</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>

                    <div class="export-table-wrap">
                        @if ($fundsTable->isEmpty())
                            <div class="empty">No funds found.</div>
                        @else
                            <table>
                                <thead><tr><th>Name</th><th>Category</th><th>Status</th><th>Balance</th></tr></thead>
                                <tbody>
                                @foreach ($fundsTable as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->category ?: '—' }}</td>
                                        <td><span class="badge badge-{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                                        <td>{{ $money($row->balance()) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </form>
                <div class="pagination export-pagination">{{ $fundsTable->onEachSide(1)->links('vendor.pagination.custom') }}</div>
            </div>

            {{-- Expenses --}}
            <div class="export-card">
                <form method="GET" action="{{ route('reports.index') }}" class="export-card-form" id="form-expenses">
                    <input type="hidden" name="block" value="expenses">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $groupIcon !!}</span>
                            <div>
                                <h3>Expenses</h3>
                                <div class="export-sub muted">{{ $expensesTable->total() }} of {{ \App\Models\Expense::count() }} expenses match the current filters</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('expenses.export.excel') }}" class="btn btn-sm btn-excel">{!! $downloadIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('expenses.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $downloadIcon !!} PDF</button>
                        </div>
                    </div>

                    <div class="export-card-toolbar">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-expenses">{!! $filterIcon !!} Filter</button>
                    </div>

                    <div class="export-filters" id="filters-expenses">
                        <select name="fund_id">
                            <option value="">All funds</option>
                            @foreach ($funds as $fund)
                                <option value="{{ $fund->id }}" @selected($block === 'expenses' && (string) request('fund_id') === (string) $fund->id)>{{ $fund->name }}</option>
                            @endforeach
                        </select>
                        <select name="project_id">
                            <option value="">All projects</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected($block === 'expenses' && (string) request('project_id') === (string) $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                        <select name="employee_id">
                            <option value="">All employees</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected($block === 'expenses' && (string) request('employee_id') === (string) $employee->id)>{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date_from" placeholder="From" value="{{ $block === 'expenses' ? request('date_from') : '' }}">
                        <input type="date" name="date_to" placeholder="To" value="{{ $block === 'expenses' ? request('date_to') : '' }}">
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>

                    <div class="export-table-wrap">
                        @if ($expensesTable->isEmpty())
                            <div class="empty">No expenses found.</div>
                        @else
                            <table>
                                <thead><tr><th>Category</th><th>Fund</th><th>Project</th><th>Amount</th><th>Date</th></tr></thead>
                                <tbody>
                                @foreach ($expensesTable as $row)
                                    <tr>
                                        <td>{{ $row->category }}</td>
                                        <td>{{ $row->fund->name ?? '—' }}</td>
                                        <td>{{ $row->project->name ?? '—' }}</td>
                                        <td>{{ $money($row->amount) }}</td>
                                        <td>{{ $row->incurred_at?->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </form>
                <div class="pagination export-pagination">{{ $expensesTable->onEachSide(1)->links('vendor.pagination.custom') }}</div>
            </div>

            {{-- Employees --}}
            <div class="export-card">
                <form method="GET" action="{{ route('reports.index') }}" class="export-card-form" id="form-employees">
                    <input type="hidden" name="block" value="employees">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $groupIcon !!}</span>
                            <div>
                                <h3>Employees</h3>
                                <div class="export-sub muted">{{ $employeesTable->total() }} of {{ \App\Models\Employee::count() }} employees match the current filters</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('employees.export.excel') }}" class="btn btn-sm btn-excel">{!! $downloadIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('employees.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $downloadIcon !!} PDF</button>
                        </div>
                    </div>

                    <div class="export-card-toolbar">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-employees">{!! $filterIcon !!} Filter</button>
                    </div>

                    <div class="export-filters" id="filters-employees">
                        <select name="status">
                            <option value="">All statuses</option>
                            <option value="active" @selected($block === 'employees' && request('status') === 'active')>Active</option>
                            <option value="terminated" @selected($block === 'employees' && request('status') === 'terminated')>Terminated</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>

                    <div class="export-table-wrap">
                        @if ($employeesTable->isEmpty())
                            <div class="empty">No employees found.</div>
                        @else
                            <table>
                                <thead><tr><th>Name</th><th>Role</th><th>Phone</th><th>Status</th></tr></thead>
                                <tbody>
                                @foreach ($employeesTable as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->role ?: '—' }}</td>
                                        <td>{{ $row->phone ?: '—' }}</td>
                                        <td><span class="badge badge-{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </form>
                <div class="pagination export-pagination">{{ $employeesTable->onEachSide(1)->links('vendor.pagination.custom') }}</div>
            </div>

            {{-- Projects --}}
            <div class="export-card">
                <form method="GET" action="{{ route('reports.index') }}" class="export-card-form" id="form-projects">
                    <input type="hidden" name="block" value="projects">
                    <div class="export-card-head">
                        <div class="export-card-title">
                            <span class="export-icon">{!! $groupIcon !!}</span>
                            <div>
                                <h3>Projects</h3>
                                <div class="export-sub muted">{{ $projectsTable->total() }} of {{ \App\Models\Project::count() }} projects match the current filters</div>
                            </div>
                        </div>
                        <div class="export-card-actions">
                            <button type="submit" formaction="{{ route('projects.export.excel') }}" class="btn btn-sm btn-excel">{!! $downloadIcon !!} Excel</button>
                            <button type="submit" formaction="{{ route('projects.export.pdf') }}" class="btn btn-sm btn-pdf">{!! $downloadIcon !!} PDF</button>
                        </div>
                    </div>

                    <div class="export-card-toolbar">
                        <button type="button" class="btn btn-sm filter-toggle" data-target="filters-projects">{!! $filterIcon !!} Filter</button>
                    </div>

                    <div class="export-filters" id="filters-projects">
                        <select name="fund_id">
                            <option value="">All funds</option>
                            @foreach ($funds as $fund)
                                <option value="{{ $fund->id }}" @selected($block === 'projects' && (string) request('fund_id') === (string) $fund->id)>{{ $fund->name }}</option>
                            @endforeach
                        </select>
                        <select name="status">
                            <option value="">All statuses</option>
                            <option value="planned" @selected($block === 'projects' && request('status') === 'planned')>Planned</option>
                            <option value="active" @selected($block === 'projects' && request('status') === 'active')>Active</option>
                            <option value="completed" @selected($block === 'projects' && request('status') === 'completed')>Completed</option>
                            <option value="archived" @selected($block === 'projects' && request('status') === 'archived')>Archived</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>

                    <div class="export-table-wrap">
                        @if ($projectsTable->isEmpty())
                            <div class="empty">No projects found.</div>
                        @else
                            <table>
                                <thead><tr><th>Name</th><th>Fund</th><th>Status</th><th>Planned</th><th>Spent</th></tr></thead>
                                <tbody>
                                @foreach ($projectsTable as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->fund->name ?? '—' }}</td>
                                        <td><span class="badge badge-{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                                        <td>{{ $money($row->planned_budget) }}</td>
                                        <td>{{ $money($row->spent()) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </form>
                <div class="pagination export-pagination">{{ $projectsTable->onEachSide(1)->links('vendor.pagination.custom') }}</div>
            </div>

        </div>
    </div>
</div>

<style>
    .export-stack{ display:flex; flex-direction:column; gap:18px; }
    .export-card{
        border:1px solid var(--md-outline-variant); border-radius:var(--md-r-lg); overflow:hidden;
        background:var(--md-surface);
    }
    .export-card-form{ display:flex; flex-direction:column; }
    .export-card-head{
        display:flex; align-items:center; justify-content:space-between; gap:10px;
        padding:16px 18px; border-bottom:1px solid var(--md-outline-variant);
    }
    .export-card-title{ display:flex; align-items:center; gap:12px; min-width:0; }
    .export-icon{
        width:40px; height:40px; flex-shrink:0; border-radius:10px;
        background:var(--md-primary-container); color:var(--md-primary);
        display:flex; align-items:center; justify-content:center;
    }
    .export-card-title h3{ margin:0; font-size:15px; }
    .export-sub{ font-size:12px; margin-top:2px; }
    .export-card-actions{ display:flex; gap:8px; flex-shrink:0; }
    .export-card-actions .btn{ display:inline-flex; align-items:center; gap:5px; padding:6px 12px; }
    .btn-excel{ color:#1B8354; border-color:rgba(27,131,84,.35); }
    .btn-excel:hover{ background:rgba(27,131,84,.08); color:#1B8354; }
    .btn-pdf{ color:#C0392B; border-color:rgba(192,57,43,.35); }
    .btn-pdf:hover{ background:rgba(192,57,43,.08); color:#C0392B; }
    .export-card-toolbar{ padding:14px 18px 0; }
    .filter-toggle{ display:inline-flex; align-items:center; gap:6px; }
    .filter-toggle.is-open{ background:var(--md-primary-container); color:var(--md-on-primary-container); border-color:transparent; }
    .export-filters{
        display:none; gap:10px; margin:12px 18px 0; flex-wrap:wrap; align-items:center;
    }
    .export-filters.open{ display:flex; }
    .export-filters select, .export-filters input{ flex:1 1 150px; min-width:130px; width:auto; }
    .export-table-wrap{ margin-top:14px; overflow-x:auto; }
    .export-table-wrap table{ margin:0; }
    .export-pagination{ padding:10px 18px 16px; margin:0; border-top:1px solid var(--md-outline-variant); }
    .badge-terminated{ background:var(--md-error-container); color:var(--md-error); }
    .badge-planned{ background:var(--md-warning-container); color:var(--md-warning); }
    .badge-completed{ background:var(--md-success-container); color:var(--md-success); }
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
    // Auto-expand the filter row for whichever block currently has
    // active filters applied (i.e. the ?block=... in the URL).
    var params = new URLSearchParams(window.location.search);
    var activeBlock = params.get('block');
    if (activeBlock) {
        var el = document.getElementById('filters-' + activeBlock);
        var toggle = document.querySelector('.filter-toggle[data-target="filters-' + activeBlock + '"]');
        if (el) el.classList.add('open');
        if (toggle) toggle.classList.add('is-open');
    }
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
