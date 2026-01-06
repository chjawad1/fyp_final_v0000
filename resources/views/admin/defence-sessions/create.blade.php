<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Schedule Defence Session</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 rounded bg-green-50 px-3 py-2 text-green-800 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded bg-red-50 px-3 py-2 text-red-800 text-sm">
                        <div class="font-medium">Please correct the errors below:</div>
                        <ul class="mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.defence-sessions.store') }}" class="grid gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Committee</label>
                        <select name="committee_id" class="mt-1 w-full border-gray-300 rounded" required>
                            @foreach ($committees as $c)
                                <option value="{{ $c->id }}" @selected(old('committee_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('committee_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project</label>
                        <select name="project_id" class="mt-1 w-full border-gray-300 rounded" required>
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}" @selected(old('project_id') == $p->id)>{{ $p->title }}</option>
                            @endforeach
                        </select>
                        @error('project_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Scheduled At</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="mt-1 w-full border-gray-300 rounded" required>
                        @error('scheduled_at') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Venue</label>
                        <input type="text" name="venue" value="{{ old('venue') }}" class="mt-1 w-full border-gray-300 rounded" placeholder="e.g., Seminar Hall A">
                        @error('venue') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Schedule</button>
                        <a class="ml-3 text-gray-600" href="{{ route('admin.defence-sessions.index') }}">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>