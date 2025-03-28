<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\FeatureService;

class QueueController extends Controller
{

    /**
     * Send notifications
     */
    private function sendNotifications(QueueEntry $entry)
    {
        $business = $entry->queue->business;

        // Check if the business has access to SMS notifications
        if ($business->hasFeature(FeatureService::FEATURE_SMS_NOTIFICATIONS)) {
            // Send SMS notification
            $this->sendSmsNotification($entry);
        }

        // Check if the business has access to email notifications
        if ($business->hasFeature(FeatureService::FEATURE_EMAIL_NOTIFICATIONS)) {
            // Send email notification
            $this->sendEmailNotification($entry);
        }
    }


    /************** */
    /**
     * Display a listing of queues.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $business = $user->business;

        // Get active queues for this business
        $activeQueues = $business->queues()->where('status', 'active')
            ->withCount(['entries as waiting_count' => function ($query) {
                $query->where('status', 'waiting');
            }])
            ->withCount(['entries as serving_count' => function ($query) {
                $query->whereIn('status', ['called', 'serving']);
            }])
            ->withCount(['entries as served_today_count' => function ($query) {
                $query->where('status', 'served')
                    ->whereDate('served_time', today());
            }])
            ->get();

        // Get other queues (paused, closed)
        $otherQueues = $business->queues()->where('status', '!=', 'active')->get();

        // Check subscription for creating more queues
        $canCreateQueue = true;
        $subscription = $business->activeSubscription();

        if ($subscription) {
            $maxQueues = $subscription->getQuota(FeatureService::FEATURE_MAX_QUEUES);
            $currentQueueCount = $business->queues()->count();

            if ($maxQueues !== null) { // null means unlimited
                $canCreateQueue = $currentQueueCount < $maxQueues;
            }
        } else {
            // No active subscription
            $canCreateQueue = false;
        }

        return view('queues.index', [
            'activeQueues' => $activeQueues,
            'otherQueues' => $otherQueues,
            'canCreateQueue' => $canCreateQueue,
            'business' => $business,
        ]);
    }

    /**
     * Show the form for creating a new queue.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $business = $user->business;
        // Get business locations for dropdown
        $locations = $business->locations;

        return view('queues.create', []);


        // if (!$business->canUseFeature(FeatureService::FEATURE_MAX_QUEUES, 1)) {
        //     return redirect()->route('queues.index')
        //         ->with('error', 'You have reached the maximum number of queues allowed by your subscription plan. Please upgrade to create more queues.');
        // }
    }

    /**
     * Store a newly created queue.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $business = $user->business;

        // Check if the business can create more queues (feature limitation)
        if (!$business->canUseFeature(FeatureService::FEATURE_MAX_QUEUES, 1)) {
            return redirect()->route('queues.index')
                ->with('error', 'You have reached the maximum number of queues allowed by your subscription plan. Please upgrade to create more queues.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_wait_time' => 'nullable|integer|min:0',
            'max_size' => 'nullable|integer|min:0',
            'location_id' => 'nullable|exists:business_locations,id',
            'settings' => 'nullable|array',
        ]);

        // Create the queue
        $queue = Queue::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'estimated_wait_time' => $validated['estimated_wait_time'] ?? 5, // Default 5 minutes
            'max_size' => $validated['max_size'] ?? null,
            'location_id' => $validated['location_id'] ?? null,
            'settings' => $validated['settings'] ?? null,
            'status' => 'active',
        ]);

        // Increment the queue usage counter
        $business->useFeature(FeatureService::FEATURE_MAX_QUEUES);

        return redirect()->route('queues.show', $queue)
            ->with('success', 'Queue created successfully.');
    }

    /**
     * Display the specified queue.
     *
     * @param  \App\Models\Queue  $queue
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function show(Queue $queue, Request $request)
    {
        $this->authorize('view', $queue);

        $user = $request->user();
        $business = $user->business;

        // Get waiting entries
        $waitingEntries = $queue->waitingEntries()
            ->with('customer')
            ->get();

        // Get currently serving entries
        $servingEntries = $queue->servingEntries()
            ->with(['customer', 'staff'])
            ->get();

        // Get recently served entries (last 10)
        $recentlyServedEntries = $queue->servedEntries()
            ->with(['customer', 'staff'])
            ->orderBy('served_time', 'desc')
            ->take(10)
            ->get();

        // Calculate current wait time
        $currentWaitTime = $queue->calculateWaitTime();

        // Check feature access
        $canSendSms = $business->hasFeature(FeatureService::FEATURE_SMS_NOTIFICATIONS);
        $canSendEmail = $business->hasFeature(FeatureService::FEATURE_EMAIL_NOTIFICATIONS);

        return view('queues.show', [
            'queue' => $queue,
            'waitingEntries' => $waitingEntries,
            'servingEntries' => $servingEntries,
            'recentlyServedEntries' => $recentlyServedEntries,
            'currentWaitTime' => $currentWaitTime,
            'canSendSms' => $canSendSms,
            'canSendEmail' => $canSendEmail,
            'business' => $business,
        ]);
    }

    /**
     * Show the form for editing the specified queue.
     *
     * @param  \App\Models\Queue  $queue
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function edit(Queue $queue, Request $request)
    {
        $this->authorize('update', $queue);

        $user = $request->user();
        $business = $user->business;

        // Get business locations for dropdown
        $locations = $business->locations;

        return view('queues.edit', [
            'queue' => $queue,
            'locations' => $locations,
            'business' => $business,
        ]);
    }

    /**
     * Update the specified queue.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Queue $queue)
    {
        $this->authorize('update', $queue);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,paused,closed',
            'estimated_wait_time' => 'nullable|integer|min:0',
            'max_size' => 'nullable|integer|min:0',
            'location_id' => 'nullable|exists:business_locations,id',
            'settings' => 'nullable|array',
        ]);

        // Update the queue
        $queue->update($validated);

        return redirect()->route('queues.show', $queue)
            ->with('success', 'Queue updated successfully.');
    }

    /**
     * Remove the specified queue.
     *
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Queue $queue)
    {
        $this->authorize('delete', $queue);

        // Check if there are active entries
        if ($queue->entries()->whereIn('status', ['waiting', 'called', 'serving'])->exists()) {
            return redirect()->route('queues.show', $queue)
                ->with('error', 'Cannot delete queue with active entries. Please serve or cancel all entries first.');
        }

        $queue->delete();

        return redirect()->route('queues.index')
            ->with('success', 'Queue deleted successfully.');
    }

    /**
     * Generate queue report
     *
     * @param  \App\Models\Queue  $queue
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function generateReport(Queue $queue, Request $request)
    {
        $this->authorize('view', $queue);

        $business = $request->user()->business;

        // Check if the business has access to advanced reporting
        if (!$business->hasFeature(FeatureService::FEATURE_ADVANCED_REPORTING)) {
            return redirect()->route('queues.show', $queue)
                ->with('error', 'Advanced reporting is not available on your current plan. Please upgrade to access this feature.');
        }

        // Get date range
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->subDays(7);

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        // Generate the report
        $report = $this->generateQueueReport($queue, $startDate, $endDate);

        return view('queues.report', [
            'queue' => $queue,
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Generate queue report data
     *
     * @param  \App\Models\Queue  $queue
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    protected function generateQueueReport(Queue $queue, Carbon $startDate, Carbon $endDate)
    {
        // Get all entries within date range
        $entries = $queue->entries()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Get served entries
        $servedEntries = $entries->where('status', 'served');

        // Get cancelled entries
        $cancelledEntries = $entries->where('status', 'cancelled');

        // Get no-show entries
        $noShowEntries = $entries->where('status', 'no_show');

        // Calculate wait times for served entries
        $waitTimes = [];
        $totalWaitTime = 0;

        foreach ($servedEntries as $entry) {
            if ($entry->served_time && $entry->created_at) {
                $waitTime = $entry->created_at->diffInMinutes($entry->served_time);
                $waitTimes[] = $waitTime;
                $totalWaitTime += $waitTime;
            }
        }

        // Calculate average wait time
        $averageWaitTime = count($waitTimes) > 0 ? round($totalWaitTime / count($waitTimes)) : 0;

        // Calculate busiest hours
        $hourCounts = [];

        foreach ($entries as $entry) {
            $hour = $entry->created_at->format('H');
            if (!isset($hourCounts[$hour])) {
                $hourCounts[$hour] = 0;
            }
            $hourCounts[$hour]++;
        }

        arsort($hourCounts);

        // Compile report data
        return [
            'total_entries' => $entries->count(),
            'served_entries' => $servedEntries->count(),
            'cancelled_entries' => $cancelledEntries->count(),
            'no_show_entries' => $noShowEntries->count(),
            'average_wait_time' => $averageWaitTime,
            'max_wait_time' => count($waitTimes) > 0 ? max($waitTimes) : 0,
            'min_wait_time' => count($waitTimes) > 0 ? min($waitTimes) : 0,
            'busy_hours' => $hourCounts,
            'daily_counts' => $this->getDailyEntryCounts($entries, $startDate, $endDate),
            'staff_stats' => $this->getStaffStats($servedEntries),
        ];
    }

    /**
     * Get daily entry counts
     *
     * @param  \Illuminate\Support\Collection  $entries
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    protected function getDailyEntryCounts($entries, Carbon $startDate, Carbon $endDate)
    {
        $dailyCounts = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $dailyCounts[$dateString] = 0;
            $currentDate->addDay();
        }

        foreach ($entries as $entry) {
            $dateString = $entry->created_at->format('Y-m-d');
            if (isset($dailyCounts[$dateString])) {
                $dailyCounts[$dateString]++;
            }
        }

        return $dailyCounts;
    }

    /**
     * Get staff performance stats
     *
     * @param  \Illuminate\Support\Collection  $servedEntries
     * @return array
     */
    protected function getStaffStats($servedEntries)
    {
        $staffStats = [];

        foreach ($servedEntries as $entry) {
            if ($entry->served_by) {
                $staffId = $entry->served_by;

                if (!isset($staffStats[$staffId])) {
                    $staffStats[$staffId] = [
                        'name' => $entry->staff ? $entry->staff->name : 'Unknown',
                        'count' => 0,
                        'total_time' => 0,
                    ];
                }

                $staffStats[$staffId]['count']++;

                if ($entry->served_time && $entry->called_time) {
                    $serviceTime = $entry->called_time->diffInMinutes($entry->served_time);
                    $staffStats[$staffId]['total_time'] += $serviceTime;
                }
            }
        }

        // Calculate average service time
        foreach ($staffStats as &$stats) {
            $stats['average_time'] = $stats['count'] > 0
                ? round($stats['total_time'] / $stats['count'])
                : 0;
        }

        return $staffStats;
    }
}
