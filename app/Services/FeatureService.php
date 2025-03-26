<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Feature;

class FeatureService
{
    // Feature codes
    const FEATURE_MAX_QUEUES = 'max_queues';
    const FEATURE_MAX_STAFF = 'max_staff';
    const FEATURE_MAX_LOCATIONS = 'max_locations';
    const FEATURE_SMS_NOTIFICATIONS = 'sms_notifications';
    const FEATURE_EMAIL_NOTIFICATIONS = 'email_notifications';
    const FEATURE_ANALYTICS = 'analytics';
    const FEATURE_API_ACCESS = 'api_access';
    const FEATURE_CUSTOM_BRANDING = 'custom_branding';
    const FEATURE_QUEUE_TEMPLATES = 'queue_templates';
    const FEATURE_CUSTOMER_FEEDBACK = 'customer_feedback';
    const FEATURE_CUSTOMER_HISTORY = 'customer_history';
    const FEATURE_PRIORITY_SUPPORT = 'priority_support';
    const FEATURE_ADVANCED_REPORTING = 'advanced_reporting';
    const FEATURE_MULTIPLE_DISPLAY = 'multiple_display';

    /**
     * Check if business has access to a feature
     */
    public static function hasFeature(Business $business, string $featureCode): bool
    {
        // Get the active subscription for the business
        $subscription = $business->activeSubscription();

        if (!$subscription) {
            return false;
        }

        return $subscription->hasFeature($featureCode);
    }

    /**
     * Get the quota for a feature
     */
    public static function getQuota(Business $business, string $featureCode): ?int
    {
        $subscription = $business->activeSubscription();

        if (!$subscription) {
            return 0;
        }

        return $subscription->getQuota($featureCode);
    }

    /**
     * Get the remaining quota for a feature
     */
    public static function getRemainingQuota(Business $business, string $featureCode): ?int
    {
        $subscription = $business->activeSubscription();

        if (!$subscription) {
            return 0;
        }

        return $subscription->getRemainingQuota($featureCode);
    }

    /**
     * Check if a feature can be used (has remaining quota)
     */
    public static function canUseFeature(Business $business, string $featureCode, int $amount = 1): bool
    {
        // First check if feature is available
        if (!self::hasFeature($business, $featureCode)) {
            return false;
        }

        // Get quota and usage
        $subscription = $business->activeSubscription();

        if (!$subscription) {
            return false;
        }

        $quota = $subscription->getQuota($featureCode);

        // If quota is null, it means unlimited
        if ($quota === null) {
            return true;
        }

        $usage = $subscription->getUsage($featureCode);

        // Check if there's enough quota left
        return ($usage + $amount) <= $quota;
    }

    /**
     * Use a feature (increment usage counter)
     */
    public static function useFeature(Business $business, string $featureCode, int $amount = 1): bool
    {
        if (!self::canUseFeature($business, $featureCode, $amount)) {
            return false;
        }

        $subscription = $business->activeSubscription();

        if (!$subscription) {
            return false;
        }

        return $subscription->incrementUsage($featureCode, $amount);
    }

    /**
     * Get all features with their status for a business
     */
    public static function getBusinessFeatures(Business $business): array
    {
        $features = Feature::all();
        $subscription = $business->activeSubscription();
        $result = [];

        foreach ($features as $feature) {
            $hasAccess = false;
            $quota = null;
            $used = 0;
            $remaining = null;

            if ($subscription) {
                $hasAccess = $subscription->hasFeature($feature->code);
                $quota = $subscription->getQuota($feature->code);
                $used = $subscription->getUsage($feature->code);
                $remaining = $subscription->getRemainingQuota($feature->code);
            }

            $result[] = [
                'id' => $feature->id,
                'name' => $feature->name,
                'code' => $feature->code,
                'description' => $feature->description,
                'has_access' => $hasAccess,
                'quota' => $quota,
                'used' => $used,
                'remaining' => $remaining,
                'unlimited' => $quota === null && $hasAccess,
            ];
        }

        return $result;
    }

    /**
     * Initialize the default features in the database
     */
    public static function initializeFeatures(): void
    {
        $features = [
            [
                'name' => 'Maximum Queues',
                'code' => self::FEATURE_MAX_QUEUES,
                'description' => 'Maximum number of active queues allowed',
                'type' => 'integer',
                'unit' => 'queues',
            ],
            [
                'name' => 'Maximum Staff Users',
                'code' => self::FEATURE_MAX_STAFF,
                'description' => 'Maximum number of staff users allowed',
                'type' => 'integer',
                'unit' => 'users',
            ],
            [
                'name' => 'Maximum Locations',
                'code' => self::FEATURE_MAX_LOCATIONS,
                'description' => 'Maximum number of business locations',
                'type' => 'integer',
                'unit' => 'locations',
            ],
            [
                'name' => 'SMS Notifications',
                'code' => self::FEATURE_SMS_NOTIFICATIONS,
                'description' => 'Send SMS notifications to customers',
                'type' => 'boolean',
            ],
            [
                'name' => 'Email Notifications',
                'code' => self::FEATURE_EMAIL_NOTIFICATIONS,
                'description' => 'Send email notifications to customers',
                'type' => 'boolean',
            ],
            [
                'name' => 'Analytics',
                'code' => self::FEATURE_ANALYTICS,
                'description' => 'Queue analytics and reporting',
                'type' => 'boolean',
            ],
            [
                'name' => 'API Access',
                'code' => self::FEATURE_API_ACCESS,
                'description' => 'Access to the API for integration',
                'type' => 'boolean',
            ],
            [
                'name' => 'Custom Branding',
                'code' => self::FEATURE_CUSTOM_BRANDING,
                'description' => 'Customize the queue interface with your branding',
                'type' => 'boolean',
            ],
            [
                'name' => 'Queue Templates',
                'code' => self::FEATURE_QUEUE_TEMPLATES,
                'description' => 'Create and save queue templates',
                'type' => 'boolean',
            ],
            [
                'name' => 'Customer Feedback',
                'code' => self::FEATURE_CUSTOMER_FEEDBACK,
                'description' => 'Collect feedback from customers',
                'type' => 'boolean',
            ],
            [
                'name' => 'Customer History',
                'code' => self::FEATURE_CUSTOMER_HISTORY,
                'description' => 'View customer queue history',
                'type' => 'boolean',
            ],
            [
                'name' => 'Priority Support',
                'code' => self::FEATURE_PRIORITY_SUPPORT,
                'description' => 'Access to priority customer support',
                'type' => 'boolean',
            ],
            [
                'name' => 'Advanced Reporting',
                'code' => self::FEATURE_ADVANCED_REPORTING,
                'description' => 'Access to advanced reports and analytics',
                'type' => 'boolean',
            ],
            [
                'name' => 'Multiple Display Support',
                'code' => self::FEATURE_MULTIPLE_DISPLAY,
                'description' => 'Support for multiple queue displays',
                'type' => 'boolean',
            ],
        ];

        foreach ($features as $featureData) {
            Feature::firstOrCreate(
                ['code' => $featureData['code']],
                $featureData
            );
        }
    }
}






// Register middlewares in app/Http/Kernel.php
// protected $routeMiddleware = [
//     // ...
//     'subscription.active' => \App\Http\Middleware\EnsureActiveSubscription::class,
//     'subscription.feature' => \App\Http\Middleware\EnsureFeatureAccess::class,
// ];