<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\DefenceSession;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userId = $request->user()->id;
        
        $project = Project::where('user_id', $user->id)->first();
        $templates = DocumentTemplate::all();
        $upcomingSession = DefenceSession::with(['committee:id,name', 'project:id,title'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->whereHas('project', fn($q) => $q->where('user_id', $userId))
            ->orderBy('scheduled_at', 'asc')
            ->first();

        return view('student.dashboard', compact('project', 'templates','upcomingSession'));
    }
}