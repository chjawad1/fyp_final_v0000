<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Assigned Sessions</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <!-- Status Messages -->
                    @if (session('success'))
                        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($assignments->isEmpty())
                        <p class="text-gray-500">No scheduled defence sessions assigned to you.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-600">
                                        <th class="py-2 pr-4">Project</th>
                                        <th class="py-2 pr-4">Committee</th>
                                        <th class="py-2 pr-4">Scheduled At</th>
                                        <th class="py-2 pr-4">Session Status</th>
                                        <th class="py-2 pr-4">Evaluation Status</th>
                                        <th class="py-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach ($assignments as $a)
                                        <tr>
                                            <td class="py-2 pr-4">
                                                <div class="font-medium">{{ $a->session->project->title ??  '—' }}</div>
                                            </td>
                                            <td class="py-2 pr-4">{{ $a->session->committee->name }}</td>
                                            <td class="py-2 pr-4">{{ optional($a->session->scheduled_at)->format('M j, Y @ H:i') }}</td>
                                            <td class="py-2 pr-4">
                                                @php
                                                    $sessionStatus = $a->session->status ??  'unknown';
                                                @endphp
                                                @if ($sessionStatus === 'scheduled')
                                                    <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">Scheduled</span>
                                                @elseif ($sessionStatus === 'completed')
                                                    <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Completed</span>
                                                @elseif ($sessionStatus === 'cancelled')
                                                    <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Cancelled</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst($sessionStatus) }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2 pr-4">
                                                @if ($a->submitted_at)
                                                    <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Submitted</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">Pending</span>
                                                @endif
                                            </td>
                                            <td class="py-2">
                                                @if (($a->session->status ??  'unknown') === 'scheduled')
                                                    <a class="text-indigo-600 hover:underline" href="{{ route('member.sessions.evaluate', $a->id) }}">
                                                        {{ $a->submitted_at ?  'View/Edit' : 'Evaluate' }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">Not Available</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $assignments->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>