<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Projects') }}
            </h2>
            @if($projects->isEmpty())
                <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    New Project
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @forelse($projects as $project)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">

                        {{-- Project Header --}}
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center flex-wrap gap-2 mb-2">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $project->title }}</h3>
                                    <x-project-status-badge :status="$project->status" />
                                    <x-phase-badge :phase="$project->current_phase" />
                                    @if($project->is_late)
                                        <x-late-badge :show="true" />
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Supervisor: {{ $project->supervisor->name ??  'Not Assigned' }}
                                    @if($project->semester)
                                        <span class="mx-2">•</span>
                                        Semester: {{ $project->semester }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Deadline Alert --}}
                        @if(isset($project->deadline_info) && $project->deadline_info['has_deadline'])
                            <x-deadline-alert
                                :deadline="$project->deadline_info['deadline']"
                                :daysRemaining="$project->deadline_info['days_remaining']"
                                :isOverdue="$project->deadline_info['is_overdue']"
                                :daysOverdue="$project->deadline_info['days_overdue'] ?? 0"
                                :allowLate="$project->deadline_info['allow_late'] ?? false"
                                :phaseName="ucfirst($project->current_phase) .  ' Phase'"
                                class="mb-4"
                            />
                        @endif

                        {{-- Project Description --}}
                        <div class="mb-4">
                            <p class="text-gray-600 dark:text-gray-400">{{ $project->description }}</p>
                        </div>

                        {{-- Rejection Reason --}}
                        @if($project->status === 'rejected' && $project->rejection_reason)
                            <div class="mt-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 text-sm p-4 rounded-md mb-4">
                                <p class="font-semibold mb-1">Rejection Feedback:</p>
                                <p>{{ $project->rejection_reason }}</p>
                            </div>
                        @endif

                        {{-- Scope Document Section --}}
                        @if($project->status === 'approved')
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Scope Document</h4>

                                @if($project->latestScopeDocument)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $project->latestScopeDocument->version_display }}
                                                    </span>
                                                    <x-scope-status-badge :status="$project->latestScopeDocument->status" size="xs" />
                                                </div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Uploaded {{ $project->latestScopeDocument->created_at->format('M d, Y') }}
                                                </p>

                                                {{-- Show feedback if revision required or rejected --}}
                                                @if($project->latestScopeDocument->feedback && in_array($project->latestScopeDocument->status, ['revision_required', 'rejected']))
                                                    <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded text-sm">
                                                        <span class="font-medium text-yellow-800 dark:text-yellow-200">Feedback:</span>
                                                        <span class="text-yellow-700 dark:text-yellow-300">{{ $project->latestScopeDocument->feedback }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('scope.document.download', $project->latestScopeDocument) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark: hover:text-indigo-300 text-sm font-medium">
                                                    Download
                                                </a>

                                                {{-- Upload new version if pending review or revision required --}}
                                                @if(in_array($project->latestScopeDocument->status, ['revision_required', 'rejected']))
                                                    <a href="{{ route('projects.scope.create', $project) }}" 
                                                       class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 text-sm font-medium">
                                                        Upload New Version
                                                    </a>
                                                @endif

                                                {{-- Delete only if student uploaded and pending --}}
                                                @if($project->latestScopeDocument->user_id === auth()->id() && $project->latestScopeDocument->status === 'pending')
                                                    <form action="{{ route('projects.scope.destroy', [$project, $project->latestScopeDocument]) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 text-sm font-medium"
                                                                onclick="return confirm('Delete this scope document?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Version History Link --}}
                                        @if($project->scopeDocuments->count() > 1)
                                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                                <details class="text-sm">
                                                    <summary class="cursor-pointer text-indigo-600 dark:text-indigo-400 hover:underline">
                                                        View all versions ({{ $project->scopeDocuments->count() }})
                                                    </summary>
                                                    <div class="mt-2 space-y-2">
                                                        @foreach($project->scopeDocuments->skip(1) as $doc)
                                                            <div class="flex items-center justify-between py-2 px-3 bg-white dark:bg-gray-800 rounded">
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-gray-700 dark:text-gray-300">{{ $doc->version_display }}</span>
                                                                    <x-scope-status-badge :status="$doc->status" size="xs" />
                                                                </div>
                                                                <a href="{{ route('scope.document.download', $doc) }}" class="text-indigo-600 dark:text-indigo-400 text-xs">
                                                                    Download
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- No scope document yet --}}
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400 mb-3">No scope document uploaded yet</p>
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
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 dark:border-gray-700 gap-3">
                            @if($project->status === 'rejected')
                                <a href="{{ route('projects.edit', $project) }}" 
                                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    Edit & Resubmit
                                </a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm"
                                            onclick="return confirm('Delete this project?')">
                                        Delete Project
                                    </button>
                                </form>
                            @elseif($project->status === 'pending')
                                <span class="text-sm text-gray-500 dark:text-gray-400">Awaiting supervisor approval... </span>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm"
                                            onclick="return confirm('Delete this project?')">
                                        Delete Project
                                    </button>
                                </form>
                            @elseif($project->status === 'completed' || $project->current_phase === 'completed')
                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">
                                    ✓ Project Completed
                                </span>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                {{-- No Projects --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No projects yet</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by creating your FYP project proposal. </p>
                        <a href="{{ route('projects.create') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create New Project
                        </a>
                    </div>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>