<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueueNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'queue_entry_id',
        'type',
        'status',
        'message',
        'response',
    ];

    /**
     * Get the queue entry this notification is for.
     */
    public function queueEntry(): BelongsTo
    {
        return $this->belongsTo(QueueEntry::class);
    }

    /**
     * Get the queue this notification is related to.
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'queue_id');
    }
}