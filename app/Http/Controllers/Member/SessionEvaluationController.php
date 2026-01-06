<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\DefenceSession;
use App\Models\SessionAssignment;
use Illuminate\Http\Request;

class SessionEvaluationController extends Controller
{
    // List sessions assigned to the logged-in user (evaluator)
    public function index(Request $request)
    {
        // First, update any past sessions to completed status
        $this->updatePastSessions();

        $assignments = SessionAssignment::with([
                'session.project:id,title,user_id', // Fixed: specify the fields needed
                'session.committee:id,name'
            ])
            ->where('user_id', $request->user()->id)
            ->whereHas('session', function ($query) {
                $query->where('status', 'scheduled')
                      ->where('scheduled_at', '>=', now()); // Only future sessions
            })
            ->latest()
            ->paginate(10);

        return view('member.sessions.index', compact('assignments'));
    }

    // Show evaluation form
    public function evaluate(SessionAssignment $assignment)
    {
        $this->authorizeView($assignment);

        // Update past sessions before checking
        $this->updatePastSessions();

        // Load the session with relationships
        $assignment->load(['session.project', 'session.committee']);
        $session = $assignment->session;
        
        // Check if the session is still scheduled and not in the past
        if ($session->status !== 'scheduled' || $session->scheduled_at < now()) {
            return redirect()->route('member.sessions.index')
                ->with('error', 'This session is no longer available for evaluation.');
        }

        // Simple default rubric:  3 criteria, 10 marks each
        $rubric = [
            ['key' => 'novelty', 'label' => 'Novelty', 'max' => 10],
            ['key' => 'methodology', 'label' => 'Methodology', 'max' => 10],
            ['key' => 'presentation', 'label' => 'Presentation & Communication', 'max' => 10],
        ];

        return view('member.sessions.evaluate', compact('assignment', 'rubric'));
    }

    // Store/submit evaluation
    public function submit(Request $request, SessionAssignment $assignment)
    {
        $this->authorizeView($assignment);

        $session = $assignment->session()->with('project')->firstOrFail();

        // Update past sessions before validating
        $this->updatePastSessions();

        // Check if the session is still scheduled and not in the past
        if ($session->status !== 'scheduled' || $session->scheduled_at < now()) {
            return back()->withErrors([
                'session' => 'This session is no longer available for evaluation.',
            ])->withInput();
        }

        if (now()->greaterThan($session->scheduled_at->copy()->addDay())) {
            return back()->withErrors([
                'scores. novelty' => 'Submission window has closed (24 hours after the session).',
            ])->withInput();
        }

        $validated = $request->validate([
            'scores.novelty' => ['required', 'integer', 'min:0', 'max:10'],
            'scores.methodology' => ['required', 'integer', 'min:0', 'max: 10'],
            'scores.presentation' => ['required', 'integer', 'min:0', 'max:10'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $scores = $validated['scores'];

        $total = ($scores['novelty'] ??  0) + ($scores['methodology'] ?? 0) + ($scores['presentation'] ?? 0);

        $assignment->update([
            'scores_json'  => $scores,
            'total_score'  => $total,
            'remarks'      => $validated['remarks'] ?? null,
            'submitted_at' => now(),
        ]);

        return redirect()->route('member.sessions.index')->with('success', 'Evaluation submitted.');
    }

    /**
     * Update past scheduled sessions to completed status
     */
    private function updatePastSessions(): void
    {
        DefenceSession::where('status', 'scheduled')
            ->where('scheduled_at', '<', now())
            ->update(['status' => 'completed']);
    }

    protected function authorizeView(SessionAssignment $assignment): void
    {
        abort_unless($assignment->user_id === auth()->id(), 403);
    }
}