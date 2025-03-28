<!-- resources/views/display/ticket.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Your Ticket - {{ $business->name }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none;
            }

            .print-only {
                display: block;
            }

            body {
                padding: 0;
                margin: 0;
            }
        }

        .print-only {
            display: none;
        }

    </style>
</head>
<body class="antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Header - Only visible on screen -->
        <header class="bg-indigo-600 py-6 shadow-md no-print">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-white">{{ $business->name }}</h1>

                    <div class="text-xl text-white" id="clock">
                        {{ now()->format('g:i A') }}
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Ticket Information -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-900">Your Queue Ticket</h2>
                        <span class="text-sm text-gray-500">{{ now()->format('F j, Y') }}</span>
                    </div>
                </div>

                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <!-- Business Information -->
                    <div class="mb-6 text-center">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $business->name }}</h3>
                        @if($business->address)
                        <p class="text-gray-500">{{ $business->address }}</p>
                        @endif
                    </div>

                    <!-- Ticket Details -->
                    <div class="flex flex-col items-center mb-8">
                        <div class="text-center mb-2">
                            <p class="text-sm text-gray-500">Queue</p>
                            <p class="text-xl font-medium text-gray-900">{{ $entry->queue->name }}</p>
                        </div>

                        <div class="text-center mb-4">
                            <p class="text-sm text-gray-500">Ticket Number</p>
                            <p class="text-4xl font-bold text-indigo-600">{{ sprintf('%03d', $entry->id) }}</p>
                        </div>

                        @if($entry->status === 'waiting')
                        <div class="mt-2 flex space-x-8 text-center">
                            <div>
                                <p class="text-sm text-gray-500">Position</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $position }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Estimated Wait</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $waitTime }} min</p>
                            </div>
                        </div>
                        @else
                        <div class="mt-2 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $entry->status === 'called' ? 'bg-yellow-100 text-yellow-800' :
                                       ($entry->status === 'serving' ? 'bg-green-100 text-green-800' :
                                       ($entry->status === 'served' ? 'bg-blue-100 text-blue-800' :
                                       'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($entry->status) }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Customer Information -->
                    <div class="border-t border-gray-200 pt-6 text-center">
                        <p class="text-sm text-gray-500">Customer</p>
                        <p class="text-lg font-medium text-gray-900">{{ $entry->customer->name }}</p>

                        @if($entry->customer->phone)
                        <p class="text-gray-500">{{ $entry->customer->phone }}</p>
                        @endif

                        <p class="mt-4 text-sm text-gray-500">
                            Added to queue on {{ $entry->created_at->format('F j, Y \a\t g:i A') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            @if($entry->status === 'waiting')
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Please keep this page open to track your position in the queue. You will be notified when it's your turn.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($entry->status === 'called')
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 animate-pulse">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <span class="font-medium">It's your turn!</span> Please proceed to the counter.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($entry->status === 'serving')
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <span class="font-medium">Currently being served.</span> Thank you for your patience.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($entry->status === 'served')
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <span class="font-medium">You have been served.</span> Thank you for your visit.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons - Only visible on screen -->
            <div class="flex justify-center space-x-4 no-print">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                    </svg>
                    Print Ticket
                </button>

                <a href="{{ route('display.queue-board', $business->slug) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                    View Queue Status
                </a>
            </div>

            <!-- QR Code for the ticket (optional) -->
            <div class="mt-8 flex justify-center">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-2">Scan to access your ticket again</p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('display.ticket', ['businessSlug' => $business->slug, 'ticketId' => base64_encode($entry->id)])) }}" alt="QR Code" class="mx-auto h-32 w-32">
                </div>
            </div>
        </main>

        <!-- Footer - Only visible on screen -->
        <footer class="bg-white no-print">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-gray-500 text-sm">
                    &copy; {{ date('Y') }} {{ $business->name }}. All rights reserved.
                    <div class="mt-2">
                        <a href="{{ route('display.kiosk', $business->slug) }}" class="text-indigo-600 hover:text-indigo-500">Back to Kiosk</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Print version - Only visible when printing -->
        <div class="print-only mt-8">
            <div class="text-center">
                <h2 class="text-xl font-bold">{{ $business->name }}</h2>
                <p class="text-sm">Queue: {{ $entry->queue->name }}</p>
                <p class="text-sm">Date: {{ now()->format('F j, Y') }}</p>
                <p class="text-sm">Time: {{ now()->format('g:i A') }}</p>

                <div class="my-4">
                    <p class="text-sm">Ticket Number</p>
                    <p class="text-4xl font-bold">{{ sprintf('%03d', $entry->id) }}</p>
                </div>

                <div class="my-4">
                    <p class="text-sm">Customer</p>
                    <p class="text-lg">{{ $entry->customer->name }}</p>
                </div>

                @if($entry->status === 'waiting')
                <div class="my-4">
                    <p class="text-sm">Position: {{ $position }}</p>
                    <p class="text-sm">Estimated Wait: {{ $waitTime }} min</p>
                </div>
                @endif

                <div class="mt-4 border-t pt-4">
                    <p class="text-sm">Thank you for your patience!</p>
                </div>
            </div>
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

        // Auto-refresh for waiting entries
        function setupAutoRefresh() {
            const status = "{{ $entry->status }}";
            if (status === 'waiting' || status === 'called') {
                setTimeout(() => {
                    window.location.reload();
                }, 30000); // Refresh every 30 seconds
            }
        }

        // Initialize
        updateClock();
        setInterval(updateClock, 60000); // Update every minute
        setupAutoRefresh();

    </script>
</body>
</html>
