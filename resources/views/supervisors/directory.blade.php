<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Supervisor Directory</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="flex gap-3">
                        <input
                            type="text"
                            name="q"
                            value="{{ $q }}"
                            placeholder="Search by name or email"
                            class="w-full sm:w-80 border-gray-300 rounded-md"
                        />
                        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Search
                        </button>
                        @if ($q !== '')
                            <a href="{{ route('supervisors.directory') }}" class="px-4 py-2 border rounded hover:bg-gray-50">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($supervisors->count() === 0)
                        <p class="text-gray-600">No supervisors found.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($supervisors as $s)
                                <div class="border rounded-md p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="font-semibold">{{ $s->name }}</div>
                                        <span class="text-xs text-gray-500">{{ $s->email }}</span>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">
                                        Research Interests:
                                        <p class="font-medium">
                                            {{ $s->supervisorProfile->research_interests ?? '—' }}
                                        </p>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">
                                        Available slots:
                                        <span class="font-medium">
                                            {{ optional($s->supervisorProfile)->available_slots ?? '—' }}
                                        </span>
                                    </div>
                                    {{-- Add more fields if you have them, e.g., department --}}
                                    {{-- <div class="text-sm text-gray-600">Department: {{ $s->department ?? '—' }}</div> --}}
                                </div>
                            @endforeach
                        </div>
    
                        <div class="mt-6">
                            {{ $supervisors->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>