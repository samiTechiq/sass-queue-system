<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\Feature;
use App\Services\FeatureService;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedFeatures();
        $this->seedPlans();
    }

    /**
     * Seed the features
     */
    private function seedFeatures(): void
    {
        // Initialize the default features from the service
        FeatureService::initializeFeatures();
    }

    /**
     * Seed the plans and their features
     */
    private function seedPlans(): void
    {
        // Free Plan
        $freePlan = Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Basic queue management for small businesses',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'trial_days' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Basic Plan
        $basicPlan = Plan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'description' => 'Essential features for growing businesses',
            'price_monthly' => 5000,
            'price_yearly' => 60000, // ~2 months free
            'stripe_monthly_price_id' => 'price_1234567890basic_monthly',
            'stripe_yearly_price_id' => 'price_1234567890basic_yearly',
            'trial_days' => 14,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Professional Plan
        $proPlan = Plan::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'Advanced features for professional businesses',
            'price_monthly' => 10000,
            'price_yearly' => 120000, // ~2 months free
            'stripe_monthly_price_id' => 'price_1234567890pro_monthly',
            'stripe_yearly_price_id' => 'price_1234567890pro_yearly',
            'trial_days' => 14,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 3,
        ]);

        // Enterprise Plan
        $enterprisePlan = Plan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'description' => 'Complete solution for large businesses',
            'price_monthly' => 30000,
            'price_yearly' => 360000, // ~2 months free
            'stripe_monthly_price_id' => 'price_1234567890enterprise_monthly',
            'stripe_yearly_price_id' => 'price_1234567890enterprise_yearly',
            'trial_days' => 14,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        // Now assign features to plans
        $this->assignFeaturesToPlans($freePlan, $basicPlan, $proPlan, $enterprisePlan);
    }

    /**
     * Assign features to plans with appropriate values
     */
    private function assignFeaturesToPlans(Plan $freePlan, Plan $basicPlan, Plan $proPlan, Plan $enterprisePlan): void
    {
        // Get all features
        $features = Feature::all();

        // Map of feature code => [free, basic, pro, enterprise] values
        $featureValues = [
            // Quota features
            FeatureService::FEATURE_MAX_QUEUES => ['1', '3', '10', 'unlimited'],
            FeatureService::FEATURE_MAX_STAFF => ['2', '5', '20', 'unlimited'],
            FeatureService::FEATURE_MAX_LOCATIONS => ['1', '1', '5', 'unlimited'],

            // Boolean features
            FeatureService::FEATURE_SMS_NOTIFICATIONS => ['false', 'true', 'true', 'true'],
            FeatureService::FEATURE_EMAIL_NOTIFICATIONS => ['true', 'true', 'true', 'true'],
            FeatureService::FEATURE_ANALYTICS => ['false', 'true', 'true', 'true'],
            FeatureService::FEATURE_API_ACCESS => ['false', 'false', 'true', 'true'],
            FeatureService::FEATURE_CUSTOM_BRANDING => ['false', 'false', 'true', 'true'],
            FeatureService::FEATURE_QUEUE_TEMPLATES => ['false', 'true', 'true', 'true'],
            FeatureService::FEATURE_CUSTOMER_FEEDBACK => ['false', 'false', 'true', 'true'],
            FeatureService::FEATURE_CUSTOMER_HISTORY => ['false', 'true', 'true', 'true'],
            FeatureService::FEATURE_PRIORITY_SUPPORT => ['false', 'false', 'true', 'true'],
            FeatureService::FEATURE_ADVANCED_REPORTING => ['false', 'false', 'true', 'true'],
            FeatureService::FEATURE_MULTIPLE_DISPLAY => ['false', 'false', 'true', 'true'],
        ];

        // For each feature, assign appropriate values to each plan
        foreach ($features as $feature) {
            if (isset($featureValues[$feature->code])) {
                $values = $featureValues[$feature->code];

                // Free Plan
                $freePlan->features()->attach($feature->id, ['value' => $values[0]]);

                // Basic Plan
                $basicPlan->features()->attach($feature->id, ['value' => $values[1]]);

                // Professional Plan
                $proPlan->features()->attach($feature->id, ['value' => $values[2]]);

                // Enterprise Plan
                $enterprisePlan->features()->attach($feature->id, ['value' => $values[3]]);
            }
        }
    }
}