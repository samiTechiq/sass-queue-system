<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'paypal_monthly_plan_id',
        'paypal_yearly_plan_id',
        'trial_days',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'float',
        'price_yearly' => 'float',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Plan features
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class)
            ->withPivot('value')
            ->withTimestamps();
    }


    /**
     * Active subscriptions to this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the value for a specific feature
     */
    public function getFeatureValue(string $featureCode)
    {
        $feature = $this->features()
            ->whereHas('feature', function ($query) use ($featureCode) {
                $query->where('code', $featureCode);
            })
            ->first();

        return $feature ? $feature->pivot->value : null;
    }

    /**
     * Check if plan has a specific feature
     */
    public function hasFeature(string $featureCode): bool
    {
        $value = $this->getFeatureValue($featureCode);

        return $value === 'true' || (is_numeric($value) && (int) $value > 0);
    }

    /**
     * Get the quota for a specific feature
     */
    public function getQuota(string $featureCode): ?int
    {
        $value = $this->getFeatureValue($featureCode);

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Get price based on billing cycle
     */
    public function getPrice(string $billingCycle = 'monthly'): float
    {
        return $billingCycle === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }

    /**
     * Get stripe price ID based on billing cycle
     */
    public function getStripePriceId(string $billingCycle = 'monthly'): ?string
    {
        return $billingCycle === 'yearly'
            ? $this->stripe_yearly_price_id
            : $this->stripe_monthly_price_id;
    }

    /**
     * Get PayPal plan ID based on billing cycle
     */
    public function getPaypalPlanId(string $billingCycle = 'monthly'): ?string
    {
        return $billingCycle === 'yearly'
            ? $this->paypal_yearly_plan_id
            : $this->paypal_monthly_plan_id;
    }
}