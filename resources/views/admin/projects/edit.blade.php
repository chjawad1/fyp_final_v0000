<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Project:  {{ Str::limit($project->title, 50) }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.projects.show', $project) }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    View Project
                </a>
                <a href="{{ route('admin.projects.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back to Projects
                </a>
            </div>
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

                    <form method="POST" action="{{ route('admin.projects.update', $project) }}">
                        @csrf
                        @method('PUT')

                        <!-- Project Title -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Project Title
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $project->title) }}"
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
                                      class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus: ring-indigo-600 rounded-md shadow-sm"
                                      required>{{ old('description', $project->description) }}</textarea>
                        </div>

                        <!-- Student Selection (Read-only display or select) -->
                        <div class="mb-4">
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Assigned Student
                            </label>
                            <select id="user_id" 
                                    name="user_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="">Select a student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" 
                                            @selected(old('user_id', $project->user_id) == $student->id)>
                                        {{ $student->name }} ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Supervisor Selection -->
                        <div class="mb-4">
                            <label for="supervisor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Assign Supervisor (Optional)
                            </label>
                            <select id="supervisor_id" 
                                    name="supervisor_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark: focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select a supervisor (optional)</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" 
                                            @selected(old('supervisor_id', $project->supervisor_id) == $supervisor->id)>
                                        {{ $supervisor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Semester Selection -->
                        <div class="mb-4">
                            <label for="semester" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Semester
                            </label>
                            <select id="semester" 
                                    name="semester"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select semester (optional)</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester }}" 
                                            @selected(old('semester', $project->semester) == $semester)>
                                        {{ $semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Current Phase -->
                        <div class="mb-4">
                            <label for="current_phase" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Current Phase
                            </label>
                            <select id="current_phase" 
                                    name="current_phase"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark: focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="idea" @selected(old('current_phase', $project->current_phase) === 'idea')>Idea Approval</option>
                                <option value="scope" @selected(old('current_phase', $project->current_phase) === 'scope')>Scope Approval</option>
                                <option value="defence" @selected(old('current_phase', $project->current_phase) === 'defence')>Defence</option>
                                <option value="completed" @selected(old('current_phase', $project->current_phase) === 'completed')>Completed</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Project Status
                            </label>
                            <select id="status" 
                                    name="status"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="pending" @selected(old('status', $project->status) === 'pending')>Pending</option>
                                <option value="approved" @selected(old('status', $project->status) === 'approved')>Approved</option>
                                <option value="rejected" @selected(old('status', $project->status) === 'rejected')>Rejected</option>
                                <option value="completed" @selected(old('status', $project->status) === 'completed')>Completed</option>
                            </select>
                        </div>

                        <!-- Late Submission Flag -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_late" 
                                       value="1"
                                       {{ old('is_late', $project->is_late) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark: focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Mark as Late Submission</span>
                            </label>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.projects.show', $project) }}" 
                               class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Update Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>