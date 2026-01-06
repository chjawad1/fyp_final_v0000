<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Session Details</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-600">Project</div>
                        <div class="font-medium">{{ $session->project->title ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Committee</div>
                        <div class="font-medium">{{ $session->committee->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Scheduled</div>
                        <div class="font-medium">{{ $session->scheduled_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Venue</div>
                        <div class="font-medium">{{ $session->venue ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Status</div>
                        <div class="font-medium">{{ ucfirst($session->status) }}</div>
                    </div>
                </div>

                <form class="mt-4" method="POST" action="{{ route('admin.defence-sessions.update-status', $session) }}">
                    @csrf
                    @method('PATCH')
                    <label class="text-sm text-gray-700">Update Status</label>
                    <div class="flex gap-2 mt-1">
                        <select name="status" class="border-gray-300 rounded">
                            @foreach (['scheduled','completed','cancelled'] as $st)
                                <option value="{{ $st }}" @selected($session->status===$st)>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                        <button class="px-3 py-1.5 bg-indigo-600 text-white rounded text-sm">Save</button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-gray-900">Evaluators</h3>
                </div>

                <form class="mt-3" method="POST" action="{{ route('admin.defence-sessions.assign-evaluators', $session) }}">
                    @csrf
                    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $committeeMembers = $session->committee->members;
                            $assigned = $session->assignments->pluck('user_id')->all();
                        @endphp
                        @foreach ($committeeMembers as $m)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="evaluator_ids[]" value="{{ $m->id }}" @checked(in_array($m->id, $assigned))>
                                <span>{{ $m->name }} <span class="text-gray-500 text-xs">({{ $m->email }})</span></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <button class="px-3 py-1.5 bg-indigo-600 text-white rounded text-sm">Update Evaluators</button>
                    </div>
                </form>

                <div class="mt-6">
                    <h4 class="font-medium">Submissions</h4>
                    <div class="mt-2 divide-y">
                        @forelse ($session->assignments as $a)
                            <div class="py-2 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $a->evaluator->name }}</div>
                                    <div class="text-sm text-gray-600">
                                        @if ($a->submitted_at)
                                            Submitted: {{ $a->submitted_at->format('Y-m-d H:i') }} — Total: {{ $a->total_score }}
                                        @else
                                            Pending
                                        @endif
                                    </div>
                                </div>
                                @if ($a->scores_json)
                                    <div class="text-xs text-gray-600">
                                        @foreach ($a->scores_json as $k => $v)
                                            <span class="mr-2">{{ ucfirst($k) }}: {{ $v }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500">No evaluators assigned.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>