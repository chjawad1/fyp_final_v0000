<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\DefenceSession;
use App\Models\FypPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of all projects for the administrator.
     */
    public function index(Request $request)
    {
        $query = Project::with(['student', 'supervisor', 'latestScopeDocument']);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by supervisor if provided
        if ($request->filled('supervisor')) {
            $query->where('supervisor_id', $request->supervisor);
        }

        // Filter by phase if provided
        if ($request->filled('phase')) {
            $query->where('current_phase', $request->phase);
        }

        // Filter by semester if provided
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // Filter late submissions
        if ($request->filled('is_late') && $request->is_late === '1') {
            $query->where('is_late', true);
        }

        // Search by title or student name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $projects = $query->latest()->paginate(15)->withQueryString();

        // For filter dropdowns
        $supervisors = User::where('role', 'supervisor')->orderBy('name')->get();
        $semesters = Project::whereNotNull('semester')
                            ->distinct()
                            ->orderBy('semester', 'desc')
                            ->pluck('semester');
        $phases = config('fyp.project_phases');

        // Statistics
        $stats = [
            'total' => Project::count(),
            'pending' => Project::where('status', 'pending')->count(),
            'approved' => Project::where('status', 'approved')->count(),
            'rejected' => Project::where('status', 'rejected')->count(),
            'late' => Project::where('is_late', true)->count(),
        ];

        return view('admin.projects.index', compact('projects', 'supervisors', 'semesters', 'phases', 'stats'));
    }

    /**
     * Display the specified project with all details.
     */
    public function show(Project $project)
    {
        $project->load([
            'student:id,name,email',
            'supervisor:id,name,email',
            'scopeDocuments' => function ($query) {
                $query->with(['uploader:id,name', 'reviewer:id,name'])
                      ->orderBy('created_at', 'desc');
            },
            'defenceSessions' => function ($query) {
                $query->with(['committee:id,name', 'scheduledBy:id,name'])
                      ->orderBy('scheduled_at', 'desc');
            },
            'phaseSubmissions' => function ($query) {
                $query->with(['phase:id,name,slug', 'reviewer:id,name'])
                      ->orderBy('created_at', 'desc');
            },
        ]);

        // Get current phase details
        $currentPhaseDetails = $project->getCurrentPhaseDetails();
        $deadlineInfo = $project->getCurrentPhaseDeadlineInfo();

        // Get all phases for this semester (for timeline)
        $semesterPhases = [];
        if ($project->semester) {
            $semesterPhases = FypPhase::where('semester', $project->semester)
                                      ->orderBy('order')
                                      ->get();
        }

        return view('admin.projects.show', compact(
            'project',
            'currentPhaseDetails',
            'deadlineInfo',
            'semesterPhases'
        ));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $students = User::where('role', 'student')
            ->whereDoesntHave('projects')
            ->orderBy('name')
            ->get();

        $supervisors = User::where('role', 'supervisor')
            ->orderBy('name')
            ->get();

        // Get available semesters
        $semesters = FypPhase::distinct()
                             ->orderBy('semester', 'desc')
                             ->pluck('semester');

        return view('admin.projects.create', compact('students', 'supervisors', 'semesters'));
    }

    /**
     * Store a newly created project in storage. 
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'student_id' => ['required', 'exists:users,id'],
            'supervisor_id' => ['nullable', 'exists: users,id'],
            'status' => ['required', 'in: pending,approved,rejected'],
            'semester' => ['nullable', 'string', 'max: 100'],
        ]);

        // Verify student role
        $student = User::findOrFail($validated['student_id']);
        if ($student->role !== 'student') {
            return back()->withErrors(['student_id' => 'Selected user must be a student.'])->withInput();
        }

        // Verify supervisor role if provided
        if ($validated['supervisor_id']) {
            $supervisor = User::findOrFail($validated['supervisor_id']);
            if ($supervisor->role !== 'supervisor') {
                return back()->withErrors(['supervisor_id' => 'Selected user must be a supervisor.'])->withInput();
            }
        }

        // Check if student already has a project
        if ($student->projects()->exists()) {
            return back()->withErrors(['student_id' => 'Student already has a project assigned.'])->withInput();
        }

        $project = Project::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'user_id' => $validated['student_id'],
            'supervisor_id' => $validated['supervisor_id'],
            'status' => $validated['status'],
            'semester' => $validated['semester'],
            'current_phase' => Project::PHASE_IDEA,
        ]);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $project->load(['student', 'supervisor']);

        $students = User::where('role', 'student')
        ->where(function ($query) use ($project) {
            // Include students without projects OR the current project's student
            $query->whereDoesntHave('projects')
                  ->orWhere('id', $project->user_id);
        })
        ->orderBy('name')
        ->get();

        $supervisors = User::where('role', 'supervisor')
            ->orderBy('name')
            ->get();

        $semesters = FypPhase::distinct()
                             ->orderBy('semester', 'desc')
                             ->pluck('semester');

        $phases = config('fyp.project_phases');

        return view('admin.projects.edit', compact('project','students', 'supervisors', 'semesters', 'phases'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max: 255'],
            'description' => ['required', 'string'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:pending,approved,rejected,completed'],
            'current_phase' => ['required', 'in: idea,scope,defence,completed'],
            'semester' => ['nullable', 'string', 'max:100'],
            'is_late' => ['boolean'],
        ]);

        // Verify supervisor role if provided
        if ($validated['supervisor_id']) {
            $supervisor = User::findOrFail($validated['supervisor_id']);
            if ($supervisor->role !== 'supervisor') {
                return back()->withErrors(['supervisor_id' => 'Selected user must be a supervisor.'])->withInput();
            }
        }

        $validated['is_late'] = $request->boolean('is_late');

        $project->update($validated);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        // Check for defence sessions
        $sessionCount = $project->defenceSessions()->count();
        if ($sessionCount > 0) {
            return back()->withErrors([
                'error' => "Cannot delete project with {$sessionCount} defence session(s)."
            ]);
        }

        $projectTitle = $project->title;
        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project "' . $projectTitle . '" deleted successfully.');
    }

    /**
     * Update project status.
     */
    public function updateStatus(Request $request, Project $project)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,completed'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'rejected') {
            $updateData['rejection_reason'] = $validated['rejection_reason'];
        }

        // If approved and in idea phase, advance to scope phase
        if ($validated['status'] === 'approved' && $project->isIdeaPhase()) {
            $updateData['current_phase'] = Project::PHASE_SCOPE;
        }

        $project->update($updateData);

        return back()->with('success', 'Project status updated to ' . ucfirst($validated['status']) . '.');
    }

    /**
     * Assign semester to project.
     */
    public function assignSemester(Request $request, Project $project)
    {
        $validated = $request->validate([
            'semester' => ['required', 'string', 'max:100'],
        ]);

        $project->update(['semester' => $validated['semester']]);

        return back()->with('success', 'Project assigned to semester ' . $validated['semester'] .  '.');
    }
}