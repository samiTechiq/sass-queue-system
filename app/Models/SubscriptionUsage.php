<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'feature_id',
        'used',
        'reset_at',
    ];

    protected $casts = [
        'used' => 'integer',
        'reset_at' => 'datetime',
    ];

    /**
     * The subscription this usage belongs to
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * The feature this usage tracks
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Reset usage counter
     */
    public function reset(): bool
    {
        $this->used = 0;
        $this->reset_at = now();

        return $this->save();
    }
}