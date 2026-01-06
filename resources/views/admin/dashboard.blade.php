@php
    use App\Models\FypPhase;
    use App\Models\Project;
    use App\Models\ScopeDocument;
    use App\Models\User;
    use App\Models\DefenceSession;

    // Get current semester (most recent active)
    $currentSemester = FypPhase::where('is_active', true)
        ->orderBy('created_at', 'desc')
        ->value('semester');

    // Phase stats for current semester
    $phaseStats = [];
    if ($currentSemester) {
        $phases = FypPhase::where('semester', $currentSemester)
            ->orderBy('order')
            ->get();

        foreach ($phases as $phase) {
            $phaseStats[] = [
                'phase' => $phase,
                'status' => $phase->status,
                'days_remaining' => $phase->days_remaining,
                'is_overdue' => $phase->isDeadlinePassed(),
            ];
        }
    }

    // General stats
    $stats = [
        'total_projects' => Project::count(),
        'pending_projects' => Project::where('status', 'pending')->count(),
        'approved_projects' => Project::where('status', 'approved')->count(),
        'pending_scope_reviews' => ScopeDocument::where('status', 'pending')->count(),
        'total_students' => User::where('role', 'student')->count(),
        'total_supervisors' => User::where('role', 'supervisor')->count(),
        'scheduled_defences' => DefenceSession::where('status', 'scheduled')->where('scheduled_at', '>', now())->count(),
        'late_submissions' => Project::where('is_late', true)->count(),
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Current Semester & Phases Overview --}}
            @if($currentSemester && count($phaseStats) > 0)
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Current Semester: {{ $currentSemester }}
                        </h3>
                        <a href="{{ route('admin.phases.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                            Manage Phases →
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($phaseStats as $phaseStat)
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 {{ $phaseStat['status'] === 'active' ? 'ring-2 ring-green-500' : '' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $phaseStat['phase']->name }}</h4>
                                    <x-phase-status-badge :status="$phaseStat['status']" size="xs" />
                                </div>
                                <div class="text-sm text-gray-500 dark: text-gray-400 mb-2">
                                    Deadline: {{ $phaseStat['phase']->end_date->format('M d, Y') }}
                                </div>
                                @if($phaseStat['is_overdue'])
                                    <div class="text-sm text-red-600 font-medium">
                                        {{ $phaseStat['phase']->days_overdue }} days overdue
                                        @if($phaseStat['phase']->allow_late)
                                            <span class="text-yellow-600">(Late OK)</span>
                                        @endif
                                    </div>
                                @elseif($phaseStat['days_remaining'] <= 7 && $phaseStat['days_remaining'] > 0)
                                    <div class="text-sm text-yellow-600 font-medium">
                                        {{ $phaseStat['days_remaining'] }} days remaining
                                    </div>
                                @elseif($phaseStat['status'] === 'active')
                                    <div class="text-sm text-green-600 font-medium">
                                        {{ $phaseStat['days_remaining'] }} days remaining
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-yellow-800 dark:text-yellow-200 font-medium">No phases configured for current semester. </span>
                        <a href="{{ route('admin.phases.create') }}" class="ml-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium">
                            Create Phase →
                        </a>
                    </div>
                </div>
            @endif

            {{-- Quick Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                {{-- Pending Scope Reviews (Highlighted) --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 {{ $stats['pending_scope_reviews'] > 0 ? 'ring-2 ring-yellow-500' : '' }}">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Scope Reviews</div>
                            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_scope_reviews'] }}</div>
                        </div>
                    </div>
                    @if($stats['pending_scope_reviews'] > 0)
                        <a href="{{ route('admin.scope-reviews.index', ['status' => 'pending']) }}" class="mt-3 block text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                            Review Now →
                        </a>
                    @endif
                </div>

                {{-- Pending Projects --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Projects</div>
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['pending_projects'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- Approved Projects --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved Projects</div>
                            <div class="text-2xl font-bold text-green-600">{{ $stats['approved_projects'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- Late Submissions --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Late Submissions</div>
                            <div class="text-2xl font-bold text-red-600">{{ $stats['late_submissions'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('admin.phases.create') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-gray-100">Create Phase</span>
                        </a>

                        <a href="{{ route('admin.scope-reviews.index') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-gray-100">Review Scopes</span>
                        </a>

                        <a href="{{ route('admin.projects.index') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-gray-100">Manage Projects</span>
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-gray-100">Manage Users</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- More Stats Row --}}
            <div class="grid grid-cols-1 md: grid-cols-3 gap-4 mt-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Projects</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_projects'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm: rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Students</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_students'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm: rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Scheduled Defences</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['scheduled_defences'] }}</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>