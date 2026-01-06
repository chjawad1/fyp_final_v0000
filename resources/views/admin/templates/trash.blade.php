<x-app-layout>
    <!-- <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Recycle Bin — Document Templates</h2>
            <a href="{{ route('admin.templates.index') }}"  class="text-sm text-blue-600 hover:text-blue-800">Back to Templates</a>

            @if (($templates ?? null) && $templates->total() > 0)
                <form action="{{ route('admin.templates.trash.clear') }}" method="POST" onsubmit="return confirm('Permanently delete ALL items in the Recycle Bin? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                        Clear All
                    </button>
                </form>
            @endif
        </div>
    </x-slot> -->
    <x-slot name="header">
    @php
        $hasItems = ($templates ?? null) && $templates->total() > 0;
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <!-- Title + count -->
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Recycle Bin — Document Templates
            </h2>

            @if (($templates ?? null) && method_exists($templates, 'total'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                             bg-gray-100 text-gray-700
                             dark:bg-gray-700 dark:text-gray-200">
                    {{ number_format($templates->total()) }} items
                </span>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.templates.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md
                      border border-gray-300 text-gray-700 bg-white hover:bg-gray-50
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0
                      dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700"
               title="Back to Templates">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Templates
            </a>

            <form action="{{ route('admin.templates.trash.clear') }}" method="POST"
                  onsubmit="return confirm('Permanently delete ALL items in the Recycle Bin? This cannot be undone.');">
                @csrf
                @method('DELETE')

                <button type="submit"
                        @class([
                            'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md focus:outline-none focus:ring-2',
                            'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500' => $hasItems,
                            'bg-red-200 text-red-700 cursor-not-allowed opacity-60 dark:bg-red-900/30 dark:text-red-300' => ! $hasItems,
                        ])
                        @disabled(! $hasItems)
                        title="{{ $hasItems ? 'Permanently delete all items' : 'Recycle Bin is already empty' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-3h4m-6 0h8m-9 3h10M9 10v8m6-8v8" />
                    </svg>
                    Clear All
                </button>
            </form>
        </div>
    </div>
</x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- 👇 ADD THIS ERROR MESSAGE BLOCK 👇 --}}
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            {{-- 👆 END ERROR MESSAGE BLOCK 👆 --}}

            {{-- Success Message --}}
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Success</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Trashed Templates</h3>

                        <div class="text-sm text-gray-600">
                            Retention: {{ config('templates.retention_days', 30) }} days
                        </div>
                    </div>

                    @if ($templates->count() === 0)
                        <p class="mt-4 text-gray-500">Recycle Bin is empty.</p>
                    @else
                        <div class="mt-4 divide-y">
                            @foreach ($templates as $template)
                                @php
                                    $retentionDays = (int) config('templates.retention_days', 30);
                                    $purgeAt = optional($template->deleted_at)->copy()?->addDays($retentionDays);
                                    $now = now();

                                    $remaining = '';
                                    if ($purgeAt instanceof \Carbon\Carbon) {
                                        if ($purgeAt->isPast()) {
                                            $remaining = 'due for purge';
                                        } else {
                                            $seconds = $now->diffInSeconds($purgeAt);
                                            $days    = intdiv($seconds, 86400); $seconds %= 86400;
                                            $hours   = intdiv($seconds, 3600);  $seconds %= 3600;
                                            $minutes = intdiv($seconds, 60);

                                            if ($days > 0) {
                                                $remaining = "{$days}d {$hours}h left";
                                            } elseif ($hours > 0) {
                                                $remaining = "{$hours}h {$minutes}m left";
                                            } else {
                                                $remaining = "{$minutes}m left";
                                            }
                                        }
                                    }
                                @endphp

                                <div class="py-3 flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">
                                            {{ $template->name ?? 'Untitled' }}
                                        </div>

                                        <!-- Condensed audit + remaining time badge -->
                                         <div class="text-xs text-gray-500">
                                            @if ($template->created_at)
                                                Uploaded: {{ $template->created_at?->format('Y-m-d H:i') }}
                                                @if ($template->createdBy)
                                                    by {{ $template->createdBy->name }}
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Deleted at: {{ $template->deleted_at?->format('Y-m-d H:i') }}
                                            @if ($template->deletedBy)
                                                by {{ $template->deletedBy->name }}
                                            @endif
                                        </div>
                                        @if ($remaining !== '')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ $remaining }}
                                            </span>
                                        @endif
                                    </div>
                                        
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <form action="{{ route('admin.templates.restore', $template->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="text-green-700 hover:text-green-900">Restore</button>
                                        </form>
                                        <form action="{{ route('admin.templates.force-delete', $template->id) }}" method="POST" onsubmit="return confirm('Permanently delete this template and its file? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:text-red-800">Delete Permanently</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $templates->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>