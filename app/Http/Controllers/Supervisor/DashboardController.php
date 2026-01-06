<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $supervisor = $request->user();

        $activeProjects = Project::where('supervisor_id', $supervisor->id)
            ->whereIn('status', ['pending', 'approved'])
            ->with('student')
            ->latest()
            ->take(10)
            ->get();

        $completedCount = Project::where('supervisor_id', $supervisor->id)
            ->where('status', 'completed')
            ->count();

        $availableSlots = optional($supervisor->supervisorProfile)->available_slots;

        return view('supervisor.dashboard', compact('activeProjects', 'completedCount', 'availableSlots'));
    }
}