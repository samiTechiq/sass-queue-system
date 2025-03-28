<!-- resources/views/livewire/business-location-dashboard.blade.php -->
<div>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                    {{ $location->name }} Dashboard
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Active Queues -->
                    <div class="bg-blue-100 overflow-hidden shadow-lg rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-blue-800 font-medium">Active Queues</p>
                                <p class="text-lg font-semibold text-blue-900">{{ $activeQueues }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Served Customers -->
                    <div class="bg-green-100 overflow-hidden shadow-lg rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-green-800 font-medium">Today's Served Customers</p>
                                <p class="text-lg font-semibold text-green-900">{{ $todayServedCustomers }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Average Wait Time -->
                    <div class="bg-yellow-100 overflow-hidden shadow-lg rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 text-white mr-4">

                                <!-- resources/views/livewire/business-location-dashboard.blade.php (continued) -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-yellow-800 font-medium">Average Wait Time</p>
                                <p class="text-lg font-semibold text-yellow-900">{{ $averageWaitTime }} minutes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Queue List -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Location Queues</h3>
                    @if($location->queues->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Queue Name</th>
                                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Waiting</th>
                                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($location->queues as $queue)
                                <tr>
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                        <div class="text-sm leading-5 font-medium text-gray-900">{{ $queue->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $queue->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $queue->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                        <div class="text-sm leading-5 text-gray-900">{{ $queue->tickets()->where('status', 'waiting')->count() }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300 text-sm leading-5 font-medium">
                                        <a href="{{ route('queues.show', $queue) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="bg-gray-100 p-4 rounded-md">
                        <p class="text-gray-700">No queues have been set up for this location yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
