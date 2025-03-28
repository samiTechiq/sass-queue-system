<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Queue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'max_size',
        'estimated_wait_time',
        'business_id',
        'location_id',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'max_size' => 'integer',
        'estimated_wait_time' => 'integer',
    ];

    /**
     * Get the business that owns the queue.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the location that this queue belongs to.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    // public function location()
    // {
    //     return $this->belongsTo(BusinessLocation::class, 'business_location_id');
    // }

    /**
     * Get all entries for this queue.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }

    /**
     * Get all customers in this queue.
     */
    public function customers(): HasManyThrough
    {
        return $this->hasManyThrough(Customer::class, QueueEntry::class, 'queue_id', 'id', 'id', 'customer_id');
    }

    /**
     * Get waiting entries.
     */
    public function waitingEntries()
    {
        return $this->entries()->where('status', 'waiting')->orderBy('position');
    }

    /**
     * Get currently serving entries.
     */
    public function servingEntries()
    {
        return $this->entries()->whereIn('status', ['called', 'serving']);
    }

    /**
     * Get entries that have been served.
     */
    public function servedEntries()
    {
        return $this->entries()->where('status', 'served');
    }

    /**
     * Get all active notifications for this queue.
     */
    public function notifications(): HasManyThrough
    {
        return $this->hasManyThrough(QueueNotification::class, QueueEntry::class);
    }

    /**
     * Check if queue is full based on max_size.
     */
    public function isFull(): bool
    {
        if (!$this->max_size) {
            return false;
        }

        return $this->waitingEntries()->count() >= $this->max_size;
    }

    /**
     * Calculate current wait time based on number of waiting entries and estimated time per customer.
     */
    public function calculateWaitTime(): int
    {
        $waitingCount = $this->waitingEntries()->count();
        $estimatedTimePerCustomer = $this->estimated_wait_time ?? 5; // Default 5 minutes if not set

        return $waitingCount * $estimatedTimePerCustomer;
    }

    /**
     * Update positions for all waiting entries to ensure they are sequential.
     */
    public function updatePositions(): void
    {
        $entries = $this->waitingEntries()->orderBy('created_at')->get();

        $position = 1;
        foreach ($entries as $entry) {
            $entry->position = $position++;
            $entry->save();
        }
    }

    /**
     * Check if queue is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if queue is paused.
     */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Check if queue is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}