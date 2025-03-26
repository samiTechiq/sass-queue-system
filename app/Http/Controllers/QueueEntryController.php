<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Customer;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use App\Services\FeatureService;
use App\Models\QueueNotification;
use App\Services\NotificationService;

class QueueEntryController extends Controller
{
    /**
     * Store a newly created entry in the queue.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Queue $queue)
    {
        $this->authorize('createEntry', $queue);

        // Check if queue is active
        if (!$queue->isActive()) {
            return $this->respondWithError('Cannot add entries to a paused or closed queue.');
        }

        // Check if queue is full
        if ($queue->isFull()) {
            return $this->respondWithError('Queue is full. Please try again later.');
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'meta_data' => 'nullable|array',
        ]);

        // Find or create customer
        $customer = null;
        if (!empty($validated['phone'])) {
            // Try to find existing customer by phone number
            $customer = Customer::where('phone', $validated['phone'])
                ->where('business_id', $queue->business_id)
                ->first();
        }

        if (!$customer) {
            // Create new customer
            $customer = Customer::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'business_id' => $queue->business_id,
            ]);
        } else {
            // Update existing customer information
            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $customer->email,
                'notes' => $validated['notes'] ?? $customer->notes,
            ]);
        }

        // Calculate position and estimated wait time
        $position = $queue->waitingEntries()->count() + 1;
        $estimatedWait = $position * ($queue->estimated_wait_time ?? 5);

        // Create queue entry
        $entry = QueueEntry::create([
            'queue_id' => $queue->id,
            'customer_id' => $customer->id,
            'status' => 'waiting',
            'position' => $position,
            'estimated_wait' => $estimatedWait,
            'meta_data' => $validated['meta_data'] ?? null,
        ]);

        // Return appropriate response based on request
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Added to queue successfully',
                'entry' => $entry,
                'position' => $position,
                'estimated_wait' => $estimatedWait,
            ]);
        }

        return redirect()->route('queues.show', $queue)
            ->with('success', 'Customer added to queue successfully.');
    }

    /**
     * Update the specified queue entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Queue  $queue
     * @param  \App\Models\QueueEntry  $entry
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Queue $queue, QueueEntry $entry)
    {
        $this->authorize('update', [$entry, $queue]);

        // Validate request
        $validated = $request->validate([
            'status' => 'nullable|in:waiting,called,serving,served,no_show,cancelled',
            'position' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'meta_data' => 'nullable|array',
        ]);

        // Handle status change
        if (isset($validated['status']) && $validated['status'] !== $entry->status) {
            switch ($validated['status']) {
                case 'called':
                    $entry->markAsCalled($request->user());
                    break;
                case 'serving':
                    $entry->markAsServing($request->user());
                    break;
                case 'served':
                    $entry->markAsServed($request->user());
                    break;
                case 'no_show':
                    $entry->markAsNoShow($request->user());
                    break;
                case 'cancelled':
                    $entry->markAsCancelled();
                    break;
                case 'waiting':
                    // Just update the status
                    $entry->status = 'waiting';
                    break;
            }

            // If status changed to anything other than waiting, remove position
            if ($validated['status'] !== 'waiting' && $entry->position) {
                $entry->position = null;
                $queue->updatePositions();
            }
        }

        // Update other fields
        if (isset($validated['position']) && $entry->isWaiting()) {
            $entry->position = $validated['position'];
        }

        if (isset($validated['notes'])) {
            $entry->notes = $validated['notes'];
        }

        if (isset($validated['meta_data'])) {
            $entry->meta_data = $validated['meta_data'];
        }

        $entry->save();

        // Return appropriate response
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Queue entry updated successfully',
                'entry' => $entry,
            ]);
        }

        return redirect()->route('queues.show', $queue)
            ->with('success', 'Queue entry updated successfully.');
    }

    /**
     * Remove the specified entry from the queue.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Queue  $queue
     * @param  \App\Models\QueueEntry  $entry
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Queue $queue, QueueEntry $entry)
    {
        $this->authorize('delete', [$entry, $queue]);

        // Delete the entry
        $entry->delete();

        // Update positions for remaining entries
        $queue->updatePositions();

        // Return appropriate response
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Queue entry removed successfully',
            ]);
        }

        return redirect()->route('queues.show', $queue)
            ->with('success', 'Queue entry removed successfully.');
    }

    /**
     * Send SMS notification to the customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Queue  $queue
     * @param  \App\Models\QueueEntry  $entry
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendSmsNotification(Request $request, Queue $queue, QueueEntry $entry)
    {
        $this->authorize('notify', [$entry, $queue]);

        // Get business
        $business = $queue->business;

        // Check feature access
        if (!$business->hasFeature(FeatureService::FEATURE_SMS_NOTIFICATIONS)) {
            return $this->respondWithError('SMS notifications are not available on your current plan.');
        }

        // Check if customer has phone number
        if (!$entry->customer || !$entry->customer->phone) {
            return $this->respondWithError('Customer does not have a phone number.');
        }

        // Validate request
        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        // Create notification record
        $notification = QueueNotification::create([
            'queue_entry_id' => $entry->id,
            'type' => 'sms',
            'status' => 'pending',
            'message' => $validated['message'],
        ]);

        // Send SMS using notification service
        try {
            $notificationService = app(NotificationService::class);
            $response = $notificationService->sendSms(
                $entry->customer->phone,
                $validated['message'],
                $business
            );

            // Update notification record
            $notification->status = $response['success'] ? 'sent' : 'failed';
            $notification->response = json_encode($response);
            $notification->save();

            if (!$response['success']) {
                return $this->respondWithError('Failed to send SMS: ' . $response['message'], $request);
            }

            // Return appropriate response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'SMS notification sent successfully',
                    'notification' => $notification,
                ]);
            }

            return redirect()->route('queues.show', $queue)
                ->with('success', 'SMS notification sent successfully.');
        } catch (\Exception $e) {
            // Update notification record
            $notification->status = 'failed';
            $notification->response = json_encode(['error' => $e->getMessage()]);
            $notification->save();

            return $this->respondWithError('Failed to send SMS: ' . $e->getMessage(), $request);
        }
    }

    /**
     * Helper method to respond with error based on request type.
     *
     * @param  string  $message
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message, $request = null)
    {
        $request = $request ?? request();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'success' => false,
            ], 422);
        }

        return back()->with('error', $message);
    }
}