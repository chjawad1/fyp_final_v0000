<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
 use App\Http\Requests\Supervisor\RejectProjectRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class SupervisorController extends Controller
{
    /**
     * Display a listing of projects assigned to the supervisor.
     */
    public function index()
    {
        $supervisor = Auth::user();
        
        // Only fetch projects that are 'pending' or 'approved' for the main dashboard
        $projects = Project::where('supervisor_id', $supervisor->id)
                            ->whereIn('status', ['pending', 'approved'])
                            ->with('student')
                            ->get();

        return view('supervisor.projects', ['projects' => $projects]);
    }


    public function history()
    {
        $supervisor = Auth::user();

        $completedProjects = Project::where('supervisor_id', $supervisor->id)
                                    ->where('status', 'completed')
                                    ->with('student')
                                    ->get();

        return view('supervisor.history', ['projects' => $completedProjects]);
    }

    public function complete(Project $project)
    {
        $supervisor = Auth::user();
        if ($project->supervisor_id !== $supervisor->id) {
            abort(403);
        }

        // Mark project as completed
        $project->update(['status' => 'completed']);

        // Increment the supervisor's available slots, as this project is no longer active
        $supervisor->supervisorProfile()->increment('available_slots');

        return redirect()->route('supervisor.projects')->with('success', 'Project marked as complete and moved to history.');
    }

       public function approve(Project $project): RedirectResponse
    {
        $supervisor = Auth::user();

        // Authorization: Ensure the logged-in user is the assigned supervisor
        if ($supervisor->id !== $project->supervisor_id) {
            abort(403, 'You are not authorized to approve this project.');
        }

        // --- NEW LOGIC START ---

        // 1. Get the supervisor's profile
        $profile = $supervisor->supervisorProfile;

        // 2. Check for available slots
        if (!$profile || $profile->available_slots <= 0) {
            return redirect()->route('supervisor.projects')->with('error', 'You have no available slots to approve a new project.');
        }

        // 3. Decrement the slots and save the profile
        $profile->decrement('available_slots');

        // --- NEW LOGIC END ---


        // 4. Update the project status
        $project->status = 'approved';
        $project->save();

        return redirect()->route('supervisor.projects')->with('success', 'Project approved successfully. Your available slots have been updated.');
    }

    /**
     * Reject a project submission.
     */
     public function reject(RejectProjectRequest $request, Project $project): RedirectResponse
    {
        $supervisor = Auth::user();

        // Authorization
        if ($supervisor->id !== $project->supervisor_id) {
            abort(403, 'You are not authorized to reject this project.');
        }

        // Validate that a reason was provided
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        // Update project status and save the reason
        $project->status = 'rejected';
        $project->rejection_reason = $request->rejection_reason;
        $project->save();

        // --- The incorrect increment logic has been REMOVED from here. ---

        return redirect()->route('supervisor.projects')->with('success', 'Project rejected and feedback has been sent to the student.');
    }

    public function editProfile()
    {
        $supervisor = Auth::user();

        // Find the supervisor's profile, or create a new one if it doesn't exist
        $profile = $supervisor->supervisorProfile()->firstOrCreate(
            ['user_id' => $supervisor->id],
            ['available_slots' => 8] // Default value if creating
        );

        return view('supervisor.profile', [
            'user' => $supervisor,
            'profile' => $profile
        ]);
    }

    /**
     * Update the supervisor's profile in storage.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $supervisor = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'research_interests' => 'nullable|string',
        ]);

        // Update the user's name
        $supervisor->update(['name' => $validated['name']]);

        // Update the supervisor's profile
        $supervisor->supervisorProfile()->update([
            'research_interests' => $validated['research_interests'],
        ]);

        return redirect()->route('supervisor.profile.edit')->with('success', 'Profile updated successfully!');
    }

    public function directory(Request $request)
    {
        $q = trim($request->get('q', ''));

        $query = User::where('role', 'supervisor')
            ->with('supervisorProfile');

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $supervisors = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('supervisors.directory', [
            'supervisors' => $supervisors,
            'q' => $q,
        ]);
    }

        /**
     * Approve a scope document (Supervisor)
     */
    public function approveScopeDocument(Request $request, \App\Models\ScopeDocument $scopeDocument)
    {
        // Verify supervisor owns this project
        $project = $scopeDocument->project;
        
        if ($project->supervisor_id !== auth()->id()) {
            abort(403, 'You are not the supervisor of this project.');
        }

        // Check if already reviewed
        if ($scopeDocument->isReviewed() && !$scopeDocument->isPending()) {
            return back()->withErrors(['error' => 'This document has already been reviewed. ']);
        }

        $scopeDocument->approve(auth()->id(), $request->feedback);

        // Update project phase if needed
        if ($project->isScopePhase()) {
            $project->update(['current_phase' => \App\Models\Project::PHASE_DEFENCE]);
        }

        return back()->with('success', 'Scope document approved successfully.');
    }

    /**
     * Request revision for a scope document (Supervisor)
     */
    public function requestScopeRevision(Request $request, \App\Models\ScopeDocument $scopeDocument)
    {
        $request->validate([
            'feedback' => ['required', 'string', 'max: 2000'],
        ]);

        // Verify supervisor owns this project
        $project = $scopeDocument->project;
        
        if ($project->supervisor_id !== auth()->id()) {
            abort(403, 'You are not the supervisor of this project.');
        }

        // Check if already reviewed
        if ($scopeDocument->isReviewed() && !$scopeDocument->isPending()) {
            return back()->withErrors(['error' => 'This document has already been reviewed.']);
        }

        $scopeDocument->requestRevision(auth()->id(), $request->feedback);

        return back()->with('success', 'Revision requested.  Student will be notified.');
    }
}