<!-- resources/views/business/settings.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Business Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('business.settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">General Information</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Update your business's basic information.
                            </p>
                        </div>

                        <!-- Business Logo -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="mr-4">
                                    @if($business->logo_path)
                                    <img src="{{ Storage::url($business->logo_path) }}" alt="{{ $business->name }}" class="h-20 w-20 object-contain bg-gray-100 rounded">
                                    @else
                                    <div class="h-20 w-20 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <label for="logo" class="block text-sm font-medium text-gray-700">
                                        Business Logo
                                    </label>
                                    <div class="mt-1">
                                        <input type="file" id="logo" name="logo" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Upload a logo for your business. Recommended size: 200x200px.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Business Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Business Name
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $business->name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Business Email & Phone -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Business Email
                                </label>
                                <input type="email" id="email" name="email" value="{{ old('email', $business->email) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">
                                    Business Phone
                                </label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone', $business->phone) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Website URL -->
                        <div class="mb-6">
                            <label for="website" class="block text-sm font-medium text-gray-700">
                                Website URL
                            </label>
                            <input type="url" id="website" name="website" value="{{ old('website', $business->website) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('website')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Business Address -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Business Address</h3>
                        </div>

                        <div class="mb-6">
                            <label for="address" class="block text-sm font-medium text-gray-700">
                                Street Address
                            </label>
                            <input type="text" id="address" name="address" value="{{ old('address', $business->address) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700">
                                    City
                                </label>
                                <input type="text" id="city" name="city" value="{{ old('city', $business->city) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700">
                                    State / Province
                                </label>
                                <input type="text" id="state" name="state" value="{{ old('state', $business->state) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('state')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">
                                    ZIP / Postal Code
                                </label>
                                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $business->postal_code) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('postal_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="country" class="block text-sm font-medium text-gray-700">
                                Country
                            </label>
                            <select id="country" name="country" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="US" {{ old('country', $business->country) == 'US' ? 'selected' : '' }}>United States</option>
                                <option value="CA" {{ old('country', $business->country) == 'CA' ? 'selected' : '' }}>Canada</option>
                                <option value="GB" {{ old('country', $business->country) == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                <option value="AU" {{ old('country', $business->country) == 'AU' ? 'selected' : '' }}>Australia</option>
                                <!-- Add more countries as needed -->
                            </select>
                            @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="timezone" class="block text-sm font-medium text-gray-700">
                                Timezone
                            </label>
                            <select id="timezone" name="timezone" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @foreach($timezones as $tz => $tzName)
                                <option value="{{ $tz }}" {{ old('timezone', $business->timezone) == $tz ? 'selected' : '' }}>
                                    {{ $tzName }}
                                </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Your timezone is used for scheduling and reporting.
                            </p>
                            @error('timezone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Business Hours -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Business Hours</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Set your regular business hours.
                            </p>
                        </div>

                        <div class="mb-6 space-y-4">
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <div class="flex items-center">
                                <input type="checkbox" id="{{ $day }}_open" name="business_hours[{{ $day }}][open]" value="1" {{ isset($businessHours[$day]['open']) && $businessHours[$day]['open'] ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">

                                <label for="{{ $day }}_open" class="ml-2 w-24 text-sm font-medium text-gray-700 capitalize">
                                    {{ $day }}
                                </label>

                                <div class="ml-4 flex items-center">
                                    <input type="time" id="{{ $day }}_start" name="business_hours[{{ $day }}][start]" value="{{ old("business_hours.$day.start", $businessHours[$day]['start'] ?? '09:00') }}" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                                    <span class="mx-2 text-gray-500">to</span>

                                    <input type="time" id="{{ $day }}_end" name="business_hours[{{ $day }}][end]" value="{{ old("business_hours.$day.end", $businessHours[$day]['end'] ?? '17:00') }}" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end pt-6 border-t border-gray-200">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Add this section to the business/settings.blade.php file -->

        <!-- Display Settings -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900">Queue Display Settings</h3>
            <p class="mt-1 text-sm text-gray-500">
                Configure how your queue displays and self-service kiosk work.
            </p>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="space-y-6">
                    <!-- Display Links -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Display Links</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-gray-200 rounded-md p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900">Queue Display Board</h5>
                                        <p class="text-xs text-gray-500">Show on screens in your waiting area</p>
                                    </div>
                                    <a href="{{ $business->getDisplayUrl() }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-500">
                                        Open in new tab
                                    </a>
                                </div>
                                <div class="mt-2 flex items-center">
                                    <input type="text" value="{{ $business->getDisplayUrl() }}" readonly class="block w-full text-sm text-gray-500 bg-gray-100 rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onclick="this.select()">
                                    <button type="button" onclick="copyToClipboard('{{ $business->getDisplayUrl() }}')" class="ml-2 p-1.5 text-gray-500 hover:text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-md p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900">Self-Service Kiosk</h5>
                                        <p class="text-xs text-gray-500">For customers to join queues themselves</p>
                                    </div>
                                    <a href="{{ $business->getKioskUrl() }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-500">
                                        Open in new tab
                                    </a>
                                </div>
                                <div class="mt-2 flex items-center">
                                    <input type="text" value="{{ $business->getKioskUrl() }}" readonly class="block w-full text-sm text-gray-500 bg-gray-100 rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onclick="this.select()">
                                    <button type="button" onclick="copyToClipboard('{{ $business->getKioskUrl() }}')" class="ml-2 p-1.5 text-gray-500 hover:text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Settings -->
                    <div class="border-t border-gray-200 pt-6">
                        <form method="POST" action="{{ route('business.display-settings.update') }}">
                            @csrf
                            @method('PUT')

                            <h4 class="text-sm font-medium text-gray-900 mb-4">Display Configuration</h4>

                            <div class="space-y-4">
                                <!-- Public Display Toggle -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" id="public_display_enabled" name="queue_settings[public_display_enabled]" value="1" {{ ($business->queue_settings['public_display_enabled'] ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="public_display_enabled" class="font-medium text-gray-700">Enable Public Queue Display</label>
                                        <p class="text-gray-500">Allow anyone with the link to view your queue status</p>
                                    </div>
                                </div>

                                <!-- Kiosk Mode Toggle -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" id="kiosk_enabled" name="queue_settings[kiosk_enabled]" value="1" {{ ($business->queue_settings['kiosk_enabled'] ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="kiosk_enabled" class="font-medium text-gray-700">Enable Self-Service Kiosk</label>
                                        <p class="text-gray-500">Allow customers to join queues through the kiosk interface</p>
                                    </div>
                                </div>

                                <!-- Refresh Interval -->
                                <div>
                                    <label for="display_refresh_interval" class="block text-sm font-medium text-gray-700">Display Refresh Interval (seconds)</label>
                                    <select id="display_refresh_interval" name="queue_settings[display_refresh_interval]" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @php
                                        $currentInterval = $business->queue_settings['display_refresh_interval'] ?? 30;
                                        @endphp
                                        <option value="15" {{ $currentInterval == 15 ? 'selected' : '' }}>15 seconds</option>
                                        <option value="30" {{ $currentInterval == 30 ? 'selected' : '' }}>30 seconds</option>
                                        <option value="60" {{ $currentInterval == 60 ? 'selected' : '' }}>1 minute</option>
                                        <option value="120" {{ $currentInterval == 120 ? 'selected' : '' }}>2 minutes</option>
                                        <option value="300" {{ $currentInterval == 300 ? 'selected' : '' }}>5 minutes</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">How often the queue display board automatically refreshes</p>
                                </div>

                                <!-- Customer Name Privacy -->
                                <div>
                                    <label for="customer_name_display" class="block text-sm font-medium text-gray-700">Customer Name Display</label>
                                    <select id="customer_name_display" name="queue_settings[customer_name_display]" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @php
                                        $nameDisplay = $business->queue_settings['customer_name_display'] ?? 'masked';
                                        @endphp
                                        <option value="full" {{ $nameDisplay == 'full' ? 'selected' : '' }}>Full Name</option>
                                        <option value="masked" {{ $nameDisplay == 'masked' ? 'selected' : '' }}>Masked Name (e.g. J*** S****)</option>
                                        <option value="first_name" {{ $nameDisplay == 'first_name' ? 'selected' : '' }}>First Name Only</option>
                                        <option value="ticket_only" {{ $nameDisplay == 'ticket_only' ? 'selected' : '' }}>Ticket Number Only</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">How customer names appear on the public display</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save Display Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- QR Code Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">QR Codes for Queue Display</h4>
                        <p class="text-sm text-gray-500 mb-4">
                            Print these QR codes and place them at your location to give customers easy access to your queues.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-gray-200 rounded-md p-4 text-center">
                                <h5 class="text-sm font-medium text-gray-900 mb-2">Queue Display Board</h5>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($business->getDisplayUrl()) }}" alt="Queue Display QR Code" class="mx-auto h-32 w-32">
                                <div class="mt-2">
                                    <button type="button" onclick="window.open('https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($business->getDisplayUrl()) }}', '_blank')" class="text-xs text-indigo-600 hover:text-indigo-500">
                                        Download Larger Size
                                    </button>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-md p-4 text-center">
                                <h5 class="text-sm font-medium text-gray-900 mb-2">Self-Service Kiosk</h5>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($business->getKioskUrl()) }}" alt="Kiosk QR Code" class="mx-auto h-32 w-32">
                                <div class="mt-2">
                                    <button type="button" onclick="window.open('https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($business->getKioskUrl()) }}', '_blank')" class="text-xs text-indigo-600 hover:text-indigo-500">
                                        Download Larger Size
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    // Show success message
                    alert('Link copied to clipboard!');
                }, function(err) {
                    console.error('Could not copy text: ', err);
                });
            }

        </script>
    </div>
</x-app-layout>
