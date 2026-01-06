<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit and Resubmit Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Display Supervisor Feedback -->
                    @if ($project->rejection_reason)
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 p-4 rounded-md">
                            <h3 class="font-bold text-lg mb-2">Supervisor Feedback</h3>
                            <p>{{ $project->rejection_reason }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('projects.update', $project) }}">
                        @csrf
                        @method('PATCH') {{-- Use PATCH for updates --}}

                        <!-- Project Title -->
                        <div>
                            <x-input-label for="title" :value="__('Project Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $project->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Project Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Project Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="6" required>{{ old('description', $project->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Supervisor Selection -->
                        <div class="mt-4">
                            <x-input-label for="supervisor_id" :value="__('Select a Supervisor')" />
                            <select id="supervisor_id" name="supervisor_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Please choose a supervisor --</option>
                                @foreach ($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" {{ old('supervisor_id', $project->supervisor_id) == $supervisor->id ? 'selected' : '' }}>
                                        {{ $supervisor->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('supervisor_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update and Resubmit') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>