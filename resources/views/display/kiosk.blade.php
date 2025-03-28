<!-- resources/views/display/kiosk.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Self-Service Kiosk - {{ $business->name }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>
</head>
<body class="antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-indigo-600 py-6 shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-white">{{ $business->name }}</h1>

                    <div class="flex items-center">
                        @if($locations->count() > 0)
                        <form action="{{ route('display.kiosk', $business->slug) }}" method="GET" class="mr-4">
                            <select name="location_id" onchange="this.form.submit()" class="rounded-md bg-indigo-500 text-white border-indigo-400 focus:border-indigo-300 focus:ring-indigo-300">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $selectedLocationId == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                        @endif

                        <div class="text-xl text-white" id="clock">
                            {{ now()->format('g:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            <div class="text-center mb-8">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Welcome to our self-service kiosk
                </h2>
                <p class="mt-3 max-w-md mx-auto text-xl text-gray-500 sm:mt-4">
                    Please select a queue to join below
                </p>
            </div>

            <div x-data="{ step: 1, selectedQueue: null, queueName: '' }">
                <!-- Step 1: Select a Queue -->
                <div x-show="step === 1" class="fade-in">
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            @foreach($queues as $queue)
                            <li>
                                <div class="px-4 py-5 sm:px-6 hover:bg-gray-50 {{ $queue->isFull ? 'opacity-50' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900">{{ $queue->name }}</h3>
                                            @if($queue->description)
                                            <p class="mt-1 text-sm text-gray-500">{{ $queue->description }}</p>
                                            @endif
                                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                <span>Estimated wait: {{ $queue->estimatedWaitTime }} minutes</span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-end">
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium {{ $queue->waitingCount > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $queue->waitingCount }} waiting
                                            </span>

                                            @if(!$queue->isFull)
                                            <button @click="step = 2; selectedQueue = {{ $queue->id }}; queueName = '{{ $queue->name }}'" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Join Queue
                                            </button>
                                            @else
                                            <span class="mt-2 inline-flex items-center px-3 py-0.5 rounded-md text-sm font-medium bg-red-100 text-red-800">
                                                Queue Full
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-4 text-right">
                        <a href="{{ route('display.queue-board', $business->slug) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            View Queue Status
                        </a>
                    </div>
                </div>

                <!-- Step 2: Enter Customer Details -->
                <div x-show="step === 2" class="fade-in">
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Join <span x-text="queueName"></span>
                            </h3>
                            <div class="mt-2 max-w-xl text-sm text-gray-500">
                                <p>Please provide your details to join the queue.</p>
                            </div>

                            <form action="{{ route('display.add-to-queue', $business->slug) }}" method="POST" class="mt-5">
                                @csrf
                                <input type="hidden" name="queue_id" x-bind:value="selectedQueue">

                                <div class="space-y-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">
                                            Your Name
                                        </label>
                                        <input type="text" name="name" id="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">
                                            Phone Number (for notifications)
                                        </label>
                                        <input type="tel" name="phone" id="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-xs text-gray-500">
                                            We'll use this to notify you when it's your turn.
                                        </p>
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">
                                            Email (optional)
                                        </label>
                                        <input type="email" name="email" id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button type="button" @click="step = 1" class="mr-3 bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Back
                                    </button>
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Join Queue
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-gray-500 text-sm">
                    &copy; {{ date('Y') }} {{ $business->name }}. All rights reserved.
                    <div class="mt-2">
                        <a href="{{ url('/') }}" class="text-indigo-600 hover:text-indigo-500">Return to main site</a>
                    </div>
                </div>
            </div>
        </footer>
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

        // Initialize
        setInterval(updateClock, 60000); // Update every minute
        updateClock();

    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
