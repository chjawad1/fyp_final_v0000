<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Evaluate: {{ $assignment->session->project->title ?? 'Project' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('member.sessions.submit', $assignment->id) }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        @foreach ($rubric as $item)
                            @php
                                $existing = $assignment->scores_json[$item['key']] ?? '';
                            @endphp
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ $item['label'] }} (0-{{ $item['max'] }})
                                </label>
                                <input type="number" name="scores[{{ $item['key'] }}]" min="0" max="{{ $item['max'] }}" value="{{ old('scores.'.$item['key'], $existing) }}" class="mt-1 w-40 border-gray-300 rounded" required>
                                @error('scores.'.$item['key'])<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Remarks</label>
                        <textarea name="remarks" rows="4" class="mt-1 w-full border-gray-300 rounded">{{ old('remarks', $assignment->remarks) }}</textarea>
                        @error('remarks')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Submit</button>
                        <a class="ml-3 text-gray-600" href="{{ route('member.sessions.index') }}">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>