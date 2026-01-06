<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\DefenceSession;
use App\Models\Project;
use App\Models\SessionAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Support\ConsecutiveSessions;
use \Illuminate\Support\Facades\DB;


class DefenceSessionController extends Controller
{
    public function index()
    {
        $sessions = DefenceSession::with(['committee:id,name', 'project:id,title', 'scheduledBy:id,name'])
            ->latest('scheduled_at')->paginate(10);

        return view('admin.defence-sessions.index', compact('sessions'));
    }

    public function create()
    {
        $committees = Committee::orderBy('name')->get(['id','name']);
        $projects   = Project::where('status', 'approved')
            ->orderBy('created_at','desc')
            ->get(['id','title']);
        $projects = Project::where('status', 'approved')
            ->whereDoesntHave('defenceSessions', function ($q) {
                $q->where('status', 'scheduled')->where('scheduled_at', '>', now());
            })
            ->orderBy('created_at','desc')->get(['id','title']);

        return view('admin.defence-sessions.create', compact('committees', 'projects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'committee_id'  => ['required', 'exists:committees,id'],
            'project_id'    => ['required', 'exists:projects,id'],
            // Must be at least 7 days in advance
            'scheduled_at'  => ['required', 'date', function ($attr, $value, $fail) {
                $dt = Carbon::parse($value);
                if ($dt->lt(now()->addDays(7))) {
                    $fail('The defence must be scheduled at least 7 days in advance.');
                }
            }],
            'venue'         => ['nullable', 'string', 'max:255'],
        ]);

        // Project must be approved
        $project = Project::findOrFail($data['project_id']);
        if (($project->status ?? null) !== 'approved') {
            return back()
                ->withErrors(['project_id' => 'Project is not approved for defence scheduling.'])
                ->withInput();
        }

        // Committee must exist and have at least 2 members
        $committee = \App\Models\Committee::with(['members:id,name'])
            ->withCount('members')
            ->findOrFail($data['committee_id']);

        if ($committee->members_count < 2) {
            return back()
                ->withErrors(['committee_id' => 'A committee must have at least 2 members before scheduling.'])
                ->withInput();
        }

        $memberIds   = $committee->members->pluck('id')->all();
        $scheduledAt = Carbon::parse($data['scheduled_at']);

        // 1) Exact same-time conflicts for any committee member (status != cancelled)
        // Use a DB join to avoid relying on the SessionAssignment relation name
        $conflictingUserIds = DB::table('session_assignments')
            ->join('defence_sessions', 'defence_sessions.id', '=', 'session_assignments.defence_session_id')
            ->whereIn('session_assignments.user_id', $memberIds)
            ->where('defence_sessions.status', '!=', 'cancelled')
            ->where('defence_sessions.scheduled_at', $scheduledAt)
            ->pluck('session_assignments.user_id')
            ->unique()
            ->values()
            ->all();

        // 2) Consecutive run limit per evaluator
        $offenders = [];
        foreach ($memberIds as $uid) {
            if (ConsecutiveSessions::wouldExceedLimit($uid, $scheduledAt)) {
                $offenders[] = $uid;
            }
        }

        if ($conflictingUserIds || $offenders) {
            $messages = [];

            if ($conflictingUserIds) {
                $namesConf = User::whereIn('id', $conflictingUserIds)
                    ->pluck('name')->implode(', ');
                $messages[] = "Time conflict: already booked at this time: {$namesConf}.";
            }

            if ($offenders) {
                $limit    = config('defence.consecutive_limit');
                $namesOff = User::whereIn('id', $offenders)
                    ->pluck('name')->implode(', ');
                $messages[] = "Consecutive sessions limit ({$limit}) would be exceeded for: {$namesOff}.";
            }

            // Surface both issues on the scheduled_at field to keep UI simple
            return back()->withErrors([
                'scheduled_at' => implode(' ', $messages),
            ])->withInput();
        }

        // All good: create the session
        $session = DefenceSession::create($data + [
            'status'           => 'scheduled',
            'scheduled_by_id'  => $request->user()->id,
        ]);

        // Assign all committee members as evaluators for this session
        foreach ($memberIds as $uid) {
            SessionAssignment::firstOrCreate([
                'defence_session_id' => $session->id,
                'user_id'            => $uid,
            ]);
        }

        return redirect()
            ->route('admin.defence-sessions.index')
            ->with('success', 'Defence session scheduled.');
    }

    // public function show(DefenceSession $defenceSession)
    // {
    //     $defenceSession->load([
    //         'committee.members:id,name,email',
    //         'project:id,title,student_id',
    //         'project.student:id,name,email',
    //         'assignments.evaluator:id,name,email',
    //     ]);

    //     return view('admin.defence-sessions.show', ['session' => $defenceSession]);
    // }

    public function show(DefenceSession $defenceSession)
    {
        $defenceSession->load([
            'committee.members:id,name,email',
            'project.student:id,name,email',
            'assignments.evaluator:id,name,email',
        ]);

        return view('admin.defence-sessions.show', ['session' => $defenceSession]);
    }

    public function assignEvaluators(Request $request, DefenceSession $defenceSession)
    {
        $data = $request->validate([
            'evaluator_ids' => ['array'],
            'evaluator_ids.*' => ['exists:users,id'],
        ]);

        $allowed = $defenceSession->committee->members()->pluck('users.id')->all();
        $ids = array_values(array_intersect($data['evaluator_ids'] ?? [], $allowed));

        if (count($ids) < 2) {
            return back()->withErrors(['evaluator_ids' => 'At least 2 evaluators are required.'])->withInput();
        }

        $scheduledAt = $defenceSession->scheduled_at instanceof Carbon
            ? $defenceSession->scheduled_at
            : Carbon::parse($defenceSession->scheduled_at);

        $offenders = [];
        foreach ($ids as $uid) {
            if (ConsecutiveSessions::wouldExceedLimit($uid, $scheduledAt)) {
                $offenders[] = $uid;
            }
        }

        if (!empty($offenders)) {
            $names = \App\Models\User::whereIn('id', $offenders)->pluck('name')->implode(', ');
            return back()->withErrors([
                'evaluator_ids' => "Consecutive sessions limit (".config('defence.consecutive_limit').") would be exceeded for: {$names}."
            ])->withInput();
        }
        // conflict: same time, non-cancelled
        $conflict = SessionAssignment::whereIn('user_id', $ids)
            ->where('defence_session_id', '!=', $defenceSession->id)
            ->whereHas('session', function ($q) use ($defenceSession) {
                $q->where('scheduled_at', $defenceSession->scheduled_at)
                ->where('status', '!=', 'cancelled');
            })->exists();

        if ($conflict) {
            return back()->withErrors(['evaluator_ids' => 'One or more selected evaluators are already booked at this time.'])->withInput();
        }

        // Sync assignments
        $defenceSession->assignments()->whereNotIn('user_id', $ids)->delete();
        foreach ($ids as $uid) {
            SessionAssignment::firstOrCreate([
                'defence_session_id' => $defenceSession->id,
                'user_id' => $uid,
            ]);
        }

        return back()->with('success', 'Evaluators updated.');
    }

    public function updateStatus(Request $request, DefenceSession $defenceSession)
    {
        $data = $request->validate([
            'status' => ['required', 'in:scheduled,completed,cancelled'],
        ]);

        $defenceSession->update($data);

        return back()->with('success', 'Session status updated.');
    }

    public function destroy(DefenceSession $defenceSession)
    {
        $defenceSession->delete();
        return redirect()->route('admin.defence-sessions.index')->with('success', 'Session deleted.');
    }
}