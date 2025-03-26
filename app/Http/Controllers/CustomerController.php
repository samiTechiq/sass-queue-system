<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
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
     * Display a listing of customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('customers.index');
    }

    /**
     * Display the specified customer.
     *
     * @param  \App\Models\Customer  $customer
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function show(Customer $customer, Request $request)
    {
        // Check if customer belongs to the current business
        if ($customer->business_id !== $request->user()->business_id) {
            abort(403, 'Unauthorized access to customer');
        }

        // Get customer queue history
        $queueEntries = $customer->queueEntries()
            ->with(['queue', 'staff'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get stats
        $stats = [
            'total_visits' => $customer->queueEntries()->count(),
            'served_count' => $customer->queueEntries()->where('status', 'served')->count(),
            'no_show_count' => $customer->queueEntries()->where('status', 'no_show')->count(),
            'cancelled_count' => $customer->queueEntries()->where('status', 'cancelled')->count(),
            'average_wait_time' => $this->calculateAverageWaitTime($customer),
        ];

        return view('customers.show', [
            'customer' => $customer,
            'queueEntries' => $queueEntries,
            'stats' => $stats,
        ]);
    }

    /**
     * Calculate average wait time for a customer.
     *
     * @param  \App\Models\Customer  $customer
     * @return int
     */
    private function calculateAverageWaitTime(Customer $customer)
    {
        $entries = $customer->queueEntries()
            ->where('status', 'served')
            ->whereNotNull('served_time')
            ->get();

        if ($entries->isEmpty()) {
            return 0;
        }

        $totalWaitTime = 0;
        $count = 0;

        foreach ($entries as $entry) {
            $joinTime = $entry->created_at;
            $servedTime = $entry->served_time;

            if ($joinTime && $servedTime) {
                $waitTimeMinutes = $joinTime->diffInMinutes($servedTime);
                $totalWaitTime += $waitTimeMinutes;
                $count++;
            }
        }

        return $count > 0 ? round($totalWaitTime / $count) : 0;
    }
}
