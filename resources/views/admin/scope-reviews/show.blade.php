<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('admin.scope-reviews.index') }}" class="mr-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Review Scope Document') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Main Content - Document Details --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Current Document Card --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Current Version:  {{ $scopeDocument->version_display }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Uploaded {{ $scopeDocument->created_at->format('M d, Y \a\t h:i A') }}
                                        by {{ $scopeDocument->uploader->name ?? 'Unknown' }}
                                    </p>
                                </div>
                                <x-scope-status-badge :status="$scopeDocument->status" size="md" />
                            </div>

                            {{-- Changelog --}}
                            @if($scopeDocument->changelog)
                                <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Changelog</div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $scopeDocument->changelog }}</p>
                                </div>
                            @endif

                            {{-- Download Button --}}
                            <a href="{{ route('scope.document.download', $scopeDocument) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download Document
                            </a>

                            {{-- Review Info (if reviewed) --}}
                            @if($scopeDocument->isReviewed())
                                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Review Details</h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Reviewed by:</span>
                                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ $scopeDocument->reviewer->name ?? 'Unknown' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark: text-gray-400">Reviewed on:</span>
                                            <span class="ml-2 text-gray-900 dark: text-gray-100">{{ $scopeDocument->reviewed_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    @if($scopeDocument->feedback)
                                        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Feedback</div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $scopeDocument->feedback }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Review Form (if pending) --}}
                    @if($scopeDocument->isPending())
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" id="review-form">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Review Actions</h3>

                                {{-- Approve Form --}}
                                <form action="{{ route('admin.scope-reviews.approve', $scopeDocument) }}" method="POST" class="mb-6">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-3">
                                        <label for="approve_feedback" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Feedback (Optional)
                                        </label>
                                        <textarea name="feedback" id="approve_feedback" rows="2"
                                                  class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                  placeholder="Add any comments for the student..."></textarea>
                                    </div>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Approve Document
                                    </button>
                                </form>

                                <hr class="my-6 border-gray-200 dark:border-gray-700">

                                {{-- Request Revision Form --}}
                                <form action="{{ route('admin.scope-reviews.request-revision', $scopeDocument) }}" method="POST" class="mb-6">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-3">
                                        <label for="revision_feedback" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Revision Feedback <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="feedback" id="revision_feedback" rows="3" required
                                                  class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus: border-indigo-500 focus:ring-indigo-500"
                                                  placeholder="Explain what changes are needed..."></textarea>
                                    </div>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Request Revision
                                    </button>
                                </form>

                                <hr class="my-6 border-gray-200 dark:border-gray-700">

                                {{-- Reject Form --}}
                                <form action="{{ route('admin.scope-reviews.reject', $scopeDocument) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-3">
                                        <label for="reject_feedback" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Rejection Reason <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="feedback" id="reject_feedback" rows="3" required
                                                  class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                  placeholder="Explain why this document is being rejected..."></textarea>
                                    </div>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition" onclick="return confirm('Are you sure you want to reject this document?')">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Reject Document
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Version History --}}
                    <div class="bg-white dark: bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Version History</h3>

                            @if($allVersions->count() > 0)
                                <div class="space-y-4">
                                    @foreach($allVersions as $version)
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $version->id === $scopeDocument->id ? 'ring-2 ring-indigo-500' : '' }}">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $version->version_display }}</span>
                                                        @if($version->id === $scopeDocument->id)
                                                            <span class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 rounded">Current</span>
                                                        @endif
                                                        <x-scope-status-badge :status="$version->status" size="xs" />
                                                    </div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                        Uploaded {{ $version->created_at->format('M d, Y \a\t h:i A') }}
                                                        by {{ $version->uploader->name ??  'Unknown' }}
                                                    </p>
                                                </div>
                                                <a href="{{ route('scope.document.download', $version) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            </div>

                                            @if($version->changelog)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                    <span class="font-medium">Changelog:</span> {{ $version->changelog }}
                                                </p>
                                            @endif

                                            @if($version->isReviewed() && $version->feedback)
                                                <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">Feedback:</span>
                                                    <span class="text-gray-600 dark:text-gray-400">{{ $version->feedback }}</span>
                                                    <span class="text-xs text-gray-500 dark: text-gray-500 ml-2">
                                                        - {{ $version->reviewer->name ?? 'Unknown' }}, {{ $version->reviewed_at->format('M d, Y') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">No version history available.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar - Project Info --}}
                <div class="space-y-6">

                    {{-- Project Details Card --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Project Details</h3>

                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $scopeDocument->project->title ??  'N/A' }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark: text-gray-400">Student</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $scopeDocument->project->student->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500 dark: text-gray-400">{{ $scopeDocument->project->student->email ?? '' }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Supervisor</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $scopeDocument->project->supervisor->name ?? 'Not Assigned' }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Semester</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $scopeDocument->project->semester ?? 'Not Set' }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Phase</div>
                                    <x-phase-badge :phase="$scopeDocument->project->current_phase ??  'idea'" />
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Project Status</div>
                                    <x-project-status-badge :status="$scopeDocument->project->status ?? 'pending'" />
                                </div>

                                @if($scopeDocument->project->is_late ??  false)
                                    <div>
                                        <x-late-badge :show="true" />
                                    </div>
                                @endif
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('admin.projects.show', $scopeDocument->project) }}" class="text-indigo-600 hover:text-indigo-900 dark: text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                                    View Full Project Details →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Document Stats</h3>

                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Total Versions</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $allVersions->count() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Approved Versions</span>
                                    <span class="text-sm font-medium text-green-600">{{ $allVersions->where('status', 'approved')->count() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark: text-gray-400">Rejected Versions</span>
                                    <span class="text-sm font-medium text-red-600">{{ $allVersions->where('status', 'rejected')->count() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Revisions Requested</span>
                                    <span class="text-sm font-medium text-orange-600">{{ $allVersions->where('status', 'revision_required')->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>