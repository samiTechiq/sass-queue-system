<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'notes',
        'business_id',
    ];

    /**
     * Get the business this customer belongs to.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get queue entries for this customer.
     */
    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }

    /**
     * Get active queue entries (not served or cancelled).
     */
    public function activeQueueEntries()
    {
        return $this->queueEntries()
            ->whereIn('status', ['waiting', 'called', 'serving']);
    }

    /**
     * Check if customer is currently in any queue.
     */
    public function isInQueue(): bool
    {
        return $this->activeQueueEntries()->exists();
    }
}