<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Defence Sessions Management</h2>
            <a href="{{ route('admin.defence-sessions.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Schedule New Session
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Sessions Table with Evaluation Progress -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">ID</th>
                                    <th class="px-4 py-2 text-left">Project</th>
                                    <th class="px-4 py-2 text-left">Committee</th>
                                    <th class="px-4 py-2 text-left">Scheduled At</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    <th class="px-4 py-2 text-left">Evaluation Progress</th>
                                    <th class="px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($sessions as $session)
                                    @php
                                        $progress = $session->evaluation_progress;
                                        $isOverdue = $session->status === 'scheduled' && $session->scheduled_at < now();
                                    @endphp
                                    <tr class="{{ $isOverdue ? 'bg-orange-50' : '' }}">
                                        <td class="px-4 py-2">{{ $session->id }}</td>
                                        <td class="px-4 py-2">
                                            {{ $session->project && $session->project->title ? $session->project->title :  '—' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $session->committee && $session->committee->name ? $session->committee->name : '—' }}
                                        </td>
                                        <td class="px-4 py-2">{{ $session->scheduled_at->format('M j, Y @ H:i') }}</td>
                                        <td class="px-4 py-2">
                                            @if($session->status === 'scheduled')
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                                    Scheduled
                                                </span>
                                                @if($isOverdue)
                                                    <br><span class="text-xs text-orange-600">Overdue</span>
                                                @endif
                                            @elseif($session->status === 'completed')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                                    Completed
                                                </span>
                                            @elseif($session->status === 'cancelled')
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">
                                                    Cancelled
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($progress['total'] > 0)
                                                <div class="flex items-center gap-2">
                                                    <!-- Progress Bar -->
                                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                                        <div class="h-2 rounded-full transition-all duration-300 {{ $progress['is_complete'] ? 'bg-green-500' : 'bg-blue-500' }}" 
                                                             style="width:  {{ $progress['percentage'] }}%"></div>
                                                    </div>
                                                    <!-- Progress Text -->
                                                    <span class="text-xs font-medium {{ $progress['is_complete'] ? 'text-green-600' : 'text-gray-600' }}">
                                                        {{ $progress['completed'] }}/{{ $progress['total'] }}
                                                    </span>
                                                    <!-- Complete Indicator -->
                                                    @if($progress['is_complete'])
                                                        <span class="text-green-500 text-xs">✓ All Done</span>
                                                    @elseif($progress['completed'] > 0)
                                                        <span class="text-blue-500 text-xs">{{ $progress['percentage'] }}%</span>
                                                    @else
                                                        <span class="text-gray-400 text-xs">Pending</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">No evaluators assigned</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex gap-2">
                                                <a href="{{ route('admin.defence-sessions.show', $session) }}" 
                                                   class="text-blue-600 hover:underline text-xs">
                                                    View
                                                </a>
                                                @if($session->status === 'scheduled')
                                                    <form action="{{ route('admin.defence-sessions.update-status', $session) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="text-green-600 hover:underline text-xs"
                                                                onclick="return confirm('Mark this session as completed?')">
                                                            Complete
                                                        </button>
                                                    </form>
                                                    
                                                    @if($progress['completed'] == 0)
                                                        <form action="{{ route('admin.defence-sessions.destroy', $session) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:underline text-xs"
                                                                    onclick="return confirm('Delete this session?')">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $sessions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>