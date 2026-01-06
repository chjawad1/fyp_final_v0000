<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Upload New Scope Document for: <span class="font-bold">{{ $project->title }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.projects.scope-documents.store', $project) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Version -->
                        <div>
                            <x-input-label for="version" :value="__('Version Name')" />
                            <x-text-input id="version" class="block mt-1 w-full" type="text" name="version" :value="old('version')" required autofocus placeholder="e.g., v1.1, Final Draft" />
                            <x-input-error :messages="$errors->get('version')" class="mt-2" />
                        </div>

                        <!-- Document File -->
                        <div class="mt-4">
                            <x-input-label for="document" :value="__('Document File (PDF, DOC, DOCX)')" />
                            <input type="file" name="document" id="document" accept=".pdf,.doc,.docx" class="block w-full mt-1 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" required>
                            <x-input-error :messages="$errors->get('document')" class="mt-2" />
                        </div>

                        <!-- Changelog -->
                        <div class="mt-4">
                            <x-input-label for="changelog" :value="__('Changelog / Description of Changes (Optional)')" />
                            <textarea id="changelog" name="changelog" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Describe the changes in this version...">{{ old('changelog') }}</textarea>
                            <x-input-error :messages="$errors->get('changelog')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.projects.scope-documents.index', $project) }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-4">
                                Cancel
                            </a>

                            <x-primary-button>
                                {{ __('Upload Version') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>