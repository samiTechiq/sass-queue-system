<!-- resources/views/livewire/business-locations.blade.php -->
<div>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Business Locations') }}
                    </h2>
                    <button wire:click="create()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('Add Location') }}
                    </button>
                </div>

                @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('message') }}</p>
                </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-500 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($locations as $location)
                            <tr>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    <div class="text-sm leading-5 text-gray-900">{{ $location->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    <div class="text-sm leading-5 text-gray-900">
                                        {{ $location->address }}<br>
                                        {{ $location->city }}, {{ $location->state }} {{ $location->zip_code }}<br>
                                        {{ $location->country }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    <div class="text-sm leading-5 text-gray-900">
                                        {{ $location->phone }}<br>
                                        {{ $location->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $location->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300 text-sm leading-5 font-medium">
                                    <button wire:click="edit({{ $location->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                        Edit
                                    </button>
                                    <button wire:click="confirmLocationDeletion({{ $location->id }})" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                    <a href="{{ route('location.dashboard', $location->id) }}" class="text-blue-600 hover:text-blue-900 mr-2">
                                        Dashboard
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300" colspan="5">
                                    <div class="text-sm leading-5 text-gray-900 text-center">No locations found.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $locations->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for creating/editing location -->
    <x-jet-dialog-modal wire:model="isOpen">
        <x-slot name="title">
            {{ isset($location['id']) ? 'Edit Location' : 'Create Location' }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input id="name" class="block mt-1 w-full" type="text" wire:model.defer="location.name" />
                @error('location.name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <x-jet-label for="address" value="{{ __('Address') }}" />
                <x-jet-input id="address" class="block mt-1 w-full" type="text" wire:model.defer="location.address" />
                @error('location.address') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <x-jet-label for="city" value="{{ __('City') }}" />
                    <x-jet-input id="city" class="block mt-1 w-full" type="text" wire:model.defer="location.city" />
                    @error('location.city') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-jet-label for="state" value="{{ __('State') }}" />
                    <x-jet-input id="state" class="block mt-1 w-full" type="text" wire:model.defer="location.state" />
                    @error('location.state') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <x-jet-label for="zip_code" value="{{ __('ZIP Code') }}" />
                    <x-jet-input id="zip_code" class="block mt-1 w-full" type="text" wire:model.defer="location.zip_code" />
                    @error('location.zip_code') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-jet-label for="country" value="{{ __('Country') }}" />
                    <x-jet-input id="country" class="block mt-1 w-full" type="text" wire:model.defer="location.country" />
                    @error('location.country') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <x-jet-label for="phone" value="{{ __('Phone') }}" />
                    <x-jet-input id="phone" class="block mt-1 w-full" type="text" wire:model.defer="location.phone" />
                    @error('location.phone') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-jet-label for="email" value="{{ __('Email') }}" />
                    <x-jet-input id="email" class="block mt-1 w-full" type="email" wire:model.defer="location.email" />
                    @error('location.email') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label class="flex items-center">
                    <x-jet-checkbox wire:model.defer="location.is_active" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                </label>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="closeModal()" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            @if (isset($location['id']))
            <x-jet-button class="ml-2" wire:click="update()" wire:loading.attr="disabled">
                {{ __('Update') }}
            </x-jet-button>
            <!-- resources/views/livewire/business-locations.blade.php (continued) -->
            @else
            <x-jet-button class="ml-2" wire:click="store()" wire:loading.attr="disabled">
                {{ __('Create') }}
            </x-jet-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>

    <!-- Delete Confirmation Modal -->
    <x-jet-confirmation-modal wire:model="confirmingLocationDeletion">
        <x-slot name="title">
            {{ __('Delete Location') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you want to delete this location? Once a location is deleted, all of its resources and data will be permanently deleted.') }}
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingLocationDeletion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="deleteLocation()" wire:loading.attr="disabled">
                {{ __('Delete') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-confirmation-modal>
</div>
