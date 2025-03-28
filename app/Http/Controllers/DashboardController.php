<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Queue;
use App\Models\QueueEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the appropriate dashboard based on user role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Redirect to the appropriate dashboard based on user role
        if ($user->isAdmin()) {
            return $this->adminDashboard($request);
        }

        if ($user->isBusinessAdmin()) {
            return $this->businessDashboard($request);
        }

        // Default to staff dashboard
        return $this->staffDashboard($request);
    }

    /**
     * Show the admin dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    protected function adminDashboard(Request $request)
    {
        // Get system-wide statistics
        $stats = [
            'total_businesses' => Business::count(),
            'total_users' => User::count(),
            'active_businesses' => Business::whereHas('subscriptions', function ($query) {
                $query->whereIn('status', ['active', 'trialing'])
                    ->where(function ($q) {
                        $q->whereNull('ends_at')
                            ->orWhere('ends_at', '>', now());
                    });
            })->count(),
            'businesses_today' => Business::whereDate('created_at', Carbon::today())->count(),
            'total_queues' => Queue::count(),
            'active_queues' => Queue::where('status', 'active')->count(),
        ];

        // Get recent businesses
        $recentBusinesses = Business::with('subscriptions')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get subscription stats
        $subscriptionStats = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select('plans.name', DB::raw('count(*) as total'))
            ->whereIn('subscriptions.status', ['active', 'trialing'])
            ->groupBy('plans.name')
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentBusinesses' => $recentBusinesses,
            'subscriptionStats' => $subscriptionStats,
        ]);
    }

    /**
     * Show the business admin dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    protected function businessDashboard(Request $request)
    {
        $user = $request->user();
        $business = $user->business;
        $now = Carbon::now();

        // Check if the business has an active subscription
        $subscription = $business->activeSubscription();

        if (!$subscription) {
            // No active subscription, redirect to subscription page
            return redirect()->route('subscription.required')
                ->with('warning', 'Your business needs an active subscription to continue.');
        }

        // Get business statistics
        $stats = [
            'total_queues' => $business->queues()->count(),
            'active_queues' => $business->queues()->where('status', 'active')->count(),
            'total_staff' => $business->users()->count(),
            'customers_today' => QueueEntry::whereHas('queue', function ($query) use ($business) {
                $query->where('business_id', $business->id);
            })->whereDate('created_at', Carbon::today())->count(),
            'customers_waiting' => QueueEntry::whereHas('queue', function ($query) use ($business) {
                $query->where('business_id', $business->id);
            })->where('status', 'waiting')->count(),
            'avg_wait_time' => $this->calculateAverageWaitTime($business),
        ];

        // Get active queues with customer counts
        $activeQueues = $business->queues()
            ->where('status', 'active')
            ->withCount(['entries as waiting_count' => function ($query) {
                $query->where('status', 'waiting');
            }])
            ->withCount(['entries as served_today_count' => function ($query) {
                $query->where('status', 'served')
                    ->whereDate('updated_at', Carbon::today());
            }])
            ->get();

        // Get recent customers
        $recentCustomers = QueueEntry::with(['queue', 'customer'])
            ->whereHas('queue', function ($query) use ($business) {
                $query->where('business_id', $business->id);
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get subscription details
        $plan = $subscription->plan;
        $subscriptionEndsAt = $subscription->ends_at;
        $isTrialing = $subscription->onTrial();
        $trialEndsAt = $subscription->trial_ends_at;

        // Feature usage statistics
        // $featureUsage = $business->getFeatures();

        return view('business.dashboard', [
            'business' => $business,
            'stats' => $stats,
            'activeQueues' => $activeQueues,
            'recentCustomers' => $recentCustomers,
            'subscription' => $subscription,
            'plan' => $plan,
            'subscriptionEndsAt' => $subscriptionEndsAt,
            'isTrialing' => $isTrialing,
            'trialEndsAt' => $trialEndsAt,
            // 'featureUsage' => $featureUsage,
        ]);
    }

    /**
     * Show the staff dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    protected function staffDashboard(Request $request)
    {
        $user = $request->user();
        $business = $user->business;

        // Check if the business has an active subscription
        $subscription = $business->activeSubscription();

        if (!$subscription) {
            // No active subscription, show message
            return redirect()->route('subscription.required')
                ->with('warning', 'Your business does not have an active subscription. Please contact your administrator.');
        }

        // Get queues this staff member has access to
        $queues = $business->queues()
            ->where('status', 'active')
            ->withCount(['entries as waiting_count' => function ($query) {
                $query->where('status', 'waiting');
            }])
            ->get();

        // Get recent activity (customers served by this staff)
        $recentActivity = QueueEntry::with(['queue', 'customer'])
            ->whereHas('queue', function ($query) use ($business) {
                $query->where('business_id', $business->id);
            })
            ->where('served_by', $user->id)
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('staff.dashboard', [
            'queues' => $queues,
            'recentActivity' => $recentActivity,
            'business' => $business,
            'user' => $user,
        ]);
    }

    /**
     * Calculate the average wait time for a business.
     *
     * @param  \App\Models\Business  $business
     * @return int
     */
    protected function calculateAverageWaitTime(Business $business)
    {
        // Calculate average wait time over the last 7 days
        $entries = QueueEntry::whereHas('queue', function ($query) use ($business) {
            $query->where('business_id', $business->id);
        })
            ->where('status', 'served')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->whereNotNull('served_time')
            ->get();

        if ($entries->isEmpty()) {
            return 0;
        }

        $totalWaitTime = 0;
        $count = 0;

        foreach ($entries as $entry) {
            $joinTime = new Carbon($entry->created_at);
            $servedTime = new Carbon($entry->served_time);
            $waitTimeMinutes = $joinTime->diffInMinutes($servedTime);

            $totalWaitTime += $waitTimeMinutes;
            $count++;
        }

        return $count > 0 ? intval($totalWaitTime / $count) : 0;
    }
}