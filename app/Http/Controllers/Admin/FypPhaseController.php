<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FypPhase;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FypPhaseController extends Controller
{
    /**
     * Display a listing of all phases. 
     */
    public function index(Request $request)
    {
        $query = FypPhase::with('creator:id,name')
            ->withCount('submissions');

        // Filter by semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true)
                          ->where('start_date', '<=', now())
                          ->where('end_date', '>=', now());
                    break;
                case 'upcoming':
                    $query->where('start_date', '>', now());
                    break;
                case 'ended':
                    $query->where('end_date', '<', now());
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        $phases = $query->orderBy('semester', 'desc')
                        ->orderBy('order', 'asc')
                        ->paginate(15)
                        ->withQueryString();

        // Get unique semesters for filter dropdown
        $semesters = FypPhase::distinct()
                             ->orderBy('semester', 'desc')
                             ->pluck('semester');

        // Get statistics
        $stats = [
            'total' => FypPhase::count(),
            'active' => FypPhase::where('is_active', true)
                                ->where('start_date', '<=', now())
                                ->where('end_date', '>=', now())
                                ->count(),
            'upcoming' => FypPhase::where('start_date', '>', now())->count(),
            'ended' => FypPhase::where('end_date', '<', now())->count(),
        ];

        return view('admin.phases.index', compact('phases', 'semesters', 'stats'));
    }

    /**
     * Show the form for creating a new phase.
     */
    public function create()
    {
        $phaseTemplates = config('fyp.phases');
        $existingSemesters = FypPhase::distinct()
                                     ->orderBy('semester', 'desc')
                                     ->pluck('semester');

        return view('admin.phases.create', compact('phaseTemplates', 'existingSemesters'));
    }

    /**
     * Store a newly created phase in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max: 100'],
            'semester' => ['required', 'string', 'max: 100'],
            'order' => ['required', 'integer', 'min:1', 'max:10'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'allow_late' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        // Check for duplicate slug in same semester
        $exists = FypPhase::where('semester', $validated['semester'])
                          ->where('slug', $validated['slug'])
                          ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['slug' => 'This phase already exists for the selected semester.']);
        }

        // Check for overlapping dates in same semester
        $overlapping = FypPhase::where('semester', $validated['semester'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                      ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                      ->orWhere(function ($q) use ($validated) {
                          $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                      });
            })
            ->exists();

        if ($overlapping) {
            return back()
                ->withInput()
                ->withErrors(['start_date' => 'Phase dates overlap with an existing phase in this semester.']);
        }

        $validated['allow_late'] = $request->boolean('allow_late');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = auth()->id();

        $phase = FypPhase::create($validated);

        return redirect()
            ->route('admin.phases.show', $phase)
            ->with('success', 'Phase "' . $phase->name . '" created successfully.');
    }

    /**
     * Display the specified phase.
     */
    public function show(FypPhase $phase)
    {
        $phase->load('creator:id,name');

        // Get projects in this phase (based on semester and current_phase mapping)
        $phaseMapping = [
            'idea_approval' => 'idea',
            'scope_approval' => 'scope',
            'defence' => 'defence',
        ];

        $projectPhase = $phaseMapping[$phase->slug] ?? null;

        $projects = collect();
        $projectStats = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'late' => 0,
        ];

        if ($projectPhase) {
            $projects = Project::with(['student:id,name', 'supervisor:id,name'])
                ->where('semester', $phase->semester)
                ->where('current_phase', $projectPhase)
                ->latest()
                ->paginate(10);

            $projectStats = [
                'total' => Project::where('semester', $phase->semester)
                                  ->where('current_phase', $projectPhase)
                                  ->count(),
                'pending' => Project::where('semester', $phase->semester)
                                    ->where('current_phase', $projectPhase)
                                    ->where('status', 'pending')
                                    ->count(),
                'approved' => Project::where('semester', $phase->semester)
                                     ->where('current_phase', $projectPhase)
                                     ->where('status', 'approved')
                                     ->count(),
                'rejected' => Project::where('semester', $phase->semester)
                                     ->where('current_phase', $projectPhase)
                                     ->where('status', 'rejected')
                                     ->count(),
                'late' => Project::where('semester', $phase->semester)
                                 ->where('current_phase', $projectPhase)
                                 ->where('is_late', true)
                                 ->count(),
            ];
        }

        return view('admin.phases.show', compact('phase', 'projects', 'projectStats'));
    }

    /**
     * Show the form for editing the specified phase.
     */
    public function edit(FypPhase $phase)
    {
        $phaseTemplates = config('fyp.phases');
        $existingSemesters = FypPhase::distinct()
                                     ->orderBy('semester', 'desc')
                                     ->pluck('semester');

        return view('admin.phases.edit', compact('phase', 'phaseTemplates', 'existingSemesters'));
    }

    /**
     * Update the specified phase in storage.
     */
    public function update(Request $request, FypPhase $phase)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max: 255'],
            'slug' => ['required', 'string', 'max:100'],
            'semester' => ['required', 'string', 'max:100'],
            'order' => ['required', 'integer', 'min:1', 'max:10'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'description' => ['nullable', 'string', 'max: 2000'],
            'allow_late' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        // Check for duplicate slug in same semester (excluding current phase)
        $exists = FypPhase::where('semester', $validated['semester'])
                          ->where('slug', $validated['slug'])
                          ->where('id', '!=', $phase->id)
                          ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['slug' => 'This phase already exists for the selected semester.']);
        }

        $validated['allow_late'] = $request->boolean('allow_late');
        $validated['is_active'] = $request->boolean('is_active', true);

        $phase->update($validated);

        return redirect()
            ->route('admin.phases.show', $phase)
            ->with('success', 'Phase "' .  $phase->name .  '" updated successfully.');
    }

    /**
     * Remove the specified phase from storage.
     */
    public function destroy(FypPhase $phase)
    {
        // Check if phase has any submissions
        $submissionCount = $phase->submissions()->count();

        if ($submissionCount > 0) {
            return back()->withErrors([
                'error' => "Cannot delete phase with {$submissionCount} submission(s). Please remove submissions first."
            ]);
        }

        $phaseName = $phase->name;
        $phase->delete();

        return redirect()
            ->route('admin.phases.index')
            ->with('success', 'Phase "' . $phaseName . '" deleted successfully.');
    }

    /**
     * Toggle phase active status.
     */
    public function toggleStatus(FypPhase $phase)
    {
        $phase->update(['is_active' => !$phase->is_active]);

        $status = $phase->is_active ? 'activated' : 'deactivated';

        return back()->with('success', 'Phase "' .  $phase->name .  '" has been ' . $status . '.');
    }

    /**
     * Toggle allow late submissions.
     */
    public function toggleLate(FypPhase $phase)
    {
        $phase->update(['allow_late' => ! $phase->allow_late]);

        $status = $phase->allow_late ? 'enabled' : 'disabled';

        return back()->with('success', 'Late submissions ' . $status . ' for "' . $phase->name . '".');
    }

    /**
     * Extend phase deadline.
     */
    public function extendDeadline(Request $request, FypPhase $phase)
    {
        $validated = $request->validate([
            'new_end_date' => ['required', 'date', 'after: ' . $phase->start_date->format('Y-m-d')],
        ]);

        $oldDate = $phase->end_date->format('M d, Y');
        $phase->update(['end_date' => $validated['new_end_date']]);
        $newDate = $phase->end_date->format('M d, Y');

        return back()->with('success', 'Deadline extended from ' .  $oldDate . ' to ' . $newDate . '.');
    }
}