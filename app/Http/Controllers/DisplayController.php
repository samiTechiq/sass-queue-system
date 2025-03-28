<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DisplayController extends Controller
{
    /**
     * Show queue display board.
     *
     * @param  string  $businessSlug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showQueueBoard($businessSlug, Request $request)
    {
        // Find the business by slug
        $business = Business::where('slug', $businessSlug)->firstOrFail();

        // Check if display access is enabled for the business
        if (!$business->getSettings('public_display_enabled', true)) {
            abort(403, 'Public display is not enabled for this business');
        }

        // Get active queues for this business
        $queues = $business->queues()
            ->where('status', 'active')
            ->when($request->input('location_id'), function ($query, $locationId) {
                return $query->where('location_id', $locationId);
            })
            ->get();

        // Get called and serving entries for each queue
        foreach ($queues as $queue) {
            $queue->calledEntries = $queue->entries()
                ->where('status', 'called')
                ->with('customer')
                ->orderBy('called_time', 'desc')
                ->take(5)
                ->get();

            $queue->servingEntries = $queue->entries()
                ->where('status', 'serving')
                ->with(['customer', 'staff'])
                ->orderBy('updated_at', 'desc')
                ->take(3)
                ->get();

            $queue->waitingCount = $queue->entries()
                ->where('status', 'waiting')
                ->count();
        }

        // Get business locations
        $locations = $business->locations;

        return view('display.queue-board', [
            'business' => $business,
            'queues' => $queues,
            'locations' => $locations,
            'selectedLocationId' => $request->input('location_id'),
            'refreshInterval' => $business->getSettings('display_refresh_interval', 30), // seconds
        ]);
    }

    /**
     * Show self-service kiosk.
     *
     * @param  string  $businessSlug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showKiosk($businessSlug, Request $request)
    {
        // Find the business by slug
        $business = Business::where('slug', $businessSlug)->firstOrFail();

        // Check if kiosk access is enabled for the business
        if (!$business->getSettings('kiosk_enabled', true)) {
            abort(403, 'Self-service kiosk is not enabled for this business');
        }

        // Get active queues for this business
        $queues = $business->queues()
            ->where('status', 'active')
            ->when($request->input('location_id'), function ($query, $locationId) {
                return $query->where('location_id', $locationId);
            })
            ->get();

        // Check if there are full queues
        foreach ($queues as $queue) {
            $queue->isFull = $queue->isFull();
            $queue->waitingCount = $queue->entries()->where('status', 'waiting')->count();
            $queue->estimatedWaitTime = $queue->calculateWaitTime();
        }

        // Get business locations
        $locations = $business->locations;

        return view('display.kiosk', [
            'business' => $business,
            'queues' => $queues,
            'locations' => $locations,
            'selectedLocationId' => $request->input('location_id'),
        ]);
    }

    /**
     * Show customer ticket.
     *
     * @param  string  $businessSlug
     * @param  string  $ticketId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showTicket($businessSlug, $ticketId, Request $request)
    {
        // Find the business by slug
        $business = Business::where('slug', $businessSlug)->firstOrFail();

        // Decode the ticket ID (it should be encoded as base64 in URL)
        $decodedId = intval(base64_decode($ticketId));

        // Find the entry
        $entry = $business->queueEntries()
            ->with(['queue', 'customer'])
            ->findOrFail($decodedId);

        // Calculate position and wait time
        $position = null;
        $waitTime = null;

        if ($entry->status === 'waiting') {
            $position = $entry->position;
            $waitTime = $entry->estimated_wait;

            // If position is not set, calculate it
            if (!$position) {
                $position = $entry->queue->waitingEntries()
                    ->where('created_at', '<', $entry->created_at)
                    ->count() + 1;
            }

            // If wait time is not set, calculate it
            if (!$waitTime) {
                $waitTime = $position * ($entry->queue->estimated_wait_time ?? 5);
            }
        }

        return view('display.ticket', [
            'business' => $business,
            'entry' => $entry,
            'position' => $position,
            'waitTime' => $waitTime,
        ]);
    }

    /**
     * Add customer to queue from kiosk.
     *
     * @param  string  $businessSlug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToQueue($businessSlug, Request $request)
    {
        // Find the business by slug
        $business = Business::where('slug', $businessSlug)->firstOrFail();

        // Check if kiosk access is enabled for the business
        if (!$business->getSettings('kiosk_enabled', true)) {
            abort(403, 'Self-service kiosk is not enabled for this business');
        }

        // Validate the request
        $validated = $request->validate([
            'queue_id' => 'required|exists:queues,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        // Verify the queue belongs to this business
        $queue = Queue::where('id', $validated['queue_id'])
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Check if queue is full
        if ($queue->isFull()) {
            return back()->with('error', 'Sorry, this queue is currently full. Please try again later or choose another queue.');
        }

        // Create or find the customer
        $customer = null;
        if (!empty($validated['phone'])) {
            // Try to find existing customer by phone
            $customer = $business->customers()
                ->where('phone', $validated['phone'])
                ->first();
        }

        if (!$customer) {
            // Create new customer
            $customer = $business->customers()->create([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
            ]);
        } else {
            // Update existing customer information
            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $customer->email,
            ]);
        }

        // Calculate position and estimated wait
        $position = $queue->waitingEntries()->count() + 1;
        $estimatedWait = $position * ($queue->estimated_wait_time ?? 5);

        // Add customer to queue
        $entry = $queue->entries()->create([
            'customer_id' => $customer->id,
            'status' => 'waiting',
            'position' => $position,
            'estimated_wait' => $estimatedWait,
        ]);

        // Generate a ticket ID for the URL
        $ticketId = base64_encode($entry->id);

        // Redirect to ticket page
        return redirect()->route('display.ticket', [
            'businessSlug' => $businessSlug,
            'ticketId' => $ticketId,
        ])->with('success', 'You have been added to the queue!');
    }
}