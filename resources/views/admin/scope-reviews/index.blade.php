<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark: text-gray-200 leading-tight">
            {{ __('Scope Document Reviews') }}
        </h2>
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

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Review</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Revision Required</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $stats['revision_required'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.scope-reviews.index') }}" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark: text-gray-300 mb-1">Status</label>
                            <select name="status" id="status" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="revision_required" {{ request('status') === 'revision_required' ? 'selected' : '' }}>Revision Required</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label for="semester" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Semester</label>
                            <select name="semester" id="semester" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Semesters</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester }}" {{ request('semester') === $semester ? 'selected' : '' }}>
                                        {{ $semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus: ring-indigo-500"
                                   placeholder="Project title or student name... ">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md hover:bg-gray-700 dark:hover:bg-gray-300 transition">
                                Filter
                            </button>
                            <a href="{{ route('admin.scope-reviews.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Scope Documents Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($scopeDocuments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Project</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Version</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Uploaded</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark: divide-gray-700">
                                    @foreach($scopeDocuments as $document)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $document->isPending() ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $document->project->title ??  'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark: text-gray-400">
                                                    {{ $document->project->semester ?? 'No semester' }}
                                                </div>
                                                @if($document->project->is_late ??  false)
                                                    <x-late-badge :show="true" size="xs" class="mt-1" />
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $document->project->student->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $document->project->student->email ?? '' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                    {{ $document->version_display }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $document->created_at->format('M d, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $document->created_at->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-scope-status-badge :status="$document->status" />
                                                @if($document->isReviewed())
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        by {{ $document->reviewer->name ??  'Unknown' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-3">
                                                    {{-- View Details --}}
                                                    <a href="{{ route('admin.scope-reviews.show', $document) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark: hover:text-indigo-300" title="View Details">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </a>

                                                    {{-- Download --}}
                                                    <a href="{{ route('scope.document.download', $document) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200" title="Download">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                    </a>

                                                    @if($document->isPending())
                                                        {{-- Quick Approve --}}
                                                        <form action="{{ route('admin.scope-reviews.approve', $document) }}" method="POST" class="inline" onsubmit="return confirm('Approve this scope document?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Approve">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                            </button>
                                                        </form>

                                                        {{-- Quick Reject (opens modal or goes to detail page) --}}
                                                        <a href="{{ route('admin.scope-reviews.show', $document) }}#review-form" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Review/Reject">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4">
                            {{ $scopeDocuments->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No scope documents found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if(request()->hasAny(['status', 'semester', 'search']))
                                    Try adjusting your filters. 
                                @else
                                    Scope documents will appear here when students upload them.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>