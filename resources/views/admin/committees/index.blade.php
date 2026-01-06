<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Evaluation Committees</h2>
            <a href="{{ route('admin.committees.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded text-sm">New Committee</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Messages -->
            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    @if($committees->isEmpty())
                        <p class="text-gray-500">No committees created yet.</p>
                    @else
                        <div class="divide-y">
                            @foreach ($committees as $c)
                                <div class="py-3 flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">{{ $c->name }}</div>
                                        <div class="text-sm text-gray-600">
                                            {{ $c->members_count }} members
                                            @php
                                                $sessionCount = $c->sessions()->count();
                                            @endphp
                                            @if($sessionCount > 0)
                                                • {{ $sessionCount }} session(s)
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a class="text-indigo-600 hover:underline" href="{{ route('admin.committees.show', $c) }}">Manage</a>
                                        
                                        @php
                                            $sessionCount = $c->sessions()->count();
                                        @endphp
                                        
                                        @if($sessionCount == 0)
                                            <form action="{{ route('admin.committees.destroy', $c) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 text-sm"
                                                        onclick="return confirm('Are you sure you want to delete the committee {{ $c->name }}? This action cannot be undone.')">
                                                    Delete
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-sm" title="Cannot delete committee with {{ $sessionCount }} defence session(s)">
                                                Delete
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $committees->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>