<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Queue;
use App\Models\QueueEntry;
use Illuminate\Auth\Access\HandlesAuthorization;

class QueueEntryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the queue entry.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\QueueEntry  $entry
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, QueueEntry $entry, Queue $queue)
    {
        // Verify entry belongs to the given queue
        if ($entry->queue_id !== $queue->id) {
            return false;
        }

        // Verify user has access to this business
        return $user->business_id === $queue->business_id;
    }

    /**
     * Determine whether the user can delete the queue entry.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\QueueEntry  $entry
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, QueueEntry $entry, Queue $queue)
    {
        // Verify entry belongs to the given queue
        if ($entry->queue_id !== $queue->id) {
            return false;
        }

        // Verify user has access to this business
        return $user->business_id === $queue->business_id;
    }

    /**
     * Determine whether the user can send notifications for the queue entry.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\QueueEntry  $entry
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function notify(User $user, QueueEntry $entry, Queue $queue)
    {
        // Verify entry belongs to the given queue
        if ($entry->queue_id !== $queue->id) {
            return false;
        }

        // Verify user has access to this business
        return $user->business_id === $queue->business_id;
    }
}
