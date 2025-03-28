<!-- resources/views/display/queue-board.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Queue Display - {{ $business->name }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #111827;
            color: white;
        }

        .refresh-indicator {
            position: fixed;
            bottom: 10px;
            right: 10px;
            z-index: 10;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.2;
            }
        }

        .animate-pulse-custom {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .fade-in {
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-indigo-900 py-4 px-6 flex justify-between items-center">
            <div class="flex items-center">
                <h1 class="text-2xl sm:text-3xl font-bold text-white mr-4">{{ $business->name }}</h1>

                @if($locations->count() > 0)
                <div class="hidden sm:block">
                    <form action="{{ route('display.queue-board', $business->slug) }}" method="GET" class="flex space-x-2">
                        <select name="location_id" onchange="this.form.submit()" class="rounded-md bg-indigo-800 text-white border-indigo-700 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ $selectedLocationId == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @endif
            </div>

            <div class="text-xl text-white font-bold" id="clock">
                {{ now()->format('g:i A') }}
            </div>
        </header>

        <main class="container mx-auto py-8 px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Now Serving Section -->
                <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-indigo-800 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Now Serving</h2>
                    </div>

                    <div class="p-6">
                        @forelse($queues as $queue)
                        @if($queue->servingEntries->isNotEmpty())
                        <div class="mb-6 last:mb-0">
                            <h3 class="text-lg font-medium text-indigo-400 mb-4">{{ $queue->name }}</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($queue->servingEntries as $entry)
                                <div class="bg-gray-700 rounded-lg p-4 fade-in">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="text-xl font-bold text-white">
                                            {{ Str::mask($entry->customer->name, '*', 4) }}
                                        </div>
                                        <div class="px-2 py-1 bg-green-800 text-green-100 text-xs rounded-full">
                                            Serving
                                        </div>
                                    </div>

                                    <div class="text-gray-300 text-sm">
                                        @if($entry->staff)
                                        With: {{ $entry->staff->name }}
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @empty
                        <div class="text-center text-gray-400 py-8">
                            <p class="text-lg">No customers currently being served</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Called Customers Section -->
                <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-yellow-700 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Now Calling</h2>
                    </div>

                    <div class="p-6">
                        @php
                        $hasCalledEntries = false;
                        foreach($queues as $queue) {
                        if($queue->calledEntries->isNotEmpty()) {
                        $hasCalledEntries = true;
                        break;
                        }
                        }
                        @endphp

                        @if($hasCalledEntries)
                        @foreach($queues as $queue)
                        @if($queue->calledEntries->isNotEmpty())
                        <div class="mb-6 last:mb-0">
                            <h3 class="text-lg font-medium text-yellow-400 mb-4">{{ $queue->name }}</h3>

                            <div class="space-y-4">
                                @foreach($queue->calledEntries as $entry)
                                <div class="bg-gray-700 rounded-lg p-4 animate-pulse-custom">
                                    <div class="flex justify-between items-center">
                                        <div class="text-xl font-bold text-white">
                                            {{ Str::mask($entry->customer->name, '*', 4) }}
                                        </div>
                                        <div class="text-sm text-gray-300">
                                            Called {{ $entry->called_time->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endforeach
                        @else
                        <div class="text-center text-gray-400 py-8">
                            <p class="text-lg">No customers currently being called</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Queue Summary Section -->
            <div class="mt-8 bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="bg-blue-800 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">Queue Status</h2>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($queues as $queue)
                        <div class="bg-gray-700 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-white mb-2">{{ $queue->name }}</h3>
                            <div class="flex justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Waiting</p>
                                    <p class="text-2xl font-bold text-white">{{ $queue->waitingCount }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Est. Wait</p>
                                    <p class="text-2xl font-bold text-white">
                                        {{ $queue->calculateWaitTime() }} min
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>

        <!-- Refresh Indicator -->
        <div class="refresh-indicator bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
            <span>Auto-refresh in </span>
            <span id="countdown">{{ $refreshInterval }}</span>
            <span>s</span>
        </div>
    </div>

    <script>
        // Update clock
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // convert 0 to 12
            document.getElementById('clock').textContent = `${hours}:${minutes} ${ampm}`;
        }

        // Auto refresh countdown
        let countdown = {
            {
                $refreshInterval
            }
        };

        function updateCountdown() {
            countdown--;
            document.getElementById('countdown').textContent = countdown;

            if (countdown <= 0) {
                location.reload();
            }
        }

        // Initialize
        setInterval(updateClock, 1000);
        setInterval(updateCountdown, 1000);
        updateClock();

    </script>
</body>
</html>
