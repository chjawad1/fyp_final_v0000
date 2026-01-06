<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($committee) ? 'Edit Committee' : 'Create Committee' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ isset($committee) ? route('admin.committees.update', $committee) : route('admin.committees.store') }}">
                    @csrf
                    @if (isset($committee))
                        @method('PUT')
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input name="name" class="mt-1 w-full border-gray-300 rounded" required value="{{ old('name', $committee->name ?? '') }}">
                        @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" class="mt-1 w-full border-gray-300 rounded">{{ old('description', $committee->description ?? '') }}</textarea>
                        @error('description')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-6">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">
                            {{ isset($committee) ? 'Update' : 'Create' }}
                        </button>
                        <a class="ml-3 text-gray-600" href="{{ route('admin.committees.index') }}">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>