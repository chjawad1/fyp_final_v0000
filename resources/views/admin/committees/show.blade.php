<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $committee->name }}</h2>
            <div class="flex items-center gap-4">
                <a class="text-sm text-indigo-600" href="{{ route('admin.committees.edit', $committee) }}">Edit</a>
                <a class="text-sm text-gray-700 hover:text-gray-900" href="{{ route('admin.evaluators.index') }}">Evaluator Directory</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="font-medium text-gray-900">Members (Supervisors only)</h3>

                <form class="mt-4 grid gap-3 sm:grid-cols-3" method="POST" action="{{ route('admin.committees.members.add', $committee) }}">
                    @csrf

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Available Evaluators</label>
                        <select name="evaluator_id" class="mt-1 w-full border-gray-300 rounded" required>
                            <option value="">— Select supervisor —</option>
                            @foreach ($availableEvaluators as $e)
                                <option value="{{ $e->id }}">{{ $e->user->name }} ({{ $e->user->email }})</option>
                            @endforeach
                        </select>
                        @error('evaluator_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Committee Role</label>
                        <select name="role" class="mt-1 w-full border-gray-300 rounded" required>
                            <option value="member">Member</option>
                            <option value="chair">Chair</option>
                        </select>
                        @error('role')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-3">
                        <button class="px-3 py-1.5 bg-indigo-600 text-white rounded text-sm">Add</button>
                    </div>
                </form>

                <div class="mt-4 divide-y">
                    @forelse ($committee->members as $m)
                        <div class="py-2 flex items-center justify-between">
                            <div>
                                <div class="font-medium">{{ $m->name }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $m->email }} — {{ ucfirst($m->pivot->role) }}
                                    @if ($m->evaluator)
                                        <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-gray-100">Evaluator (Supervisor)</span>
                                        <span class="ml-2 text-xs px-2 py-0.5 rounded-full {{ $m->evaluator->status === 'assigned' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                            {{ ucfirst($m->evaluator->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.committees.members.remove', [$committee, $m]) }}" onsubmit="return confirm('Remove member from committee?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                            </form>
                        </div>
                    @if ($committee->members->count() < 2)
                        <div class="mb-4 p-3 rounded bg-yellow-50 text-yellow-800 text-sm">
                            This committee has fewer than 2 members. You cannot schedule a defence with this committee until you add more members.
                        </div>
                    @endif
                    @empty
                        <p class="text-gray-500 mt-2">No members yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- existing scheduling card remains unchanged --}}
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-gray-900">Schedule Defence Session</h3>
                    <a class="text-sm text-indigo-600" href="{{ route('admin.defence-sessions.index') }}">All Sessions</a>
                </div>

                {{-- Flash + errors --}}
                @if (session('success'))
                    <div class="mt-3 rounded bg-green-50 px-3 py-2 text-green-800 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mt-3 rounded bg-red-50 px-3 py-2 text-red-800 text-sm">
                        <div class="font-medium">Please correct the errors below:</div>
                        <ul class="mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="mt-4 grid gap-4 sm:grid-cols-2" method="POST" action="{{ route('admin.defence-sessions.store') }}">
                    @csrf
                    <input type="hidden" name="committee_id" value="{{ $committee->id }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project</label>
                        <select name="project_id" class="mt-1 w-full border-gray-300 rounded" required>
                            @foreach ($approvedProjects as $p)
                                <option value="{{ $p->id }}" @selected(old('project_id') == $p->id)>{{ $p->title }}</option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Scheduled At</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="mt-1 w-full border-gray-300 rounded" required>
                        @error('scheduled_at')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Venue</label>
                        <input type="text" name="venue" value="{{ old('venue') }}" class="mt-1 w-full border-gray-300 rounded" placeholder="e.g., Seminar Hall A">
                        @error('venue')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Schedule</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>

