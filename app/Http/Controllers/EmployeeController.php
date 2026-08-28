<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('role', 'like', "%{$request->search}%")
                ->orWhere('id_number', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees'));
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

        return redirect()->route('employees.index')->with('status', 'Employee added.');
    }

    public function edit(Employee $employee)
    {
        return view('employees.form', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validated($request);
        $data['status'] = $request->input('status', $employee->status); // status only settable here, on edit
        $employee->update($data);

        return redirect()->route('employees.index')->with('status', 'Employee updated.');
    }

    /**
     * No delete for employees — terminate instead so salary/payment history stays intact.
     */
    public function toggle(Employee $employee)
    {
        $employee->update(['status' => $employee->status === 'active' ? 'terminated' : 'active']);

        return back()->with('status', 'Employee status updated.');
    }

    /**
     * Shows every salary payment logged against this employee via the
     * expenses ledger, plus a running total paid.
     */
    public function show(Employee $employee)
    {
        $employee->load(['expenses' => fn ($q) => $q->latest('incurred_at')]);

        return view('employees.show', compact('employee'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:100', 'unique:employees,id_number,'.($request->route('employee')?->id ?? 'NULL').',id'],
            'role' => ['required', 'string', 'max:255'],
            'salary' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,terminated'],
        ]);
    }
}
