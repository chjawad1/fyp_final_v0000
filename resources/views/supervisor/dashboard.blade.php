@php
    use App\Models\FypPhase;
    use App\Models\ScopeDocument;

    // Get current semester
    $currentSemester = FypPhase::where('is_active', true)
        ->orderBy('created_at', 'desc')
        ->value('semester');

    // Get pending scope reviews for this supervisor
    $pendingScopeReviews = ScopeDocument::where('status', 'pending')
        ->whereHas('project', function ($query) {
            $query->where('supervisor_id', auth()->id());
        })
        ->with(['project:id,title,user_id', 'project.student:id,name'])
        ->latest()
        ->take(5)
        ->get();

    $pendingScopeCount = ScopeDocument::where('status', 'pending')
        ->whereHas('project', function ($query) {
            $query->where('supervisor_id', auth()->id());
        })
        ->count();

    // Get current phase info
    $currentPhase = null;
    if ($currentSemester) {
        $currentPhase = FypPhase::where('semester', $currentSemester)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    // Project statistics
    $projectStats = [
        'pending_ideas' => $activeProjects->where('status', 'pending')->count(),
        'approved' => $activeProjects->where('status', 'approved')->count(),
        'in_scope_phase' => $activeProjects->where('current_phase', 'scope')->count(),
        'in_defence_phase' => $activeProjects->where('current_phase', 'defence')->count(),
        'late_submissions' => $activeProjects->where('is_late', true)->count(),
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark: text-gray-200 leading-tight">
                {{ __('Supervisor Dashboard') }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if($currentSemester)
                    <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full">
                        {{ $currentSemester }}
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Current Phase Alert --}}
            @if($currentPhase)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm: rounded-lg">
                    <div class="p-4 flex items-center justify-between {{ $currentPhase->days_remaining <= 7 ? 'bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500' : 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' }}">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 {{ $currentPhase->days_remaining <= 7 ? 'text-yellow-600' : 'text-blue-600' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $currentPhase->name }}</span>
                                <span class="text-gray-600 dark:text-gray-400 ml-2">
                                    Deadline: {{ $currentPhase->end_date->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($currentPhase->days_remaining <= 0)
                                <span class="px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full text-sm font-medium">
                                    Deadline Passed
                                </span>
                            @elseif($currentPhase->days_remaining <= 3)
                                <span class="px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full text-sm font-medium">
                                    {{ $currentPhase->days_remaining }} day(s) left! 
                                </span>
                            @elseif($currentPhase->days_remaining <= 7)
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full text-sm font-medium">
                                    {{ $currentPhase->days_remaining }} days remaining
                                </span>
                            @else
                                <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-sm font-medium">
                                    {{ $currentPhase->days_remaining }} days remaining
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                {{-- Available Slots --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $availableSlots ?? '—' }}</div>
                            <div class="text-xs text-gray-500 dark: text-gray-400">Available Slots</div>
                        </div>
                    </div>
                </div>

                {{-- Pending Ideas --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 {{ $projectStats['pending_ideas'] > 0 ? 'ring-2 ring-yellow-400' : '' }}">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600">{{ $projectStats['pending_ideas'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Pending Ideas</div>
                        </div>
                    </div>
                </div>

                {{-- Pending Scope Reviews --}}
                <div class="bg-white dark: bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 {{ $pendingScopeCount > 0 ? 'ring-2 ring-orange-400' : '' }}">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-orange-600">{{ $pendingScopeCount }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Scope Reviews</div>
                        </div>
                    </div>
                </div>

                {{-- Active Projects --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">{{ $projectStats['approved'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Approved</div>
                        </div>
                    </div>
                </div>

                {{-- Completed --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600">{{ $completedCount }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Completed</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Pending Scope Reviews --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Pending Scope Reviews
                            </h3>
                            @if($pendingScopeCount > 0)
                                <span class="px-2 py-1 text-xs font-bold bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 rounded-full">
                                    {{ $pendingScopeCount }} pending
                                </span>
                            @endif
                        </div>

                        @if($pendingScopeReviews->count() > 0)
                            <div class="space-y-3">
                                @foreach($pendingScopeReviews as $doc)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ $doc->project->title ??  'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $doc->project->student->name ?? 'N/A' }} • {{ $doc->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                        <a href="{{ route('supervisor.projects') }}" 
                                           class="ml-3 px-3 py-1 text-xs font-medium text-white bg-orange-500 rounded hover:bg-orange-600">
                                            Review
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p>No pending scope reviews</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Pending Project Ideas --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Pending Project Ideas
                            </h3>
                            @if($projectStats['pending_ideas'] > 0)
                                <span class="px-2 py-1 text-xs font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full">
                                    {{ $projectStats['pending_ideas'] }} awaiting
                                </span>
                            @endif
                        </div>

                        @php
                            $pendingProjects = $activeProjects->where('status', 'pending')->take(5);
                        @endphp

                        @if($pendingProjects->count() > 0)
                            <div class="space-y-3">
                                @foreach($pendingProjects as $project)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900 dark: text-gray-100 truncate">
                                                    {{ $project->title }}
                                                </span>
                                                @if($project->is_late)
                                                    <x-late-badge :show="true" size="xs" />
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $project->student->name ?? 'N/A' }} • {{ $project->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                        <a href="{{ route('supervisor.projects') }}" 
                                           class="ml-3 px-3 py-1 text-xs font-medium text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                            Review
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            @if($projectStats['pending_ideas'] > 5)
                                <div class="mt-3 text-center">
                                    <a href="{{ route('supervisor.projects') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        View all {{ $projectStats['pending_ideas'] }} pending →
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p>No pending project ideas</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Active Projects Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Active Projects</h3>
                        <a href="{{ route('supervisor.projects') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                            View All →
                        </a>
                    </div>

                    @if($activeProjects->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Project</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Student</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Phase</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Scope</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($activeProjects->take(10) as $project)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ Str::limit($project->title, 40) }}</div>
                                                @if($project->is_late)
                                                    <x-late-badge :show="true" size="xs" />
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $project->student->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <x-phase-badge :phase="$project->current_phase" size="xs" />
                                            </td>
                                            <td class="px-4 py-3">
                                                <x-project-status-badge :status="$project->status" size="xs" />
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($project->latestScopeDocument)
                                                    <x-scope-status-badge :status="$project->latestScopeDocument->status" size="xs" />
                                                @else
                                                    <span class="text-xs text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p>No active projects assigned to you. </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('supervisor.projects') }}" 
                           class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <div>
                                <span class="font-medium text-gray-900 dark:text-gray-100 block">Review Projects</span>
                                <span class="text-xs text-gray-500 dark: text-gray-400">Approve or reject</span>
                            </div>
                        </a>

                        <a href="{{ route('supervisor.history') }}" 
                           class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <span class="font-medium text-gray-900 dark:text-gray-100 block">View History</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Completed projects</span>
                            </div>
                        </a>

                        <a href="{{ route('supervisor.profile.edit') }}" 
                           class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div>
                                <span class="font-medium text-gray-900 dark:text-gray-100 block">My Profile</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Update details</span>
                            </div>
                        </a>

                        <a href="{{ route('supervisors.directory') }}" 
                           class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <div>
                                <span class="font-medium text-gray-900 dark:text-gray-100 block">Supervisors</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">View directory</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Footer Stats --}}
            <div class="grid grid-cols-2 md: grid-cols-4 gap-4 text-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $projectStats['in_scope_phase'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">In Scope Phase</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $projectStats['in_defence_phase'] }}</div>
                    <div class="text-xs text-gray-500 dark: text-gray-400">In Defence Phase</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-red-600">{{ $projectStats['late_submissions'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Late Submissions</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activeProjects->count() }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Active</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>