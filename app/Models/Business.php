<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\FeatureService;

class Business extends Model
{
    // Add this to the Business model

    /**
     * Get the active subscription for this business
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->first();
    }

    /**
     * Check if business has access to a feature
     */
    public function hasFeature(string $featureCode): bool
    {
        return FeatureService::hasFeature($this, $featureCode);
    }

    /**
     * Get remaining quota for a feature
     */
    public function getRemainingQuota(string $featureCode): ?int
    {
        return FeatureService::getRemainingQuota($this, $featureCode);
    }

    /**
     * Check if business can use a feature (has quota)
     */
    public function canUseFeature(string $featureCode, int $amount = 1): bool
    {
        return FeatureService::canUseFeature($this, $featureCode, $amount);
    }

    /**
     * Use a feature (increment usage counter)
     */
    public function useFeature(string $featureCode, int $amount = 1): bool
    {
        return FeatureService::useFeature($this, $featureCode, $amount);
    }

    /**
     * Get list of all features and their status for this business
     */
    public function getFeatures(): array
    {
        return FeatureService::getBusinessFeatures($this);
    }
}