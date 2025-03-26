<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'plan_id',
        'status',
        'billing_cycle',
        'gateway',
        'gateway_id',
        'gateway_status',
        'trial_ends_at',
        'next_billing_date',
        'canceled_at',
        'ends_at',
        'cancellation_reason',
        'auto_renew',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'next_billing_date' => 'datetime',
        'canceled_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    /**
     * The business that owns the subscription
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * The plan of the subscription
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Usage records for this subscription
     */
    public function usage(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    /**
     * Invoices for this subscription
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        if ($this->status === 'canceled' && $this->ends_at && $this->ends_at <= now()) {
            return false;
        }

        return in_array($this->status, ['active', 'trialing']);
    }

    /**
     * Check if subscription is canceled
     */
    public function isCanceled(): bool
    {
        return $this->canceled_at !== null;
    }

    /**
     * Check if subscription is on trial
     */
    public function onTrial(): bool
    {
        return $this->status === 'trialing' &&
            $this->trial_ends_at !== null &&
            $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription has a specific feature
     */
    public function hasFeature(string $featureCode): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->plan->hasFeature($featureCode);
    }

    /**
     * Get quota for a specific feature
     */
    public function getQuota(string $featureCode): ?int
    {
        if (!$this->isActive()) {
            return 0;
        }

        return $this->plan->getQuota($featureCode);
    }

    /**
     * Get remaining quota for a specific feature
     */
    public function getRemainingQuota(string $featureCode): ?int
    {
        $quota = $this->getQuota($featureCode);

        if ($quota === null) {
            return null; // Unlimited or not found
        }

        $usage = $this->getUsage($featureCode);

        return max(0, $quota - $usage);
    }

    /**
     * Get current usage for a specific feature
     */
    public function getUsage(string $featureCode): int
    {
        $feature = Feature::where('code', $featureCode)->first();

        if (!$feature) {
            return 0;
        }

        $usage = $this->usage()
            ->where('feature_id', $feature->id)
            ->first();

        return $usage ? $usage->used : 0;
    }

    /**
     * Increment usage for a specific feature
     */
    public function incrementUsage(string $featureCode, int $incrementBy = 1): bool
    {
        $feature = Feature::where('code', $featureCode)->first();

        if (!$feature) {
            return false;
        }

        $quota = $this->getQuota($featureCode);
        $usage = $this->getUsage($featureCode);

        // Check quota limit if not unlimited
        if ($quota !== null && $usage + $incrementBy > $quota) {
            return false;
        }

        $usageRecord = $this->usage()
            ->firstOrCreate(['feature_id' => $feature->id]);

        $usageRecord->increment('used', $incrementBy);

        return true;
    }

    /**
     * Cancel subscription
     */
    public function cancel(string $reason = null): bool
    {
        $this->canceled_at = now();
        $this->cancellation_reason = $reason;
        $this->auto_renew = false;

        // If not on trial, set end date to next billing date
        if (!$this->onTrial() && $this->next_billing_date) {
            $this->ends_at = $this->next_billing_date;
        } else if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        } else {
            // Fallback to 30 days from now if no dates are set
            $this->ends_at = now()->addDays(30);
        }

        return $this->save();
    }

    /**
     * Resume a canceled subscription
     */
    public function resume(): bool
    {
        if (!$this->isCanceled() || ($this->ends_at && $this->ends_at->isPast())) {
            return false;
        }

        $this->canceled_at = null;
        $this->ends_at = null;
        $this->auto_renew = true;

        return $this->save();
    }
}