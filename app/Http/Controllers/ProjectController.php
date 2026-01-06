<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\ScopeDocument;
use App\Models\FypPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $projects = collect();

        if ($user->role === 'student') {
            $projects = $user->projects()
                ->with(['supervisor', 'latestScopeDocument'])
                ->latest()
                ->get();

            // Add deadline info to each project
            $projects->each(function ($project) {
                $project->deadline_info = $project->getCurrentPhaseDeadlineInfo();
            });
        } elseif ($user->role === 'supervisor') {
            $projects = $user->supervisedProjects()
                ->with(['student', 'latestScopeDocument'])
                ->latest()
                ->get();
        }

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }

    /**
 * Show the form for creating a new resource. 
 */
public function create()
{
    $user = Auth::user();

    // Check if student can create new project (limit 3, no approved projects)
    if (!$user->canCreateNewProject()) {
        return redirect()
            ->route('projects.index')
            ->with('error', $user->getCannotCreateProjectReason());
    }

    $supervisors = User::where('role', 'supervisor')
        ->whereHas('supervisorProfile', function ($query) {
            $query->where('available_slots', '>', 0);
        })
        ->orderBy('name')
        ->get();

    // Get current active semester
    $currentSemester = FypPhase::where('is_active', true)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->value('semester');

    // Check if idea submission phase is active
    $ideaPhase = FypPhase::where('slug', 'idea_approval')
        ->where('is_active', true)
        ->where('start_date', '<=', now())
        ->first();

    $canSubmit = true;
    $deadlineWarning = null;

    if ($ideaPhase) {
        if ($ideaPhase->isDeadlinePassed()) {
            if (! $ideaPhase->allow_late) {
                $canSubmit = false;
                $deadlineWarning = 'The idea submission deadline has passed. You cannot submit a new project.';
            } else {
                $deadlineWarning = 'Warning: The idea submission deadline has passed.  Your submission will be marked as LATE.';
            }
        } elseif ($ideaPhase->days_remaining <= 3) {
            $deadlineWarning = 'Warning:  Only ' . $ideaPhase->days_remaining . ' day(s) remaining until the deadline! ';
        }
    }

    // Get current project count for display
    $currentProjectCount = $user->projects()->count();

    return view('projects.create', [
        'supervisors' => $supervisors,
        'currentSemester' => $currentSemester,
        'canSubmit' => $canSubmit,
        'deadlineWarning' => $deadlineWarning,
        'ideaPhase' => $ideaPhase,
        'currentProjectCount' => $currentProjectCount,
        'maxProjects' => 3,
    ]);
}

/**
 * Store a newly created resource in storage.
 */
public function store(Request $request): RedirectResponse
{
    $user = Auth::user();

    // Check if student can create new project (limit 3, no approved projects)
    if (!$user->canCreateNewProject()) {
        return redirect()
            ->route('projects.index')
            ->with('error', $user->getCannotCreateProjectReason());
    }

    $validated = $request->validate([
        'title' => 'required|string|max: 255',
        'description' => 'required|string',
        'supervisor_id' => 'required|exists:users,id', // Fixed:  removed extra space
    ]);

    // Get current semester and check deadline
    $ideaPhase = FypPhase::where('slug', 'idea_approval')
        ->where('is_active', true)
        ->first();

    $isLate = false;
    $semester = null;

    if ($ideaPhase) {
        $semester = $ideaPhase->semester;

        if ($ideaPhase->isDeadlinePassed()) {
            if (!$ideaPhase->allow_late) {
                return redirect()
                    ->route('projects.create')
                    ->with('error', 'The idea submission deadline has passed.');
            }
            $isLate = true;
        }
    }

    Auth::user()->projects()->create([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'supervisor_id' => $validated['supervisor_id'],
        'status' => 'pending',
        'current_phase' => Project::PHASE_IDEA,
        'semester' => $semester,
        'is_late' => $isLate,
    ]);

    $message = 'Project idea submitted successfully!';
    if ($isLate) {
        $message .= ' (Submitted after deadline - marked as LATE)';
    }

    return redirect()->route('projects.index')->with('success', $message);
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, Project $project): RedirectResponse
{
    if ($project->user_id !== Auth::id()) {
        abort(403);
    }

    if ($project->status !== 'rejected') {
        return redirect()
            ->route('projects.index')
            ->with('error', 'Only rejected projects can be edited.');
    }

    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'supervisor_id' => 'required|exists:users,id', // Fixed: removed extra space
    ]);

    $project->update([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'supervisor_id' => $validated['supervisor_id'],
        'status' => 'pending',
        'rejection_reason' => null,
    ]);

    return redirect()
        ->route('projects.index')
        ->with('success', 'Project resubmitted successfully! ');
}

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     // Check if student already has a project
    //     if (Auth::user()->projects()->exists()) {
    //         return redirect()
    //             ->route('projects.index')
    //             ->with('error', 'You already have a project.  You cannot create another one.');
    //     }

    //     $supervisors = User::where('role', 'supervisor')
    //         ->whereHas('supervisorProfile', function ($query) {
    //             $query->where('available_slots', '>', 0);
    //         })
    //         ->orderBy('name')
    //         ->get();

    //     // Get current active semester
    //     $currentSemester = FypPhase::where('is_active', true)
    //         ->where('start_date', '<=', now())
    //         ->where('end_date', '>=', now())
    //         ->value('semester');

    //     // Check if idea submission phase is active
    //     $ideaPhase = FypPhase::where('slug', 'idea_approval')
    //         ->where('is_active', true)
    //         ->where('start_date', '<=', now())
    //         ->first();

    //     $canSubmit = true;
    //     $deadlineWarning = null;

    //     if ($ideaPhase) {
    //         if ($ideaPhase->isDeadlinePassed()) {
    //             if (!$ideaPhase->allow_late) {
    //                 $canSubmit = false;
    //                 $deadlineWarning = 'The idea submission deadline has passed. You cannot submit a new project. ';
    //             } else {
    //                 $deadlineWarning = 'Warning: The idea submission deadline has passed. Your submission will be marked as LATE.';
    //             }
    //         } elseif ($ideaPhase->days_remaining <= 3) {
    //             $deadlineWarning = 'Warning: Only ' . $ideaPhase->days_remaining . ' day(s) remaining until the deadline! ';
    //         }
    //     }

    //     return view('projects.create', [
    //         'supervisors' => $supervisors,
    //         'currentSemester' => $currentSemester,
    //         'canSubmit' => $canSubmit,
    //         'deadlineWarning' => $deadlineWarning,
    //         'ideaPhase' => $ideaPhase,
    //     ]);
    // }

    /**
     * Store a newly created resource in storage. 
     */
    // public function store(Request $request): RedirectResponse
    // {
    //     // Check if student already has a project
    //     if (Auth::user()->projects()->exists()) {
    //         return redirect()
    //             ->route('projects.index')
    //             ->with('error', 'You already have a project.');
    //     }

    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'supervisor_id' => 'required|exists:users,id',
    //     ]);

    //     // Get current semester and check deadline
    //     $ideaPhase = FypPhase::where('slug', 'idea_approval')
    //         ->where('is_active', true)
    //         ->first();

    //     $isLate = false;
    //     $semester = null;

    //     if ($ideaPhase) {
    //         $semester = $ideaPhase->semester;

    //         if ($ideaPhase->isDeadlinePassed()) {
    //             if (!$ideaPhase->allow_late) {
    //                 return redirect()
    //                     ->route('projects.create')
    //                     ->with('error', 'The idea submission deadline has passed.');
    //             }
    //             $isLate = true;
    //         }
    //     }

    //     Auth::user()->projects()->create([
    //         'title' => $validated['title'],
    //         'description' => $validated['description'],
    //         'supervisor_id' => $validated['supervisor_id'],
    //         'status' => 'pending',
    //         'current_phase' => Project::PHASE_IDEA,
    //         'semester' => $semester,
    //         'is_late' => $isLate,
    //     ]);

    //     $message = 'Project idea submitted successfully!';
    //     if ($isLate) {
    //         $message .= ' (Submitted after deadline - marked as LATE)';
    //     }

    //     return redirect()->route('projects.index')->with('success', $message);
    // }

    /**
     * Show the form for editing the specified resource. 
     */
    public function edit(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Unauthorized Action');
        }

        if ($project->status !== 'rejected') {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Only rejected projects can be edited.');
        }

        $availableSupervisors = User::where('role', 'supervisor')
            ->whereHas('supervisorProfile', function ($query) {
                $query->where('available_slots', '>', 0);
            })
            ->orderBy('name')
            ->get();

        $originalSupervisor = $project->supervisor;

        if ($originalSupervisor && ! $availableSupervisors->contains('id', $originalSupervisor->id)) {
            $availableSupervisors->push($originalSupervisor);
        }

        return view('projects.edit', [
            'project' => $project,
            'supervisors' => $availableSupervisors
        ]);
    }

    /**
     * Update the specified resource in storage. 
     */
    // public function update(Request $request, Project $project): RedirectResponse
    // {
    //     if ($project->user_id !== Auth::id()) {
    //         abort(403);
    //     }

    //     if ($project->status !== 'rejected') {
    //         return redirect()
    //             ->route('projects.index')
    //             ->with('error', 'Only rejected projects can be edited.');
    //     }

    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'supervisor_id' => 'required|exists:users,id',
    //     ]);

    //     $project->update([
    //         'title' => $validated['title'],
    //         'description' => $validated['description'],
    //         'supervisor_id' => $validated['supervisor_id'],
    //         'status' => 'pending',
    //         'rejection_reason' => null,
    //     ]);

    //     return redirect()
    //         ->route('projects.index')
    //         ->with('success', 'Project resubmitted successfully! ');
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Unauthorized Action');
        }

        // Only allow deletion of pending or rejected projects
        if (! in_array($project->status, ['pending', 'rejected'])) {
            return back()->withErrors(['error' => 'Cannot delete an approved or completed project.']);
        }

        // Check for defence sessions
        if ($project->defenceSessions()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete project with scheduled defence sessions.']);
        }

        $projectTitle = $project->title;
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project "' . $projectTitle . '" deleted successfully.');
    }

    /**
     * Show form to upload scope document.
     */
    public function createScopeDocument(Project $project)
    {
        if (Auth::id() !== $project->user_id) {
            abort(403);
        }

        if ($project->status !== 'approved') {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Scope document can only be uploaded for approved projects.');
        }

        // Check scope phase deadline
        $scopePhase = null;
        $canSubmit = true;
        $deadlineWarning = null;

        if ($project->semester) {
            $scopePhase = FypPhase::where('semester', $project->semester)
                ->where('slug', 'scope_approval')
                ->where('is_active', true)
                ->first();

            if ($scopePhase) {
                if ($scopePhase->isDeadlinePassed()) {
                    if (!$scopePhase->allow_late) {
                        $canSubmit = false;
                        $deadlineWarning = 'The scope submission deadline has passed. You cannot upload a new scope document. ';
                    } else {
                        $deadlineWarning = 'Warning:  The scope submission deadline has passed. Your submission will be marked as LATE.';
                    }
                } elseif ($scopePhase->days_remaining <= 3) {
                    $deadlineWarning = 'Warning:  Only ' . $scopePhase->days_remaining . ' day(s) remaining until the deadline!';
                }
            }
        }

        // Get previous versions for reference
        $previousVersions = $project->scopeDocuments()
            ->with(['uploader:id,name', 'reviewer:id,name'])
            ->get();

        // Get latest scope document status
        $latestDocument = $project->latestScopeDocument;

        return view('projects.scope.create', [
            'project' => $project,
            'scopePhase' => $scopePhase,
            'canSubmit' => $canSubmit,
            'deadlineWarning' => $deadlineWarning,
            'previousVersions' => $previousVersions,
            'latestDocument' => $latestDocument,
        ]);
    }

    /**
     * Store a scope document uploaded by a student.
     */
    public function storeScopeDocument(Request $request, Project $project): RedirectResponse
    {
        if (Auth::id() !== $project->user_id) {
            abort(403);
        }

        if ($project->status !== 'approved') {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Scope document can only be uploaded for approved projects.');
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'changelog' => 'nullable|string|max: 1000',
        ]);

        // Check deadline
        $isLate = false;
        if ($project->semester) {
            $scopePhase = FypPhase::where('semester', $project->semester)
                ->where('slug', 'scope_approval')
                ->where('is_active', true)
                ->first();

            if ($scopePhase && $scopePhase->isDeadlinePassed()) {
                if (!$scopePhase->allow_late) {
                    return redirect()
                        ->route('projects.scope.create', $project)
                        ->with('error', 'The scope submission deadline has passed.');
                }
                $isLate = true;
            }
        }

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('scope_documents');

            // Calculate version number
            $versionNumber = $project->scopeDocuments()->count() + 1;

            $project->scopeDocuments()->create([
                'file_path' => $path,
                'user_id' => Auth::id(),
                'version' => 'v' . $versionNumber,
                'changelog' => $request->changelog ??  'Version ' . $versionNumber . ' uploaded.',
                'status' => ScopeDocument::STATUS_PENDING,
            ]);

            // Mark project as late if applicable
            if ($isLate && ! $project->is_late) {
                $project->update(['is_late' => true]);
            }

            // Update project phase if not already in scope phase
            if ($project->isIdeaPhase()) {
                $project->update(['current_phase' => Project::PHASE_SCOPE]);
            }

            $message = 'Scope document uploaded successfully! ';
            if ($isLate) {
                $message .= ' (Submitted after deadline - marked as LATE)';
            }

            return redirect()
                ->route('projects.index')
                ->with('success', $message);
        }

        return redirect()->back()->with('error', 'File upload failed.');
    }

    /**
     * Download any version of a scope document.
     */
    public function downloadScopeDocument(ScopeDocument $scope_document)
    {
        $user = Auth::user();
        $project = $scope_document->project;

        // Authorization:  Allow download for the project's student, supervisor, or any admin
        if ($user->role !== 'admin' && $user->id !== $project->user_id && $user->id !== $project->supervisor_id) {
            abort(403, 'Unauthorized access');
        }

        if (! Storage::exists($scope_document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::download($scope_document->file_path);
    }

    /**
     * Delete a scope document. 
     */
    public function destroyScopeDocument(Project $project, ScopeDocument $scope_document)
    {
        if ($project->user_id !== auth()->id()) {
            return back()->withErrors(['error' => 'You can only delete scope documents from your own projects.']);
        }

        if ($scope_document->project_id !== $project->id) {
            return back()->withErrors(['error' => 'Scope document does not belong to this project.']);
        }

        if ($scope_document->user_id !== auth()->id()) {
            return back()->withErrors(['error' => 'You can only delete scope documents you uploaded.']);
        }

        // Don't allow deletion of approved documents
        if ($scope_document->isApproved()) {
            return back()->withErrors(['error' => 'Cannot delete an approved scope document.']);
        }

        // Delete the file
        if (Storage::exists($scope_document->file_path)) {
            Storage::delete($scope_document->file_path);
        }

        $scope_document->delete();

        return back()->with('success', 'Scope document deleted successfully.');
    }
}