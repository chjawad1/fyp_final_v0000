<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('projects.index') }}" class="mr-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Upload Scope Document') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            {{-- Deadline Warning --}}
            @if(isset($deadlineWarning) && $deadlineWarning)
                <div class="mb-6 p-4 rounded-lg {{ isset($canSubmit) && ! $canSubmit ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-yellow-100 border border-yellow-400 text-yellow-700' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8. 257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-. 213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $deadlineWarning }}</span>
                    </div>
                </div>
            @endif

            {{-- Scope Phase Deadline Info --}}
            @if(isset($scopePhase) && $scopePhase)
                <x-deadline-alert
                    :deadline="$scopePhase->end_date"
                    :daysRemaining="$scopePhase->days_remaining"
                    :isOverdue="$scopePhase->isDeadlinePassed()"
                    :daysOverdue="$scopePhase->days_overdue"
                    :allowLate="$scopePhase->allow_late"
                    phaseName="Scope Submission"
                    class="mb-6"
                />
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    {{-- Project Info --}}
                    <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            Project:  {{ $project->title }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Supervisor: {{ $project->supervisor->name ??  'Not Assigned' }}
                        </p>
                    </div>

                    {{-- Previous Versions (if any) --}}
                    @if(isset($previousVersions) && $previousVersions->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Previous Versions</h4>
                            <div class="space-y-2">
                                @foreach($previousVersions as $version)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $version->version_display }}</span>
                                            <x-scope-status-badge :status="$version->status" size="xs" />
                                        </div>
                                        <div class="flex items-center gap-3 text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">{{ $version->created_at->format('M d, Y') }}</span>
                                            <a href="{{ route('scope.document.download', $version) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Download
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Show feedback for revision required --}}
                                    @if($version->feedback && in_array($version->status, ['revision_required', 'rejected']))
                                        <div class="ml-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 text-sm">
                                            <span class="font-medium text-yellow-800 dark:text-yellow-200">Feedback:</span>
                                            <p class="text-yellow-700 dark:text-yellow-300 mt-1">{{ $version->feedback }}</p>
                                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                                - {{ $version->reviewer->name ?? 'Reviewer' }}, {{ $version->reviewed_at?->format('M d, Y') }}
                                            </p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Cannot Submit Message --}}
                    @if(isset($canSubmit) && !$canSubmit)
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h. 01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Submissions Closed</h3>
                            <p class="text-gray-500 dark:text-gray-400">The deadline has passed and late submissions are not allowed for this phase.</p>
                            <a href="{{ route('projects.index') }}" class="mt-4 inline-block text-indigo-600 dark: text-indigo-400 hover:underline">
                                ← Back to Projects
                            </a>
                        </div>
                    @else
                        {{-- Upload Form --}}
                        <form method="POST" action="{{ route('projects.scope.store', $project) }}" enctype="multipart/form-data">
                            @csrf

                            {{-- Instructions --}}
                            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark: border-blue-800 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Upload Instructions</h4>
                                <ul class="text-sm text-blue-700 dark:text-blue-300 list-disc list-inside space-y-1">
                                    <li>Document must be in PDF, DOC, or DOCX format</li>
                                    <li>Maximum file size: 10MB</li>
                                    <li>Include all required sections as per the scope template</li>
                                    <li>Your document will be reviewed by admin or supervisor</li>
                                </ul>
                            </div>

                            {{-- File Input --}}
                            <div class="mb-4">
                                <label for="document" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Scope Document <span class="text-red-500">*</span>
                                </label>
                                <input type="file" id="document" name="document" required accept=".pdf,.doc,.docx"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus: border-indigo-500 dark:bg-gray-900 dark:text-gray-300">
                                @error('document')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Changelog --}}
                            <div class="mb-6">
                                <label for="changelog" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Version Notes / Changelog
                                </label>
                                <textarea id="changelog" name="changelog" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus: ring-indigo-500 focus:border-indigo-500 dark:bg-gray-900 dark:text-gray-300"
                                          placeholder="Describe what's included or what changes were made in this version... ">{{ old('changelog') }}</textarea>
                                @error('changelog')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Late Submission Warning --}}
                            @if(isset($scopePhase) && $scopePhase && $scopePhase->isDeadlinePassed() && $scopePhase->allow_late)
                                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-medium text-red-800 dark:text-red-200">
                                            This submission will be marked as LATE
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Submit Buttons --}}
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover: bg-gray-300 dark: hover:bg-gray-600 transition">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                    Upload Document
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>