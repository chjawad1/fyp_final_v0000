<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('All Projects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Projects List</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Supervisor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scope Document</th> {{-- New Column --}}
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th> {{-- New Column --}}
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($projects as $project)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->student->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->supervisor->name ?? 'Not Assigned' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @switch($project->status)
                                                    @case('approved') bg-green-100 text-green-800 @break
                                                    @case('pending') bg-yellow-100 text-yellow-800 @break
                                                    @case('rejected') bg-red-100 text-red-800 @break
                                                    @default bg-gray-100 text-gray-800
                                                @endswitch">
                                                {{ ucfirst($project->status) }}
                                            </span>
                                        </td>
                                        
                                        {{-- 👇 ADD THIS TABLE CELL FOR THE LINK --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <a href="{{ route('admin.projects.scope-documents.index', $project) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200 font-medium">
                                                Manage Versions
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <a href="{{ route('admin.projects.show', $project) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-medium">
                                               View
                                            </a>
                                            /
                                            {{-- Enhanced delete validation --}}
                                            @php
                                                $hasDefenceSessions = \App\Models\DefenceSession::where('project_id', $project->id)->exists();
                                                $hasScopeDocuments = $project->scopeDocuments()->exists();
                                                $canDelete = !$hasDefenceSessions;
                                                
                                                $deleteTooltip = '';
                                                if ($hasDefenceSessions) {
                                                    $deleteTooltip = 'Cannot delete - has defence sessions';
                                                } elseif ($project->status === 'completed') {
                                                    $deleteTooltip = 'Cannot delete - project completed';
                                                }
                                            @endphp
                                            
                                            @if($canDelete && $project->status !== 'completed')
                                                <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200"
                                                            onclick="return confirm('⚠️ DELETE PROJECT CONFIRMATION ⚠️\n\nProject: {{ $project->title }}\nStudent: {{ $project->student->name }}\nStatus: {{ ucfirst($project->status) }}\n{{ $hasScopeDocuments ? "⚠️ This project has scope documents that will also be deleted." : "" }}\n\nThis action is PERMANENT and cannot be undone.\n\nContinue with deletion?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 cursor-not-allowed" title="{{ $deleteTooltip }}">
                                                    Delete
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                            No projects found.
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