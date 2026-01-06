<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Evaluator Directory</h2>
            <a href="{{ route('admin.evaluators.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded text-sm">Add Evaluator</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
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

                <form method="GET" class="flex flex-wrap gap-3">
                    <select name="status" class="border-gray-300 rounded">
                        <option value="">All Statuses</option>
                        <option value="available" @selected(request('status')==='available')>Available</option>
                        <option value="assigned" @selected(request('status')==='assigned')>Assigned</option>
                    </select>
                    <button class="px-3 py-1.5 bg-gray-800 text-white rounded text-sm">Filter</button>
                </form>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-600">
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($evaluators as $e)
                                <tr>
                                    <td class="py-2 pr-4">{{ $e->user->name }}</td>
                                    <td class="py-2 pr-4">{{ $e->user->email }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="px-2 py-0.5 rounded-full text-xs {{ $e->status==='available' ? 'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">
                                            {{ ucfirst($e->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4">
                                    @if($e->status === 'assigned')
                                        <span class="text-gray-400 text-xs">Cannot remove (assigned)</span>
                                    @else
                                        <form action="{{ route('admin.evaluators.destroy', $e) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-xs"
                                                    onclick="return confirm('Remove {{ $e->user->name }} from evaluator directory? ')">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $evaluators->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>