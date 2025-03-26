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
    </div>
</x-app-layout>
