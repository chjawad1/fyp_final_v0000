<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScopeDocument;
use App\Models\Project;
use Illuminate\Http\Request;

class ScopeReviewController extends Controller
{
    /**
     * Display a listing of scope documents pending review.
     */
    public function index(Request $request)
    {
        $query = ScopeDocument::with([
            'project:id,title,user_id,supervisor_id,current_phase,semester,is_late',
            'project.student:id,name,email',
            'project.supervisor:id,name,email',
            'uploader:id,name',
            'reviewer:id,name',
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default:  show pending reviews first
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END");
        }

        // Filter by semester
        if ($request->filled('semester')) {
            $query->whereHas('project', function ($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        // Search by project title or student name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('project', function ($pq) use ($search) {
                    $pq->where('title', 'like', "%{$search}%");
                })->orWhereHas('project. student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $scopeDocuments = $query->latest()
                                ->paginate(15)
                                ->withQueryString();

        // Get statistics
        $stats = [
            'pending' => ScopeDocument::where('status', 'pending')->count(),
            'approved' => ScopeDocument::where('status', 'approved')->count(),
            'rejected' => ScopeDocument::where('status', 'rejected')->count(),
            'revision_required' => ScopeDocument::where('status', 'revision_required')->count(),
        ];

        // Get unique semesters for filter
        $semesters = Project::whereNotNull('semester')
                            ->distinct()
                            ->orderBy('semester', 'desc')
                            ->pluck('semester');

        return view('admin.scope-reviews.index', compact('scopeDocuments', 'stats', 'semesters'));
    }

    /**
     * Display the specified scope document with full history.
     */
    public function show(ScopeDocument $scopeDocument)
    {
        $scopeDocument->load([
            'project:id,title,description,user_id,supervisor_id,status,current_phase,semester,is_late,created_at',
            'project.student:id,name,email',
            'project.supervisor:id,name,email',
            'uploader:id,name',
            'reviewer:id,name',
        ]);

        // Get all versions of scope documents for this project
        $allVersions = $scopeDocument->project
            ->scopeDocuments()
            ->with(['uploader:id,name', 'reviewer:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get previous version for comparison (if exists)
        $previousVersion = $scopeDocument->getPreviousVersion();

        return view('admin.scope-reviews.show', compact('scopeDocument', 'allVersions', 'previousVersion'));
    }

    /**
     * Approve the scope document.
     */
    public function approve(Request $request, ScopeDocument $scopeDocument)
    {
        $request->validate([
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        // Check if already reviewed
        if ($scopeDocument->isReviewed() && !$scopeDocument->isPending()) {
            return back()->withErrors(['error' => 'This document has already been reviewed. ']);
        }

        $scopeDocument->approve(auth()->id(), $request->feedback);

        // Update project phase if needed
        $project = $scopeDocument->project;
        if ($project->isScopePhase()) {
            $project->update(['current_phase' => Project::PHASE_DEFENCE]);
        }

        return redirect()
            ->route('admin.scope-reviews.index')
            ->with('success', 'Scope document approved successfully.  Project advanced to Defence phase.');
    }

    /**
     * Reject the scope document. 
     */
    public function reject(Request $request, ScopeDocument $scopeDocument)
    {
        $request->validate([
            'feedback' => ['required', 'string', 'max:2000'],
        ]);

        // Check if already reviewed
        if ($scopeDocument->isReviewed() && !$scopeDocument->isPending()) {
            return back()->withErrors(['error' => 'This document has already been reviewed.']);
        }

        $scopeDocument->reject(auth()->id(), $request->feedback);

        return redirect()
            ->route('admin.scope-reviews.index')
            ->with('success', 'Scope document rejected.  Student will need to submit a new project.');
    }

    /**
     * Request revision for the scope document.
     */
    public function requestRevision(Request $request, ScopeDocument $scopeDocument)
    {
        $request->validate([
            'feedback' => ['required', 'string', 'max:2000'],
        ]);

        // Check if already reviewed
        if ($scopeDocument->isReviewed() && !$scopeDocument->isPending()) {
            return back()->withErrors(['error' => 'This document has already been reviewed.']);
        }

        $scopeDocument->requestRevision(auth()->id(), $request->feedback);

        return redirect()
            ->route('admin.scope-reviews.index')
            ->with('success', 'Revision requested.  Student can upload a new version.');
    }
}