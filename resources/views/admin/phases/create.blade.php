<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('admin.phases.index') }}" class="mr-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark: text-gray-200 leading-tight">
                {{ __('Create FYP Phase') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.phases.store') }}">
                        @csrf

                        {{-- Phase Template Selection --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quick Select Template</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                @foreach($phaseTemplates as $slug => $template)
                                    <button type="button" 
                                            onclick="fillTemplate('{{ $template['name'] }}', '{{ $slug }}', {{ $template['order'] }}, '{{ $template['description'] }}')"
                                            class="p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-indigo-500 dark:hover:border-indigo-400 transition text-left">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $template['name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Order: {{ $template['order'] }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <hr class="my-6 border-gray-200 dark:border-gray-700">

                        {{-- Phase Name --}}
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Phase Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full rounded-md border-gray-300 dark: border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="e.g., Idea Approval">
                        </div>

                        {{-- Slug --}}
                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Slug <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                                   class="w-full rounded-md border-gray-300 dark: border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="e.g., idea_approval">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use:  idea_approval, scope_approval, or defence</p>
                        </div>

                        {{-- Semester --}}
                        <div class="mb-4">
                            <label for="semester" class="block text-sm font-medium text-gray-700 dark: text-gray-300 mb-1">
                                Semester <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="semester" id="semester" value="{{ old('semester') }}" required list="semester-list"
                                       class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark: text-gray-300 shadow-sm focus:border-indigo-500 focus: ring-indigo-500"
                                       placeholder="e.g., Spring 2026">
                                <datalist id="semester-list">
                                    @foreach($existingSemesters as $sem)
                                        <option value="{{ $sem }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        {{-- Order --}}
                        <div class="mb-4">
                            <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Order <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="order" id="order" value="{{ old('order', 1) }}" required min="1" max="10"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Phase sequence (1 = first, 2 = second, etc.)</p>
                        </div>

                        {{-- Date Range --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Start Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    End Date (Deadline) <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus: border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Instructions or notes for this phase... ">{{ old('description') }}</textarea>
                        </div>

                        {{-- Options --}}
                        <div class="mb-6 space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="allow_late" id="allow_late" value="1" {{ old('allow_late') ? 'checked' :  '' }}
                                       class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="allow_late" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Allow late submissions (will be marked with LATE badge)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark: border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Phase is active
                                </label>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.phases.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                Create Phase
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        function fillTemplate(name, slug, order, description) {
            document.getElementById('name').value = name;
            document. getElementById('slug').value = slug;
            document.getElementById('order').value = order;
            document.getElementById('description').value = description;
        }

        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const slug = this.value. toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_');
            document.getElementById('slug').value = slug;
        });
    </script>
</x-app-layout>