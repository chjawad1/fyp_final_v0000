<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Document Templates</h2>
            <a href="{{ route('admin.templates.trash') }}" class="text-sm text-blue-600 hover:text-blue-800">Recycle Bin</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Upload -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Upload Template</h3>
                    <p class="text-sm text-gray-500 mt-1">Allowed: PDF, DOC, DOCX. Max size: 10 MB.</p>

                    @if (session('success'))
                        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                            <p class="font-bold">Success</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('admin.templates.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md">
                            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description (optional)</label>
                            <textarea name="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('description') }}</textarea>
                            @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Template file</label>
                            <input
                                type="file"
                                name="file"
                                accept=".pdf,.doc,.docx"
                                class="mt-1 block w-full"
                            >
                            @error('file') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
                    </form>
                </div>
            </div> 

            <!-- List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Existing Templates</h3>

                    @if(($templates ?? null) && $templates->count())
                        <div class="mt-4 divide-y">
                            @foreach ($templates as $template)
                                <div class="py-3 flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">
                                            {{ $template->name ?? $template->original_name ?? 'Untitled' }}
                                        </div>
                                        @if (!empty($template->description))
                                            <div class="text-sm text-gray-600">{{ $template->description }}</div>
                                        @endif
                                        <!-- Audit info -->
                                        <div class="text-xs text-gray-500">
                                            Uploaded: {{ $template->created_at?->format('Y-m-d H:i') }}
                                            @if ($template->createdBy)
                                                by {{ $template->createdBy->name }}
                                            @endif
                                        </div>
                                        @if ($template->updated_at && $template->updated_at->gt($template->created_at))
                                            <div class="text-xs text-gray-500">
                                                Last updated: {{ $template->updated_at?->format('Y-m-d H:i') }}
                                                @if ($template->updatedBy)
                                                    by {{ $template->updatedBy->name }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if (strtolower(pathinfo($template->file_path, PATHINFO_EXTENSION)) === 'pdf')
                                            <a class="text-indigo-600 hover:text-indigo-800" href="{{ route('templates.view', $template) }}" target="_blank" rel="noopener">View</a>
                                        @endif
                                        <a class="text-blue-600 hover:text-blue-800" href="{{ route('templates.download', $template) }}">Download</a>
                                        <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Move this template to Recycle Bin?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $templates->links() }}
                        </div>
                    @else
                        <p class="mt-3 text-gray-500">No templates uploaded yet.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>