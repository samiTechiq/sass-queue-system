<!-- resources/views/queues/report.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Queue Report') }} - {{ $queue->name }}
            </h2>
            <a href="{{ route('queues.show', $queue) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Back to Queue
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Date Range Selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('queues.reports', $queue) }}" class="flex flex-col sm:flex-row sm:items-end space-y-4 sm:space-y-0 sm:space-x-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Update Report
                            </button>
                        </div>

                        <div class="ml-auto hidden sm:block">
                            <span class="text-sm text-gray-500">
                                Showing data from {{ $startDate->format('M j, Y') }} to {{ $endDate->format('M j, Y') }}
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Entries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-md bg-indigo-500 p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Customers
                                    </dt>
                                    <dd class="text-3xl font-semibold text-gray-900">
                                        {{ $report['total_entries'] }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Served Entries -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-md bg-green-500 p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Customers Served
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-semibold text-gray-900">
                                            {{ $report['served_entries'] }}
                                        </div>
                                        @if($report['total_entries'] > 0)
                                        <div class="ml-2 text-sm text-gray-600">
                                            ({{ round(($report['served_entries'] / $report['total_entries']) * 100) }}%)
                                        </div>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No-Shows -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-md bg-red-500 p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        No-Shows
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-semibold text-gray-900">
                                            {{ $report['no_show_entries'] }}
                                        </div>
                                        @if($report['total_entries'] > 0)
                                        <div class="ml-2 text-sm text-gray-600">
                                            ({{ round(($report['no_show_entries'] / $report['total_entries']) * 100) }}%)
                                        </div>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Wait Time -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-md bg-yellow-500 p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Avg. Wait Time
                                    </dt>
                                    <dd class="text-3xl font-semibold text-gray-900">
                                        {{ $report['average_wait_time'] }} min
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Daily Activity Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Activity</h3>

                        <div class="bg-gray-50 p-4 rounded-lg overflow-x-auto">
                            <canvas id="dailyActivityChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Busiest Hours -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Busiest Hours</h3>

                        <div class="bg-gray-50 p-4 rounded-lg overflow-x-auto">
                            <canvas id="busiestHoursChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Staff Performance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Staff Performance</h3>

                        @if(count($report['staff_stats']) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Staff Member
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Customers Served
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Avg. Service Time
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($report['staff_stats'] as $staffId => $stats)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $stats['name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $stats['count'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $stats['average_time'] }} min
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-gray-500 text-sm">No staff performance data available for this period.</p>
                        @endif
                    </div>
                </div>

                <!-- Wait Time Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Wait Time Statistics</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="text-lg font-bold text-indigo-600">{{ $report['average_wait_time'] }}</div>
                                <div class="text-sm text-gray-500">Average (minutes)</div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="text-lg font-bold text-indigo-600">{{ $report['min_wait_time'] }}</div>
                                <div class="text-sm text-gray-500">Minimum (minutes)</div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="text-lg font-bold text-indigo-600">{{ $report['max_wait_time'] }}</div>
                                <div class="text-sm text-gray-500">Maximum (minutes)</div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-sm text-gray-500">
                                Wait time is calculated from when a customer joins the queue until they are served.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Daily Activity Chart
            const dailyActivityCtx = document.getElementById('dailyActivityChart').getContext('2d');
            const dailyActivityChart = new Chart(dailyActivityCtx, {
                type: 'line'
                , data: {
                    labels: {
                        !!json_encode(array_keys($report['daily_counts'])) !!
                    }
                    , datasets: [{
                        label: 'Customers'
                        , data: {
                            !!json_encode(array_values($report['daily_counts'])) !!
                        }
                        , backgroundColor: 'rgba(79, 70, 229, 0.2)'
                        , borderColor: 'rgba(79, 70, 229, 1)'
                        , borderWidth: 2
                        , tension: 0.1
                    }]
                }
                , options: {
                    scales: {
                        y: {
                            beginAtZero: true
                            , ticks: {
                                precision: 0
                            }
                        }
                    }
                    , plugins: {
                        legend: {
                            display: false
                        }
                    }
                    , responsive: true
                    , maintainAspectRatio: false
                }
            });

            // Busiest Hours Chart
            const hours = {
                !!json_encode(array_keys($report['busy_hours'])) !!
            };
            const hourCounts = {
                !!json_encode(array_values($report['busy_hours'])) !!
            };

            // Format hours for display (e.g. "9 AM", "2 PM")
            const formattedHours = hours.map(hour => {
                const h = parseInt(hour);
                return h === 0 ? '12 AM' :
                    h < 12 ? `${h} AM` :
                    h === 12 ? '12 PM' :
                    `${h - 12} PM`;
            });

            const busiestHoursCtx = document.getElementById('busiestHoursChart').getContext('2d');
            const busiestHoursChart = new Chart(busiestHoursCtx, {
                type: 'bar'
                , data: {
                    labels: formattedHours
                    , datasets: [{
                        label: 'Customers'
                        , data: hourCounts
                        , backgroundColor: 'rgba(59, 130, 246, 0.6)'
                        , borderColor: 'rgba(59, 130, 246, 1)'
                        , borderWidth: 1
                    }]
                }
                , options: {
                    scales: {
                        y: {
                            beginAtZero: true
                            , ticks: {
                                precision: 0
                            }
                        }
                    }
                    , plugins: {
                        legend: {
                            display: false
                        }
                    }
                    , responsive: true
                    , maintainAspectRatio: false
                }
            });
        });

    </script>
    @endpush
</x-app-layout>
