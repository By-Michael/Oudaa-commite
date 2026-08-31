<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('fund')
            ->when($request->fund_id, fn ($q) => $q->where('fund_id', $request->fund_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $funds = Fund::orderBy('name')->get();

        return view('projects.index', compact('projects', 'funds'));
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
