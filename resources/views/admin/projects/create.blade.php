<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Create New Project') }}
            </h2>
            <a href="{{ route('admin.projects.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Back to Projects
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($errors->any())
                        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.projects.store') }}">
                        @csrf

                        <!-- Project Title -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Project Title
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                   required>
                        </div>

                        <!-- Project Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Project Description
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4"
                                      class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                      required>{{ old('description') }}</textarea>
                        </div>

                        <!-- Student Selection -->
                        <div class="mb-4">
                            <label for="student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Assign to Student
                            </label>
                            <select id="student_id" 
                                    name="student_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="">Select a student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                        {{ $student->name }} ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                            @if($students->isEmpty())
                                <p class="mt-1 text-sm text-red-600">No available students found.  All students may already have projects.</p>
                            @endif
                        </div>

                        <!-- Supervisor Selection -->
                        <div class="mb-4">
                            <label for="supervisor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Assign Supervisor (Optional)
                            </label>
                            <select id="supervisor_id" 
                                    name="supervisor_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select a supervisor (optional)</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" @selected(old('supervisor_id') == $supervisor->id)>
                                        {{ $supervisor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Initial Status
                            </label>
                            <select id="status" 
                                    name="status"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
                                <option value="approved" @selected(old('status') === 'approved')>Approved</option>
                                <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
                            </select>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.projects.index') }}" 
                               class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Create Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>