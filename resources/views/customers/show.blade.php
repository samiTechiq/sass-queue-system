<!-- resources/views/customers/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customer Details') }}
            </h2>
            <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Back to Customers
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Customer Profile Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 mb-6 md:mb-0">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>

                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                    <p class="text-base text-gray-900">{{ $customer->name }}</p>
                                </div>

                                @if($customer->phone)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Phone</h4>
                                    <p class="text-base text-gray-900">{{ $customer->phone }}</p>
                                </div>
                                @endif

                                @if($customer->email)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Email</h4>
                                    <p class="text-base text-gray-900">{{ $customer->email }}</p>
                                </div>
                                @endif

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Customer Since</h4>
                                    <p class="text-base text-gray-900">{{ $customer->created_at->format('F j, Y') }}</p>
                                </div>
                            </div>

                            @if($customer->notes)
                            <div class="mt-6">
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Notes</h4>
                                <div class="bg-gray-50 p-3 rounded-md text-sm text-gray-700">
                                    {{ $customer->notes }}
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="md:w-2/3 md:pl-8 md:border-l md:border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Statistics</h3>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-indigo-600">{{ $stats['total_visits'] }}</div>
                                    <div class="text-sm text-gray-500">Total Visits</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $stats['served_count'] }}</div>
                                    <div class="text-sm text-gray-500">Served</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-red-600">{{ $stats['no_show_count'] }}</div>
                                    <div class="text-sm text-gray-500">No-Shows</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['average_wait_time'] }}</div>
                                    <div class="text-sm text-gray-500">Avg. Wait (min)</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-gray-600">{{ $stats['cancelled_count'] }}</div>
                                    <div class="text-sm text-gray-500">Cancellations</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-indigo-600">
                                        {{ $stats['served_count'] > 0 ? round(($stats['served_count'] / ($stats['served_count'] + $stats['no_show_count'])) * 100) : 0 }}%
                                    </div>
                                    <div class="text-sm text-gray-500">Show Rate</div>
                                </div>
                            </div>

                            <div class="flex space-x-2">
                                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Edit Customer
                                </a>

                                @if($stats['total_visits'] === 0)
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Delete Customer
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Queue History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Queue History</h3>

                    @if($queueEntries->isEmpty())
                    <p class="text-gray-500 text-sm">This customer has not been in any queues yet.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Queue
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Wait Time
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Served By
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notes
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($queueEntries as $entry)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $entry->created_at->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $entry->queue->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-medium rounded-full
                                                    {{ $entry->status === 'served' ? 'bg-green-100 text-green-800' :
                                                       ($entry->status === 'no_show' ? 'bg-red-100 text-red-800' :
                                                       ($entry->status === 'cancelled' ? 'bg-gray-100 text-gray-800' :
                                                       ($entry->status === 'waiting' ? 'bg-blue-100 text-blue-800' :
                                                       'bg-yellow-100 text-yellow-800'))) }}">
                                            {{ ucfirst($entry->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($entry->status === 'served' && $entry->served_time)
                                        {{ $entry->created_at->diffInMinutes($entry->served_time) }} min
                                        @elseif($entry->status === 'waiting')
                                        Still waiting
                                        @elseif($entry->status === 'called' || $entry->status === 'serving')
                                        In process
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $entry->staff ? $entry->staff->name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $entry->notes ?: '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $queueEntries->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
