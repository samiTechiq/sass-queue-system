<?php

namespace App\Policies;

use App\Models\Queue;
use App\Models\QueueEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QueuePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any queues.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // All authenticated users can view queues for their business
        return true;
    }

    /**
     * Determine whether the user can view the queue.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Queue $queue)
    {
        // User can only view queues for their business
        return $user->business_id === $queue->business_id;
    }

    /**
     * Determine whether the user can create queues.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Only business admins and admins can create queues
        return $user->isBusinessAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the queue.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Queue $queue)
    {
        // User must be business admin or admin and the queue must belong to their business
        return ($user->isBusinessAdmin() || $user->isAdmin()) && $user->business_id === $queue->business_id;
    }

    /**
     * Determine whether the user can delete the queue.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Queue $queue)
    {
        // Only business admins and admins can delete queues for their business
        return ($user->isBusinessAdmin() || $user->isAdmin()) && $user->business_id === $queue->business_id;
    }

    /**
     * Determine whether the user can create entries in the queue.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function createEntry(User $user, Queue $queue)
    {
        // All staff for this business can add entries
        return $user->business_id === $queue->business_id;
    }
}
