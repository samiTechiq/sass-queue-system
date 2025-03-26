<!-- resources/views/queues/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Queue') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('queues.store') }}">
                        @csrf

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Queue Information</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Fill in the details for your new queue.
                            </p>
                        </div>

                        <!-- Queue Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Queue Name
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <p class="mt-1 text-xs text-gray-500">
                                This name will be displayed to staff and customers.
                            </p>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Queue Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description (Optional)
                            </label>
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Provide additional information about this queue.
                            </p>
                            @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Wait Time and Capacity -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="estimated_wait_time" class="block text-sm font-medium text-gray-700">
                                    Estimated Time Per Customer (minutes)
                                </label>
                                <input type="number" id="estimated_wait_time" name="estimated_wait_time" min="1" max="120" value="{{ old('estimated_wait_time', 5) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="mt-1 text-xs text-gray-500">
                                    Average time needed to serve each customer.
                                </p>
                                @error('estimated_wait_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="max_size" class="block text-sm font-medium text-gray-700">
                                    Maximum Queue Size (Optional)
                                </label>
                                <input type="number" id="max_size" name="max_size" min="1" max="1000" value="{{ old('max_size') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="mt-1 text-xs text-gray-500">
                                    Leave empty for unlimited queue size.
                                </p>
                                @error('max_size')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Location -->
                        @if($locations->isNotEmpty())
                        <div class="mb-6">
                            <label for="location_id" class="block text-sm font-medium text-gray-700">
                                Location (Optional)
                            </label>
                            <select id="location_id" name="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">No specific location</option>
                                @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Assign this queue to a specific business location.
                            </p>
                            @error('location_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <!-- Advanced Settings (optional) -->
                        <div x-data="{ open: false }" class="mb-6">
                            <button type="button" @click="open = !open" class="flex items-center text-sm text-indigo-600 hover:text-indigo-900 mb-2">
                                <svg :class="{'rotate-90': open}" class="transform transition-transform h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Advanced Settings
                            </button>

                            <div x-show="open" class="bg-gray-50 p-4 rounded-md" style="display: none;">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Notification Settings
                                    </label>

                                    <div class="space-y-2">
                                        <div class="flex items-start">
                                            <input type="checkbox" id="settings_auto_sms" name="settings[auto_sms]" value="1" {{ old('settings.auto_sms') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-1">
                                            <label for="settings_auto_sms" class="ml-2 block text-sm text-gray-700">
                                                Automatically send SMS notification when customer is called
                                                @unless($canSendSms)
                                                <span class="text-xs text-gray-500">(requires SMS notification feature)</span>
                                                @endunless
                                            </label>
                                        </div>

                                        <div class="flex items-start">
                                            <input type="checkbox" id="settings_auto_email" name="settings[auto_email]" value="1" {{ old('settings.auto_email') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-1">
                                            <label for="settings_auto_email" class="ml-2 block text-sm text-gray-700">
                                                Automatically send email notification when customer is called
                                                @unless($canSendEmail)
                                                <span class="text-xs text-gray-500">(requires email notification feature)</span>
                                                @endunless
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Queue Behavior
                                    </label>

                                    <div class="space-y-2">
                                        <div class="flex items-start">
                                            <input type="checkbox" id="settings_auto_no_show" name="settings[auto_no_show]" value="1" {{ old('settings.auto_no_show') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-1">
                                            <label for="settings_auto_no_show" class="ml-2 block text-sm text-gray-700">
                                                Automatically mark as no-show after 5 minutes of being called
                                            </label>
                                        </div>

                                        <div class="flex items-start">
                                            <input type="checkbox" id="settings_collect_feedback" name="settings[collect_feedback]" value="1" {{ old('settings.collect_feedback') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-1">
                                            <label for="settings_collect_feedback" class="ml-2 block text-sm text-gray-700">
                                                Collect customer feedback after service
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('queues.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create Queue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
