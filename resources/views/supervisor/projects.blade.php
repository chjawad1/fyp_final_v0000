<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Project Approval Dashboard') }}
            </h2>
            <a href="{{ route('supervisor.history') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover: underline">
                View Project History →
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Success</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Error</p>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Pending Ideas Section --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Pending Project Ideas
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark: divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Project</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Phase</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($projects->where('status', 'pending') as $project)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->student->name }}</div>
                                            <div class="text-sm text-gray-500 dark: text-gray-400">{{ $project->student->email }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->title }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ Str::limit($project->description, 60) }}</div>
                                            @if($project->is_late)
                                                <x-late-badge :show="true" size="xs" class="mt-1" />
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-phase-badge :phase="$project->current_phase" size="xs" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-project-status-badge :status="$project->status" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div x-data="{ showRejectModal: false }" class="flex items-center gap-3">
                                                {{-- Approve --}}
                                                <form method="POST" action="{{ route('supervisor.projects.approve', $project) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover: text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                        Approve
                                                    </button>
                                                </form>

                                                {{-- Reject --}}
                                                <button @click="showRejectModal = true" class="text-red-600 hover:text-red-900 dark:text-red-400 dark: hover:text-red-300">
                                                    Reject
                                                </button>

                                                {{-- Reject Modal --}}
                                                <div x-show="showRejectModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click. away="showRejectModal = false">
                                                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-md" @click.stop>
                                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Rejection Feedback</h3>
                                                        <form method="POST" action="{{ route('supervisor.projects.reject', $project) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <textarea name="rejection_reason" rows="4" required
                                                                      class="w-full border-gray-300 dark: border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                                                      placeholder="Please provide feedback for the student... "></textarea>
                                                            <div class="mt-4 flex justify-end gap-3">
                                                                <button type="button" @click="showRejectModal = false" 
                                                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                                                                    Cancel
                                                                </button>
                                                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                                                    Submit Rejection
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark: text-gray-400">
                                            No pending project ideas to review.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Approved Projects (Awaiting Scope / In Progress) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark: text-gray-100 mb-4">
                        Approved Projects
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phase</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scope Document</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($projects->where('status', 'approved') as $project)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->student->name }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->title }}</div>
                                            @if($project->is_late)
                                                <x-late-badge :show="true" size="xs" class="mt-1" />
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-phase-badge :phase="$project->current_phase" size="xs" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($project->latestScopeDocument)
                                                <div class="flex items-center gap-2">
                                                    <x-scope-status-badge :status="$project->latestScopeDocument->status" size="xs" />
                                                    <a href="{{ route('scope.document.download', $project->latestScopeDocument) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900 dark: text-indigo-400 text-sm">
                                                        Download
                                                    </a>
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $project->latestScopeDocument->version_display }} - {{ $project->latestScopeDocument->created_at->format('M d') }}
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Not uploaded yet</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-3">
                                                {{-- Review Scope (if pending) --}}
                                                @if($project->latestScopeDocument && $project->latestScopeDocument->isPending())
                                                    <div x-data="{ showReviewModal: false }">
                                                        <button @click="showReviewModal = true" class="text-yellow-600 hover: text-yellow-900 dark:text-yellow-400">
                                                            Review Scope
                                                        </button>

                                                        {{-- Review Modal --}}
                                                        <div x-show="showReviewModal" x-cloak 
                                                             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" 
                                                             @click.away="showReviewModal = false"
                                                             @keydown.escape. window="showReviewModal = false">
                                                            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-lg mx-4" @click.stop>
                                                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Review Scope Document</h3>
                                                                
                                                                {{-- Document Info --}}
                                                                <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                        <strong>Project:</strong> {{ $project->title }}<br>
                                                                        <strong>Version:</strong> {{ $project->latestScopeDocument->version_display }}<br>
                                                                        <strong>Uploaded:</strong> {{ $project->latestScopeDocument->created_at->format('M d, Y') }}
                                                                    </p>
                                                                    <a href="{{ route('scope.document.download', $project->latestScopeDocument) }}" 
                                                                       class="inline-flex items-center text-indigo-600 dark:text-indigo-400 text-sm mt-2 hover:underline">
                                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                                        </svg>
                                                                        Download to Review
                                                                    </a>
                                                                </div>

                                                                {{-- Action Buttons --}}
                                                                <div class="space-y-4">
                                                                    {{-- Approve Form --}}
                                                                    <form action="{{ route('supervisor.scope-reviews.approve', $project->latestScopeDocument) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center justify-center">
                                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                            </svg>
                                                                            Approve Document
                                                                        </button>
                                                                    </form>

                                                                    {{-- Divider --}}
                                                                    <div class="relative">
                                                                        <div class="absolute inset-0 flex items-center">
                                                                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                                                                        </div>
                                                                        <div class="relative flex justify-center text-sm">
                                                                            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500">or request changes</span>
                                                                        </div>
                                                                    </div>

                                                                    {{-- Revision Form --}}
                                                                    <form action="{{ route('supervisor.scope-reviews.request-revision', $project->latestScopeDocument) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <div class="mb-3">
                                                                            <label for="feedback_{{ $project->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                                                Feedback for Student <span class="text-red-500">*</span>
                                                                            </label>
                                                                            <textarea name="feedback" id="feedback_{{ $project->id }}" rows="3" required
                                                                                      class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                                                                                      placeholder="Explain what changes are needed... "></textarea>
                                                                        </div>
                                                                        <button type="submit" class="w-full px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 flex items-center justify-center">
                                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582 m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                                            </svg>
                                                                            Request Revision
                                                                        </button>
                                                                    </form>
                                                                </div>

                                                                {{-- Close Button --}}
                                                                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 text-right">
                                                                    <button type="button" @click="showReviewModal = false" 
                                                                            class="px-4 py-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Mark Complete --}}
                                                @if($project->latestScopeDocument && $project->latestScopeDocument->isApproved())
                                                    <form action="{{ route('supervisor.projects.complete', $project) }}" method="POST" 
                                                          onsubmit="return confirm('Mark this project as complete? ')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-green-600 hover:text-green-900 dark: text-green-400">
                                                            Mark Complete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No approved projects yet. 
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>