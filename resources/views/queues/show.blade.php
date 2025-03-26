<!-- resources/views/queues/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-2 sm:mb-0">
                {{ $queue->name }}
            </h2>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $queue->status === 'active' ? 'bg-green-100 text-green-800' : ($queue->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($queue->status) }}
                </span>
                <a href="{{ route('queues.edit', $queue) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Edit Queue
                </a>
                @if($business->hasFeature(\App\Services\FeatureService::FEATURE_ADVANCED_REPORTING))
                <a href="{{ route('queues.reports', $queue) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Reports
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Notifications Area -->
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Queue Stats and Add Customer -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Queue Status Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Queue Status</h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-indigo-600">{{ $waitingEntries->count() }}</div>
                                    <div class="text-sm text-gray-500">Waiting</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-indigo-600">{{ $servingEntries->count() }}</div>
                                    <div class="text-sm text-gray-500">Serving</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-indigo-600">{{ $currentWaitTime }}</div>
                                    <div class="text-sm text-gray-500">Min Wait</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-indigo-600">{{ $recentlyServedEntries->count() }}</div>
                                    <div class="text-sm text-gray-500">Recently Served</div>
                                </div>
                            </div>

                            <!-- Queue Actions -->
                            <div class="mt-6">
                                <form action="{{ route('queues.update', $queue) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $queue->name }}">
                                    <input type="hidden" name="estimated_wait_time" value="{{ $queue->estimated_wait_time }}">

                                    <div class="flex space-x-2">
                                        @if($queue->status === 'active')
                                        <button type="submit" name="status" value="paused" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Pause Queue
                                        </button>
                                        <button type="submit" name="status" value="closed" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Close Queue
                                        </button>
                                        @elseif($queue->status === 'paused')
                                        <button type="submit" name="status" value="active" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Resume Queue
                                        </button>
                                        <button type="submit" name="status" value="closed" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Close Queue
                                        </button>
                                        @else
                                        <button type="submit" name="status" value="active" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Reopen Queue
                                        </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Add Customer Form -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Customer</h3>

                            @if($queue->status !== 'active')
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            Queue is currently {{ $queue->status }}. Activate the queue to add customers.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @elseif($queue->isFull())
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">
                                            Queue is full. Maximum capacity: {{ $queue->max_size }}.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @else
                            <form action="{{ route('queues.entries.store', $queue) }}" method="POST">
                                @csrf

                                <div class="space-y-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <input type="tel" name="phone" id="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <p class="mt-1 text-xs text-gray-500">Optional. Used for notifications.</p>
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <p class="mt-1 text-xs text-gray-500">Optional. Used for notifications.</p>
                                    </div>

                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                        <textarea name="notes" id="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Add to Queue
                                    </button>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Middle Column: Waiting List -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Waiting List</h3>

                            @if($waitingEntries->isEmpty())
                            <p class="text-gray-500 text-sm">No customers waiting.</p>
                            @else
                            <div class="space-y-4">
                                @foreach($waitingEntries as $entry)
                                <div class="border rounded-lg p-4 bg-white shadow-sm hover:shadow-md transition-shadow duration-300">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center">
                                                <span class="text-lg font-medium text-gray-900">{{ $entry->customer->name }}</span>
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    #{{ $entry->position }}
                                                </span>
                                            </div>

                                            @if($entry->customer->phone)
                                            <div class="text-sm text-gray-500 mt-1">
                                                <span>{{ $entry->customer->phone }}</span>
                                            </div>
                                            @endif

                                            @if($entry->estimated_wait)
                                            <div class="text-sm text-gray-500 mt-1">
                                                <span>Est. wait: {{ $entry->estimated_wait }} mins</span>
                                            </div>
                                            @endif

                                            @if($entry->notes)
                                            <div class="text-sm text-gray-500 mt-2 italic">
                                                "{{ $entry->notes }}"
                                            </div>
                                            @endif
                                        </div>

                                        <div class="text-xs text-gray-400">
                                            {{ $entry->created_at->diffForHumans() }}
                                        </div>
                                    </div>

                                    <div class="mt-4 flex space-x-2">
                                        <form action="{{ route('queues.entries.update', ['queue' => $queue, 'entry' => $entry]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="called">
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Call
                                            </button>
                                        </form>

                                        <form action="{{ route('queues.entries.update', ['queue' => $queue, 'entry' => $entry]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="no_show">
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                No-Show
                                            </button>
                                        </form>

                                        @if($canSendSms && $entry->customer->phone)
                                        <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="openSmsModal('{{ $entry->id }}', '{{ $entry->customer->name }}', '{{ $entry->customer->phone }}')">
                                            SMS
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: Currently Serving & Recent -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Currently Serving -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Currently Serving</h3>

                            @if($servingEntries->isEmpty())
                            <p class="text-gray-500 text-sm">No customers being served.</p>
                            @else
                            <div class="space-y-4">
                                @foreach($servingEntries as $entry)
                                <div class="border rounded-lg p-4 {{ $entry->status === 'called' ? 'bg-yellow-50' : 'bg-green-50' }} shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center">
                                                <span class="text-lg font-medium text-gray-900">{{ $entry->customer->name }}</span>
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entry->status === 'called' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ ucfirst($entry->status) }}
                                                </span>
                                            </div>

                                            @if($entry->customer->phone)
                                            <div class="text-sm text-gray-700 mt-1">
                                                <span>{{ $entry->customer->phone }}</span>
                                            </div>
                                            @endif

                                            @if($entry->notes)
                                            <div class="text-sm text-gray-700 mt-2 italic">
                                                "{{ $entry->notes }}"
                                            </div>
                                            @endif
                                        </div>

                                        <div class="text-xs text-gray-600">
                                            @if($entry->called_time)
                                            Called: {{ $entry->called_time->diffForHumans() }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 flex space-x-2">
                                        @if($entry->status === 'called')
                                        <form action="{{ route('queues.entries.update', ['queue' => $queue, 'entry' => $entry]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="serving">
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                Start Serving
                                            </button>
                                        </form>
                                        @endif

                                        <form action="{{ route('queues.entries.update', ['queue' => $queue, 'entry' => $entry]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="served">
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Complete
                                            </button>
                                        </form>

                                        <form action="{{ route('queues.entries.update', ['queue' => $queue, 'entry' => $entry]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="no_show">
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                No-Show
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recently Served -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recently Served</h3>

                            @if($recentlyServedEntries->isEmpty())
                            <p class="text-gray-500 text-sm">No customers served yet.</p>
                            @else
                            <div class="overflow-y-auto max-h-96">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name
                                            </th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Served By
                                            </th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Time
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($recentlyServedEntries as $entry)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $entry->customer->name }}</div>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">
                                                    {{ $entry->staff ? $entry->staff->name : 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                                {{ $entry->served_time ? $entry->served_time->format('H:i') : 'N/A' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Modal -->
    @if($canSendSms)
    <div id="smsModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Send SMS Notification</h3>
            </div>

            <form id="smsForm" action="" method="POST">
                @csrf
                <div class="px-6 py-4">
                    <input type="hidden" id="entryId" name="entryId">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">To:</label>
                        <div id="recipientInfo" class="text-sm text-gray-900"></div>
                    </div>

                    <div>
                        <label for="smsMessage" class="block text-sm font-medium text-gray-700 mb-1">Message:</label>
                        <textarea id="smsMessage" name="message" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-2">
                    <button type="button" onclick="closeSmsModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openSmsModal(entryId, customerName, customerPhone) {
            document.getElementById('entryId').value = entryId;
            document.getElementById('recipientInfo').textContent = customerName + ' (' + customerPhone + ')';
            document.getElementById('smsForm').action = "{{ route('queues.entries.notify-sms', ['queue' => $queue, 'entry' => ':entryId']) }}".replace(':entryId', entryId);

            // Prefill message
            document.getElementById('smsMessage').value = `Hello ${customerName}, your turn is coming up at ${$queue->business->name}. Please prepare to be served soon.`;

            document.getElementById('smsModal').classList.remove('hidden');
        }

        function closeSmsModal() {
            document.getElementById('smsModal').classList.add('hidden');
        }

    </script>
    @endif
</x-app-layout>
