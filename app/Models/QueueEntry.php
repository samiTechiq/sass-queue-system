<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class QueueEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'queue_id',
        'customer_id',
        'status',
        'position',
        'called_time',
        'served_time',
        'served_by',
        'estimated_wait',
        'notes',
        'meta_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta_data' => 'array',
        'position' => 'integer',
        'estimated_wait' => 'integer',
        'called_time' => 'datetime',
        'served_time' => 'datetime',
    ];

    /**
     * Get the queue that this entry belongs to.
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * Get the customer that this entry belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the staff member who served this customer.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    /**
     * Get notifications sent for this entry.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(QueueNotification::class);
    }

    /**
     * Check if entry is waiting.
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Check if entry is being served.
     */
    public function isServing(): bool
    {
        return $this->status === 'serving';
    }

    /**
     * Check if entry has been called.
     */
    public function isCalled(): bool
    {
        return $this->status === 'called';
    }

    /**
     * Check if entry has been served.
     */
    public function isServed(): bool
    {
        return $this->status === 'served';
    }

    /**
     * Check if entry was a no-show.
     */
    public function isNoShow(): bool
    {
        return $this->status === 'no_show';
    }

    /**
     * Check if entry was cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Mark this entry as called.
     */
    public function markAsCalled(User $user = null): void
    {
        $this->status = 'called';
        $this->called_time = now();
        $this->save();
    }

    /**
     * Mark this entry as serving.
     */
    public function markAsServing(?User $user): void
    {
        $this->status = 'serving';
        $this->served_by = $user?->id;
        $this->save();
    }

    /**
     * Mark this entry as served.
     */
    public function markAsServed(?User $user): void
    {
        $this->status = 'served';
        $this->served_time = now();
        $this->served_by = $user?->id ?? $this->served_by;
        $this->save();
    }

    /**
     * Mark this entry as no-show.
     */
    public function markAsNoShow(?User $user): void
    {
        $this->status = 'no_show';
        $this->served_by = $user?->id ?? $this->served_by;
        $this->save();
    }

    /**
     * Mark this entry as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }
}