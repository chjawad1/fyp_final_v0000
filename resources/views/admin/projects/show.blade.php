<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('admin.projects.index') }}" class="mr-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $project->title }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                <x-phase-badge :phase="$project->current_phase" />
                <x-project-status-badge :status="$project->status" />
                @if($project->is_late)
                    <x-late-badge :show="true" />
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg: px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Deadline Alert --}}
            @if($deadlineInfo['has_deadline'])
                <x-deadline-alert
                    :deadline="$deadlineInfo['deadline']"
                    :daysRemaining="$deadlineInfo['days_remaining']"
                    :isOverdue="$deadlineInfo['is_overdue']"
                    :daysOverdue="$deadlineInfo['days_overdue'] ?? 0"
                    :allowLate="$deadlineInfo['allow_late'] ?? false"
                    :phaseName="$currentPhaseDetails->name ??  'Current Phase'"
                    class="mb-6"
                />
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Project Details --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Project Details</h3>

                            <div class="prose dark:prose-invert max-w-none">
                                <p class="text-gray-600 dark:text-gray-400">{{ $project->description }}</p>
                            </div>

                            @if($project->rejection_reason)
                                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <h4 class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Rejection Reason</h4>
                                    <p class="text-sm text-red-700 dark:text-red-300">{{ $project->rejection_reason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Phase Timeline --}}
                    @if(count($semesterPhases) > 0)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm: rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark: text-gray-100 mb-4">Phase Timeline</h3>

                                <div class="relative">
                                    @foreach($semesterPhases as $index => $phase)
                                        @php
                                            $phaseSlugMap = ['idea_approval' => 'idea', 'scope_approval' => 'scope', 'defence' => 'defence'];
                                            $projectPhase = $phaseSlugMap[$phase->slug] ?? null;
                                            $isCurrent = $project->current_phase === $projectPhase;
                                            $isPast = array_search($project->current_phase, array_values($phaseSlugMap)) > array_search($projectPhase, array_values($phaseSlugMap));
                                        @endphp
                                        <div class="flex items-start mb-4 {{ $index < count($semesterPhases) - 1 ? 'pb-4 border-l-2 border-gray-200 dark:border-gray-700 ml-3' : '' }} {{ $isCurrent ? 'border-indigo-500' : '' }}">
                                            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center -ml-3 {{ $isPast ? 'bg-green-500' : ($isCurrent ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600') }}">
                                                @if($isPast)
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($isCurrent)
                                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="flex items-center">
                                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $phase->name }}</span>
                                                    @if($isCurrent)
                                                        <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 rounded">Current</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Deadline: {{ $phase->end_date->format('M d, Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Scope Documents --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Scope Documents</h3>
                                <a href="{{ route('admin.scope-reviews.index', ['search' => $project->title]) }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                    Review in Scope Reviews →
                                </a>
                            </div>

                            @if($project->scopeDocuments->count() > 0)
                                <div class="space-y-3">
                                    @foreach($project->scopeDocuments as $document)
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $document->version_display }}</span>
                                                        <x-scope-status-badge :status="$document->status" size="xs" />
                                                    </div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                        Uploaded {{ $document->created_at->format('M d, Y') }} by {{ $document->uploader->name ??  'Unknown' }}
                                                    </p>
                                                    @if($document->changelog)
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                            <span class="font-medium">Changelog:</span> {{ $document->changelog }}
                                                        </p>
                                                    @endif
                                                    @if($document->feedback)
                                                        <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm">
                                                            <span class="font-medium">Feedback:</span> {{ $document->feedback }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <a href="{{ route('scope.document.download', $document) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                    </a>
                                                    @if($document->isPending())
                                                        <a href="{{ route('admin.scope-reviews.show', $document) }}" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400">
                                                            Review
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">No scope documents uploaded yet.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Defence Sessions --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Defence Sessions</h3>

                            @if($project->defenceSessions->count() > 0)
                                <div class="space-y-3">
                                    @foreach($project->defenceSessions as $session)
                                        <div class="border border-gray-200 dark: border-gray-700 rounded-lg p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $session->scheduled_at->format('M d, Y \a\t h:i A') }}
                                                    </div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        Committee: {{ $session->committee->name ?? 'N/A' }}
                                                    </p>
                                                    @if($session->venue)
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            Venue: {{ $session->venue }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <span class="px-2 py-1 text-xs font-medium rounded {{ $session->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($session->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">No defence sessions scheduled yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">

                    {{-- Project Info Card --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Project Info</h3>

                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Student</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $project->student->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $project->student->email ?? '' }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Supervisor</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $project->supervisor->name ?? 'Not Assigned' }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Semester</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $project->semester ??  'Not Assigned' }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $project->created_at->format('M d, Y') }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</div>
                                    <div class="text-gray-900 dark: text-gray-100">{{ $project->updated_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Card --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Actions</h3>

                            <div class="space-y-3">
                                <a href="{{ route('admin.projects.edit', $project) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit Project
                                </a>

                                @if($project->status === 'pending')
                                    <form action="{{ route('admin.projects.update-status', $project) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Approve Project
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete Project
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>