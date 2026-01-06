<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Evaluator (Supervisor)</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.evaluators.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supervisor</label>
                        <select name="user_id" class="mt-1 w-full border-gray-300 rounded" required>
                            <option value="">— Select supervisor —</option>
                            @foreach ($eligibleUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Add Evaluator</button>
                        <a class="ml-3 text-gray-600" href="{{ route('admin.evaluators.index') }}">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>