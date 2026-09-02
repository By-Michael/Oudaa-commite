<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('Summary Report') }}</title>
    <style>
        @page { margin: 26px 30px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1F2933; font-size: 11px; }
        h1 { font-size: 20px; margin: 0 0 2px; color: #14919B; }
        h2 { font-size: 14px; margin: 22px 0 8px; color: #5B4488; border-bottom: 1px solid #E2E8F0; padding-bottom: 4px; }
        .meta { color: #667085; font-size: 10px; margin-bottom: 16px; }
        .stats { display: table; width: 100%; margin-bottom: 6px; }
        .stat { display: table-cell; width: 25%; padding: 10px; background: #F8FAFC; border-radius: 6px; }
        .stat .label { font-size: 9px; color: #667085; text-transform: uppercase; }
        .stat .value { font-size: 15px; font-weight: bold; color: #14919B; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead th { background: #14919B; color: #fff; text-align: left; padding: 5px 8px; font-size: 9.5px; }
        tbody td { padding: 4px 8px; border-bottom: 1px solid #E2E8F0; font-size: 9.5px; }
        tbody tr:nth-child(even) { background: #F8FAFC; }
        .empty { color: #98A2B3; font-style: italic; padding: 8px 0; }
        .footer { position: fixed; bottom: -12px; left: 0; right: 0; font-size: 9px; color: #98A2B3; text-align: center; }
    </style>
</head>
<body>
    <h1>Summary Report</h1>
    <div class="meta">
        Period: {{ $from->format('Y-m-d') }} — {{ $to->format('Y-m-d') }}
        &nbsp;·&nbsp; Generated {{ $generatedAt->format('Y-m-d H:i') }}
    </div>

    <div class="stats">
        <div class="stat"><div class="label">{{ __('Total Funds Balance') }}</div><div class="value">{{ money($totalFundsBalance) }}</div></div>
        <div class="stat"><div class="label">Collected (period)</div><div class="value">{{ money($totalCollected) }}</div></div>
        <div class="stat"><div class="label">{{ __('Spent (period)') }}</div><div class="value">{{ money($totalSpent) }}</div></div>
        <div class="stat"><div class="label">Net (period)</div><div class="value">{{ money($totalCollected - $totalSpent) }}</div></div>
    </div>

    <div class="stats" style="margin-top:8px;">
        <div class="stat"><div class="label">{{ __('Residents') }}</div><div class="value">{{ $residentsActive }} / {{ $residentsTotal }} active</div></div>
        <div class="stat"><div class="label">Employees</div><div class="value">{{ $employeesActive }} / {{ $employeesTotal }} active</div></div>
        <div class="stat"><div class="label">{{ __('Projects') }}</div><div class="value">{{ $projectsActive }} / {{ $projectsTotal }} active</div></div>
        <div class="stat"><div class="label">Funds</div><div class="value">{{ $funds->count() }}</div></div>
    </div>

    <h2>{{ __('Fund Balances') }}</h2>
    @if ($funds->isEmpty())
        <div class="empty">{{ __('No active funds.') }}</div>
    @else
        <table>
            <thead><tr><th>Fund</th><th>{{ __('Category') }}</th><th>Status</th><th>Balance</th></tr></thead>
            <tbody>
            @foreach ($funds as $fund)
                <tr>
                    <td>{{ $fund->name }}</td>
                    <td>{{ $fund->category ?: '—' }}</td>
                    <td>{{ ucfirst($fund->status) }}</td>
                    <td>{{ money($fund->balance()) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <h2>{{ __('Expenses by Category (period)') }}</h2>
    @if ($expensesByCategory->isEmpty())
        <div class="empty">{{ __('No expenses in this period.') }}</div>
    @else
        <table>
            <thead><tr><th>Category</th><th>{{ __('Total') }}</th></tr></thead>
            <tbody>
            @foreach ($expensesByCategory as $row)
                <tr><td>{{ $row->category }}</td><td>{{ money($row->total) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <h2>Top Projects by Spend</h2>
    @if ($topProjects->{{ __('isEmpty())') }}
        <div class="empty">No active projects.</div>
    @else
        <table>
            <thead><tr><th>{{ __('Project') }}</th><th>Fund</th><th>Planned</th><th>{{ __('Spent') }}</th><th>Remaining</th></tr></thead>
            <tbody>
            @foreach ($topProjects as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->fund->name ?? '—' }}</td>
                    <td>{{ money($project->planned_budget) }}</td>
                    <td>{{ money($project->spent()) }}</td>
                    <td>{{ money($project->remaining()) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <h2>Recent Payments (period)</h2>
    @if ($recentPayments->{{ __('isEmpty())') }}
        <div class="empty">No payments in this period.</div>
    @else
        <table>
            <thead><tr><th>{{ __('Date') }}</th><th>Resident</th><th>Fee</th><th>{{ __('Amount') }}</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($recentPayments as $payment)
                <tr>
                    <td>{{ $payment->paid_at?->format('Y-m-d') }}</td>
                    <td>{{ $payment->resident->name ?? '—' }}</td>
                    <td>{{ $payment->fee->name ?? '—' }}</td>
                    <td>{{ money($payment->amount) }}</td>
                    <td>{{ $payment->status }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <h2>Recent Expenses (period)</h2>
    @if ($recentExpenses->{{ __('isEmpty())') }}
        <div class="empty">No expenses in this period.</div>
    @else
        <table>
            <thead><tr><th>{{ __('Date') }}</th><th>Category</th><th>Fund</th><th>{{ __('Project') }}</th><th>Amount</th></tr></thead>
            <tbody>
            @foreach ($recentExpenses as $expense)
                <tr>
                    <td>{{ $expense->incurred_at?->format('Y-m-d') }}</td>
                    <td>{{ $expense->category }}</td>
                    <td>{{ $expense->fund->name ?? '—' }}</td>
                    <td>{{ $expense->project->name ?? '—' }}</td>
                    <td>{{ money($expense->amount) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">Oudaa Committee Panel — Summary Report</div>
</body>
</html>
