<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Support\Export\Exportable;

class ProjectController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        $projects = $this->filtered($request)
            ->with('fund')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $funds = Fund::orderBy('name')->get();
        $totalCount = Project::count();

        return view('projects.index', compact('projects', 'funds', 'totalCount'));
    }

    private function filtered(Request $request)
    {
        return Project::query()
            ->when($request->fund_id, fn ($q) => $q->where('fund_id', $request->fund_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->project_id, fn ($q) => $q->where('id', $request->project_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('start_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('start_date', '<=', $request->date_to));
    }

    private function filterSummary(Request $request): array
    {
        $lines = [];
        if ($request->fund_id) $lines[] = 'Fund: '.(Fund::find($request->fund_id)->name ?? '#'.$request->fund_id);
        if ($request->status) $lines[] = "Status: {$request->status}";
        if ($request->project_id) $lines[] = 'Project #'.$request->project_id;
        if ($request->date_from) $lines[] = "Starting from: {$request->date_from}";
        if ($request->date_to) $lines[] = "Starting to: {$request->date_to}";

        return $lines;
    }

    private function exportRows(Request $request): array
    {
        $headers = ['Name', 'Fund', 'Planned Budget', 'Spent', 'Remaining', 'Start Date', 'End Date', 'Status'];

        $rows = $this->filtered($request)->with('fund')->orderBy('name')->get()
            ->map(fn (Project $p) => [
                $p->name, $p->fund->name ?? '—', money($p->planned_budget), money($p->spent()),
                money($p->remaining()), $p->start_date?->format('Y-m-d') ?: '—',
                $p->end_date?->format('Y-m-d') ?: '—', ucfirst($p->status),
            ])->all();

        return [$headers, $rows];
    }

    public function exportExcelIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportExcel('Projects', $headers, $rows, 'projects', $this->filterSummary($request));
    }

    public function exportPdfIndex(Request $request)
    {
        [$headers, $rows] = $this->exportRows($request);

        return $this->exportPdf('Projects', $headers, $rows, 'projects', $this->filterSummary($request), 'landscape');
    }

    public function create()
    {
        $funds = Fund::active()->orderBy('name')->get();

        return view('projects.form', ['project' => new Project(), 'funds' => $funds]);
    }

    public function store(Request $request)
    {
        Project::create($this->validated($request));

        return redirect()->route('projects.index')->with('status', 'Project created.');
    }

    public function edit(Request $request)
    {
        $project = Project::findOrFail($request->route('project'));
        $funds = Fund::active()->orderBy('name')->get();

        return view('projects.form', compact('project', 'funds'));
    }

    public function update(Request $request)
    {
        $project = Project::findOrFail($request->route('project'));
        $project->update($this->validated($request));

        return redirect()->route('projects.index')->with('status', 'Project updated.');
    }

    /**
     * Shows how this project connects to the rest of the system: its
     * linked fund and every expense recorded against it — the
     * "inter-project" view tying spend back to funds.
     */
    public function show(Request $request)
    {
        $project = Project::findOrFail($request->route('project'));
        $project->load(['fund', 'expenses' => fn ($q) => $q->latest('incurred_at')]);

        return view('projects.show', compact('project'));
    }

    public function archive(Request $request)
    {
        $project = Project::findOrFail($request->route('project'));
        $project->update([
            'status' => $project->status === 'archived' ? 'active' : 'archived',
        ]);

        return back()->with('status', 'Project status updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fund_id' => ['required', 'exists:funds,id'],
            'planned_budget' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:planned,active,completed,archived'],
        ]);
    }
}
