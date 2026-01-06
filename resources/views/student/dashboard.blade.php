@php
    use App\Models\FypPhase;

    // Get current semester and phase info
    $currentSemester = null;
    $currentPhase = null;
    $semesterPhases = collect();
    $deadlineInfo = null;

    if ($project && $project->semester) {
        $currentSemester = $project->semester;
        $semesterPhases = FypPhase::where('semester', $currentSemester)
            ->orderBy('order')
            ->get();
        $deadlineInfo = $project->getCurrentPhaseDeadlineInfo();
        $currentPhase = $project->getCurrentPhaseDetails();
    } elseif (! $project) {
        // For students without project, get active idea phase
        $currentPhase = FypPhase::where('slug', 'idea_approval')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->first();
        
        if ($currentPhase) {
            $currentSemester = $currentPhase->semester;
            $semesterPhases = FypPhase::where('semester', $currentSemester)
                ->orderBy('order')
                ->get();
        }
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Student Dashboard') }}
            </h2>
            @if($currentSemester)
                <span class="px-3 py-1 text-sm bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full">
                    {{ $currentSemester }}
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm: px-6 lg:px-8 space-y-6">

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
            {{-- Deadline Alert (if applicable) --}}
            @if($currentPhase && ($currentPhase->days_remaining <= 7 || $currentPhase->isDeadlinePassed()))
                <div class="rounded-lg overflow-hidden {{ $currentPhase->isDeadlinePassed() ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' }}">
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 {{ $currentPhase->isDeadlinePassed() ? 'text-red-600' : 'text-yellow-600' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <span class="font-semibold {{ $currentPhase->isDeadlinePassed() ? 'text-red-800 dark:text-red-200' : 'text-yellow-800 dark:text-yellow-200' }}">
                                    {{ $currentPhase->name }} Deadline
                                </span>
                                <span class="{{ $currentPhase->isDeadlinePassed() ? 'text-red-700 dark:text-red-300' : 'text-yellow-700 dark:text-yellow-300' }} ml-2">
                                    {{ $currentPhase->end_date->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                        <div>
                            @if($currentPhase->isDeadlinePassed())
                                @if($currentPhase->allow_late)
                                    <span class="px-3 py-1 bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-200 rounded-full text-sm font-medium">
                                        Late submissions allowed
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-200 rounded-full text-sm font-medium">
                                        Deadline passed
                                    </span>
                                @endif
                            @elseif($currentPhase->days_remaining <= 3)
                                <span class="px-3 py-1 bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-200 rounded-full text-sm font-bold animate-pulse">
                                    {{ $currentPhase->days_remaining }} day(s) left! 
                                </span>
                            @else
                                <span class="px-3 py-1 bg-yellow-200 text-yellow-900 dark:bg-yellow-800 dark:text-yellow-200 rounded-full text-sm font-medium">
                                    {{ $currentPhase->days_remaining }} days remaining
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Progress Timeline (if project exists) --}}
            @if($project && $semesterPhases->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Your FYP Progress</h3>
                        
                        <div class="relative">
                            {{-- Progress Bar --}}
                            <div class="flex items-center justify-between mb-2">
                                @php
                                    $phaseOrder = ['idea' => 1, 'scope' => 2, 'defence' => 3, 'completed' => 4];
                                    $currentOrder = $phaseOrder[$project->current_phase] ?? 1;
                                    $progressPercent = (($currentOrder - 1) / 3) * 100;
                                    if ($project->current_phase === 'completed') $progressPercent = 100;
                                @endphp
                                @foreach($semesterPhases as $index => $phase)
                                    @php
                                        $phaseSlugMap = ['idea_approval' => 'idea', 'scope_approval' => 'scope', 'defence' => 'defence'];
                                        $mappedPhase = $phaseSlugMap[$phase->slug] ??  null;
                                        $isComplete = $mappedPhase && isset($phaseOrder[$mappedPhase]) && $phaseOrder[$mappedPhase] < $currentOrder;
                                        $isCurrent = $project->current_phase === $mappedPhase;
                                    @endphp
                                    <div class="flex flex-col items-center flex-1">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $isComplete ? 'bg-green-500 text-white' : ($isCurrent ? 'bg-indigo-500 text-white ring-4 ring-indigo-200 dark:ring-indigo-800' : 'bg-gray-200 dark:bg-gray-700 text-gray-500') }}">
                                            @if($isComplete)
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        <div class="mt-2 text-center">
                                            <div class="text-xs font-medium {{ $isCurrent ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
                                                {{ $phase->name }}
                                            </div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                                {{ $phase->end_date->format('M d') }}
                                            </div>
                                        </div>
                                    </div>
                                    @if($index < $semesterPhases->count() - 1)
                                        <div class="flex-1 h-1 mx-2 {{ $isComplete ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }} rounded"></div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Project Card --}}
                    <div class="bg-white dark: bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            @if($project)
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Your Project</h3>
                                        <p class="text-sm text-gray-500 dark: text-gray-400">
                                            Submitted {{ $project->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-project-status-badge :status="$project->status" />
                                        <x-phase-badge :phase="$project->current_phase" />
                                        @if($project->is_late)
                                            <x-late-badge :show="true" />
                                        @endif
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</div>
                                        <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $project->title }}</div>
                                    </div>

                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Supervisor</div>
                                        <div class="text-gray-900 dark:text-gray-100">{{ $project->supervisor->name ??  'Not Assigned' }}</div>
                                    </div>

                                    @if($project->status === 'rejected' && $project->rejection_reason)
                                        <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                            <div class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Rejection Feedback</div>
                                            <p class="text-sm text-red-700 dark:text-red-300">{{ $project->rejection_reason }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                                    <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                                        View Project Details
                                    </a>
                                    @if($project->status === 'rejected')
                                        <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 text-sm">
                                            Edit & Resubmit
                                        </a>
                                    @endif
                                </div>
                            @else
                                {{-- No Project --}}
                                <div class="text-center py-6">
                                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Project Yet</h3>
                                    <p class="text-gray-500 dark:text-gray-400 mb-4">Start your FYP journey by creating a project proposal. </p>
                                    
                                    @if($currentPhase && $currentPhase->isDeadlinePassed() && ! $currentPhase->allow_late)
                                        <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg mb-4">
                                            <p class="text-sm text-red-700 dark:text-red-300">
                                                The idea submission deadline has passed. Contact your coordinator for assistance.
                                            </p>
                                        </div>
                                    @else
                                        <a href="{{ route('projects.create') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Create Project Proposal
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Scope Document Card (if project is approved) --}}
                    @if($project && $project->status === 'approved')
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Scope Document</h3>
                                    @if($project->latestScopeDocument)
                                        <x-scope-status-badge :status="$project->latestScopeDocument->status" />
                                    @endif
                                </div>

                                @if($project->latestScopeDocument)
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $project->latestScopeDocument->version_display }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Uploaded {{ $project->latestScopeDocument->created_at->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <a href="{{ route('scope.document.download', $project->latestScopeDocument) }}" 
                                               class="px-3 py-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Download
                                            </a>
                                        </div>

                                        {{-- Feedback if revision required --}}
                                        @if($project->latestScopeDocument->feedback && in_array($project->latestScopeDocument->status, ['revision_required', 'rejected']))
                                            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                                <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-1">Reviewer Feedback</div>
                                                <p class="text-sm text-yellow-700 dark: text-yellow-300">{{ $project->latestScopeDocument->feedback }}</p>
                                                <p class="text-xs text-yellow-600 dark: text-yellow-400 mt-1">
                                                    - {{ $project->latestScopeDocument->reviewer->name ?? 'Reviewer' }}, {{ $project->latestScopeDocument->reviewed_at?->format('M d, Y') }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Action based on status --}}
                                        @if(in_array($project->latestScopeDocument->status, ['revision_required', 'rejected']))
                                            <a href="{{ route('projects.scope.create', $project) }}" 
                                               class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 text-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                </svg>
                                                Upload New Version
                                            </a>
                                        @elseif($project->latestScopeDocument->status === 'pending')
                                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="w-5 h-5 mr-2 text-yellow-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Awaiting review from supervisor/admin
                                            </div>
                                        @elseif($project->latestScopeDocument->status === 'approved')
                                            <div class="flex items-center text-sm text-green-600 dark:text-green-400">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Scope document approved!  Ready for defence phase.
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-6">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400 mb-4">No scope document uploaded yet</p>
                                        <a href="{{ route('projects.scope.create', $project) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            Upload Scope Document
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Upcoming Defence --}}
                    @if($upcomingSession)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming Defence</h3>
                                    <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full text-sm font-medium">
                                        {{ $upcomingSession->scheduled_at->diffForHumans() }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Date & Time</div>
                                        <div class="text-gray-900 dark:text-gray-100 font-medium">
                                            {{ $upcomingSession->scheduled_at->format('l, M d, Y') }}
                                        </div>
                                        <div class="text-gray-600 dark:text-gray-400">
                                            {{ $upcomingSession->scheduled_at->format('h:i A') }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Venue</div>
                                        <div class="text-gray-900 dark:text-gray-100">
                                            {{ $upcomingSession->venue ?: 'To be announced' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Committee</div>
                                        <div class="text-gray-900 dark:text-gray-100">{{ $upcomingSession->committee->name ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Project</div>
                                        <div class="text-gray-900 dark:text-gray-100">{{ $upcomingSession->project->title ??  'N/A' }}</div>
                                    </div>
                                </div>

                                {{-- Countdown --}}
                                @php
                                    $daysUntil = now()->diffInDays($upcomingSession->scheduled_at, false);
                                @endphp
                                @if($daysUntil > 0 && $daysUntil <= 7)
                                    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-yellow-800 dark: text-yellow-200">
                                                {{ $daysUntil }} day(s) until your defence!  Make sure you're prepared.
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">

                    {{-- Quick Stats --}}
                    @if($project)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Project Status</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Idea</span>
                                        @if($project->status === 'approved' || $project->status === 'completed')
                                            <span class="text-green-600 dark:text-green-400">✓ Approved</span>
                                        @elseif($project->status === 'rejected')
                                            <span class="text-red-600 dark:text-red-400">✗ Rejected</span>
                                        @else
                                            <span class="text-yellow-600 dark:text-yellow-400">⏳ Pending</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Scope Document</span>
                                        @if($project->latestScopeDocument)
                                            @if($project->latestScopeDocument->status === 'approved')
                                                <span class="text-green-600 dark:text-green-400">✓ Approved</span>
                                            @elseif($project->latestScopeDocument->status === 'pending')
                                                <span class="text-yellow-600 dark:text-yellow-400">⏳ Pending</span>
                                            @elseif($project->latestScopeDocument->status === 'revision_required')
                                                <span class="text-orange-600 dark:text-orange-400">↻ Revision</span>
                                            @else
                                                <span class="text-red-600 dark:text-red-400">��� Rejected</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">— Not uploaded</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Defence</span>
                                        @if($upcomingSession)
                                            <span class="text-purple-600 dark:text-purple-400">📅 Scheduled</span>
                                        @elseif($project->current_phase === 'completed')
                                            <span class="text-green-600 dark:text-green-400">✓ Completed</span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">— Not scheduled</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Document Templates --}}
                    <div class="bg-white dark: bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Document Templates</h3>
                            
                            @if($templates->count() > 0)
                                <ul class="space-y-2">
                                    @foreach($templates as $template)
                                        <li class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $template->name }}</span>
                                            </div>
                                            <a href="{{ route('templates.download', $template) }}" 
                                               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Download
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">No templates available yet. </p>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Links --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Links</h3>
                            <div class="space-y-2">
                                <a href="{{ route('projects.index') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">My Projects</span>
                                </a>
                                <a href="{{ route('supervisors.directory') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover: bg-gray-100 dark: hover:bg-gray-600 transition">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-. 656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">Supervisors Directory</span>
                                </a>
                                <a href="{{ route('profile.edit') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">My Profile</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Help Card --}}
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-white">
                            <h3 class="font-semibold mb-2">Need Help?</h3>
                            <p class="text-sm text-indigo-100 mb-4">
                                Contact your supervisor or the FYP coordinator if you have any questions about your project.
                            </p>
                            @if($project && $project->supervisor)
                                <div class="text-sm">
                                    <div class="font-medium">Your Supervisor</div>
                                    <div class="text-indigo-100">{{ $project->supervisor->name }}</div>
                                    <div class="text-indigo-200 text-xs">{{ $project->supervisor->email }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>