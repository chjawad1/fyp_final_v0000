<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Generate New Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('admin.reports.store') }}">
                        @csrf

                        <!-- Report Title -->
                        <div>
                            <x-input-label for="title" :value="__('Report Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" 
                                          : value="{{ old('title') }}" required placeholder="Enter report title..." />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Report Type -->
                        <div class="mt-4">
                            <x-input-label for="type" :value="__('Report Type')" />
                            <select name="type" id="type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark: focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Report Type</option>
                                @foreach($reportTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Output Format -->
                        <div class="mt-4">
                            <x-input-label for="format" :value="__('Output Format')" />
                            <select name="format" id="format" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark: focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Format</option>
                                <option value="csv" @selected(old('format') == 'csv')>CSV (Excel Compatible)</option>
                                <option value="excel" @selected(old('format') == 'excel')>Excel</option>
                                <option value="pdf" @selected(old('format') == 'pdf')>PDF</option>
                            </select>
                            <x-input-error :messages="$errors->get('format')" class="mt-2" />
                        </div>

                        <!-- Date Range -->
                        <div class="mt-4 grid grid-cols-1 md: grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="date_from" :value="__('From Date (Optional)')" />
                                <x-text-input id="date_from" class="block mt-1 w-full" type="date" name="date_from" : value="old('date_from')" />
                                <x-input-error :messages="$errors->get('date_from')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="date_to" :value="__('To Date (Optional)')" />
                                <x-text-input id="date_to" class="block mt-1 w-full" type="date" name="date_to" :value="old('date_to')" />
                                <x-input-error :messages="$errors->get('date_to')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="status_filter" :value="__('Status Filter (Optional)')" />
                                <select name="status_filter" id="status_filter" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">All Statuses</option>
                                    <option value="pending" @selected(old('status_filter') == 'pending')>Pending</option>
                                    <option value="scheduled" @selected(old('status_filter') == 'scheduled')>Scheduled</option>
                                    <option value="completed" @selected(old('status_filter') == 'completed')>Completed</option>
                                    <option value="cancelled" @selected(old('status_filter') == 'cancelled')>Cancelled</option>
                                    <option value="approved" @selected(old('status_filter') == 'approved')>Approved</option>
                                    <option value="rejected" @selected(old('status_filter') == 'rejected')>Rejected</option>
                                </select>
                                <x-input-error :messages="$errors->get('status_filter')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="role_filter" :value="__('Role Filter (Optional)')" />
                                <select name="role_filter" id="role_filter" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark: focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">All Roles</option>
                                    <option value="student" @selected(old('role_filter') == 'student')>Students</option>
                                    <option value="supervisor" @selected(old('role_filter') == 'supervisor')>Supervisors</option>
                                    <option value="admin" @selected(old('role_filter') == 'admin')>Admins</option>
                                </select>
                                <x-input-error :messages="$errors->get('role_filter')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-4">
                            <a href="{{ route('admin.reports.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Generate Report') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>