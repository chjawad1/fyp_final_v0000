<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit User') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" : value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email (Read-only) -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="email" name="email" :value="$user->email" disabled />
                        </div>

                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Role')" />
                            <select name="role" id="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus: border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="student" @selected(old('role', $user->role) == 'student')>Student</option>
                                <option value="supervisor" @selected(old('role', $user->role) == 'supervisor')>Supervisor</option>
                                <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Supervisor-specific fields - Show only if user is supervisor -->
                        @if($user->role === 'supervisor')
                            <div class="mt-4 p-4 border rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <h3 class="font-medium text-lg mb-3">Supervisor Settings</h3>
                                
                                <!-- Available Slots -->
                                <div class="mb-4">
                                    <x-input-label for="available_slots" :value="__('Available Slots')" />
                                    <x-text-input id="available_slots" class="block mt-1 w-full" type="number" name="available_slots" 
                                                  :value="old('available_slots', $user->supervisorProfile->available_slots ??  8)" 
                                                  min="0" max="20" />
                                    <p class="text-xs text-gray-500 mt-1">Number of projects this supervisor can handle (0-20)</p>
                                    <x-input-error :messages="$errors->get('available_slots')" class="mt-2" />
                                </div>

                                <!-- Research Interests -->
                                <div>
                                    <x-input-label for="research_interests" :value="__('Research Interests')" />
                                    <textarea id="research_interests" name="research_interests" rows="3" 
                                              class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                              placeholder="Enter research areas and interests... ">{{ old('research_interests', $user->supervisorProfile->research_interests ?? '') }}</textarea>
                                    <x-input-error :messages="$errors->get('research_interests')" class="mt-2" />
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.users.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update User') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Additional Admin Actions -->
                    @if($user->id !== Auth::id())
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="font-medium text-lg mb-4">Administrative Actions</h3>
                            <div class="flex gap-4">
                                <!-- Reset Password -->
                                <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                                            onclick="return confirm('Reset password for {{ $user->name }}?  This will set their password to: password123')">
                                        Reset Password
                                    </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>