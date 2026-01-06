<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Upload Users via CSV') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Session Status Messages -->
            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                    {{ session('success') }}
                </div>
            @endif
             @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6 p-4 border border-blue-300 bg-blue-50 rounded-lg dark:bg-gray-700 dark:border-blue-600">
                        <h3 class="font-bold text-lg mb-2">Instructions</h3>
                        <p class="mb-2">Please ensure your CSV file has the following columns in this exact order:</p>
                        <code class="text-sm bg-gray-200 dark:bg-gray-900 p-2 rounded">name,email,role</code>
                        <ul class="list-disc list-inside mt-2">
                            <li>The <span class="font-mono">name</span> column should contain the user's full name.</li>
                            <li>The <span class="font-mono">email</span> column must contain a unique, valid email address.</li>
                            <li>The <span class="font-mono">role</span> column must be one of: <span class="font-mono">student</span> or <span class="font-mono">supervisor</span>.</li>
                            <li>A default password ('<span class="font-mono">password</span>') will be assigned to all new users. They will be required to change it upon first login.</li>
                        </ul>
                        <div class="mt-4">
                            <a href="{{ route('admin.users.template.download') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Download CSV Template
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('admin.users.upload.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <x-input-label for="csv_file" :value="__('CSV File')" />
                            <input type="file" name="csv_file" id="csv_file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" required>
                            <x-input-error :messages="$errors->get('csv_file')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Upload and Create Users') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>