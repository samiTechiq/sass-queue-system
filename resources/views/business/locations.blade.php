<!-- resources/views/business/locations.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Business Locations') }} - {{ $business->name }}
            </h2>
            <x-jet-button onclick="window.location.href='{{ route('businesses.show', $business) }}'">
                {{ __('Back to Business') }}
            </x-jet-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('business-locations', ['business' => $business])
        </div>
    </div>
</x-app-layout>
