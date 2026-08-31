<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Read-only by design: this controller has no store/update/destroy
 * methods and no routes exist for them. Entries are only ever written
 * via AuditLog::record(), called from model events.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::query()
            ->when($request->subject_type, fn ($q) => $q->where('subject_type', $request->subject_type))
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $subjectTypes = AuditLog::query()->distinct()->orderBy('subject_type')->pluck('subject_type');
        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('audit.index', compact('logs', 'subjectTypes', 'actions'));
    }
}
