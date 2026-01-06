<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('admin.phases.index') }}" class="mr-4 text-gray-500 hover:text-gray-700 dark: text-gray-400 dark:hover: text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark: text-gray-200 leading-tight">
                    {{ $phase->name }}
                </h2>
                <x-phase-status-badge :status="$phase->status" class="ml-3" />
            </div>
            <a href="{{ route('admin.phases.edit', $phase) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Phase
            </a>
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

            {{-- Phase Details Card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- Semester --}}
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Semester</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $phase->semester }}</div>
                        </div>

                        {{-- Order --}}
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Phase Order</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $phase->order }}</div>
                        </div>

                        {{-- Start Date --}}
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark: text-gray-400">Start Date</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $phase->start_date->format('M d, Y') }}</div>
                        </div>

                        {{-- End Date --}}
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Deadline</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $phase->end_date->format('M d, Y') }}</div>
                            @if($phase->isDeadlinePassed())
                                <span class="text-xs text-red-600">{{ $phase->days_overdue }} days overdue</span>
                            @elseif($phase->days_remaining <= 7)
                                <span class="text-xs text-yellow-600">{{ $phase->days_remaining }} days remaining</span>
                            @endif
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($phase->description)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Description</div>
                            <p class="text-gray-900 dark: text-gray-100">{{ $phase->description }}</p>
                        </div>
                    @endif

                    {{-- Settings --}}
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4">
                        <div class="flex items-center">
                            @if($phase->is_active)
                                <span class="flex items-center text-green-600">
                                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Active
                                </span>
                            @else
                                <span class="flex items-center text-gray-400">
                                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    Inactive
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center">
                            @if($phase->allow_late)
                                <span class="flex items-center text-yellow-600">
                                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2. 828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Late Submissions Allowed
                                </span>
                            @else
                                <span class="flex items-center text-gray-400">
                                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    No Late Submissions
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Created by {{ $phase->creator->name ??  'Unknown' }} on {{ $phase->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Project Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Projects</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $projectStats['total'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm: rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $projectStats['pending'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm: rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved</div>
                    <div class="text-3xl font-bold text-green-600">{{ $projectStats['approved'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm: rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected</div>
                    <div class="text-3xl font-bold text-red-600">{{ $projectStats['rejected'] }}</div>
                </div>
                <div class="bg-white dark: bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Late</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $projectStats['late'] }}</div>
                </div>
            </div>

            {{-- Projects in this Phase --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Projects in this Phase</h3>

                    @if($projects->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark: divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Project</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Supervisor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark: text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark: divide-gray-700">
                                    @foreach($projects as $project)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->title }}</div>
                                                @if($project->is_late)
                                                    <x-late-badge :show="true" size="xs" />
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark: text-gray-400">
                                                {{ $project->student->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $project->supervisor->name ?? 'Not Assigned' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-project-status-badge :status="$project->status" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('admin.projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $projects->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2">No projects in this phase yet. </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>