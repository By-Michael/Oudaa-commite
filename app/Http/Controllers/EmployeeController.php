<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Support\Export\Exportable;

class EmployeeController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        $employees = $this->filtered($request)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees'));
    }

    private function filtered(Request $request)
    {
        return Employee::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('role', 'like', "%{$request->search}%")
                ->orWhere('id_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->employee_id, fn ($q) => $q->where('id', $request->employee_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));
    }

    private function filterSummary(Request $request): array
    {
        $lines = [];
        if ($request->search) $lines[] = "Search: {$request->search}";
        if ($request->status) $lines[] = "Status: {$request->status}";
        if ($request->employee_id) $lines[] = 'Employee #'.$request->employee_id;
        if ($request->date_from) $lines[] = "From: {$request->date_from}";
        if ($request->date_to) $lines[] = "To: {$request->date_to}";

        return $lines;
    }

    private function exportRows(Request $request): array
    {
        $headers = ['Name', 'Role', 'ID Number', 'Salary', 'Payment Date', 'Phone', 'Status', 'Total Paid'];

        $rows = $this->filtered($request)->orderBy('name')->get()
            ->map(fn (Employee $e) => [
                $e->name, $e->role, $e->id_number, money($e->salary),
                $e->payment_date?->format('Y-m-d') ?: '—', $e->phone ?: '—',
                ucfirst($e->status), money($e->totalPaid()),
            ])->all();

        return [$headers, $rows];
    }

    public function exportExcelIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportExcel('Employees', $headers, $rows, 'employees', $this->filterSummary($request));
    }

    public function exportPdfIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportPdf('Employees', $headers, $rows, 'employees', $this->filterSummary($request));
    }

    public function create()
    {
        return view('employees.form', ['employee' => new Employee()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = 'active'; // new employees always start active — status only changes later, via edit.
        Employee::create($data);

        return redirect()->route('employees.index')->with('status', __('Employee added.'));
    }

    public function edit(Request $request)
    {
        $employee = Employee::findOrFail($request->route('employee'));

        return view('employees.form', compact('employee'));
    }

    public function update(Request $request)
    {
        $employee = Employee::findOrFail($request->route('employee'));
        $data = $this->validated($request, $employee);
        $data['status'] = $request->input('status', $employee->status); // status only settable here, on edit
        $employee->update($data);

        return redirect()->route('employees.index')->with('status', __('Employee updated.'));
    }

    /**
     * No delete for employees — terminate instead so salary/payment history stays intact.
     */
    public function toggle(Request $request)
    {
        $employee = Employee::findOrFail($request->route('employee'));
        $employee->update(['status' => $employee->status === 'active' ? 'terminated' : 'active']);

        return back()->with('status', __('Employee status updated.'));
    }

    /**
     * Shows every salary payment logged against this employee via the
     * expenses ledger, plus a running total paid.
     */
    public function show(Request $request)
    {
        $employee = Employee::findOrFail($request->route('employee'));
        $employee->load(['expenses' => fn ($q) => $q->latest('incurred_at')]);

        return view('employees.show', compact('employee'));
    }

    private function validated(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:100', 'unique:employees,id_number,'.($employee?->id ?? 'NULL').',id'],
            'role' => ['required', 'string', 'max:255'],
            'salary' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,terminated'],
        ]);
    }
}
