<!-- resources/views/business/location-dashboard.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $location->name }} - Dashboard
            </h2>
            <div class="flex space-x-2">
                <x-jet-button onclick="window.location.href='{{ route('business.locations', $location->business) }}'">
                    {{ __('Back to Locations') }}
                </x-jet-button>
                <x-jet-button onclick="window.location.href='{{ route('businesses.show', $location->business) }}'">
                    {{ __('Back to Business') }}
                </x-jet-button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('business-location-dashboard', ['location' => $location])
        </div>
    </div>
</x-app-layout>
