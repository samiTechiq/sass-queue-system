<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Plan;
use App\Models\Feature;

class PricingTable extends Component
{
    public $billingCycle = 'monthly';
    public $currentPlanId = null;

    protected $queryString = ['billingCycle'];

    public function mount()
    {
        // Get current plan if authenticated
        if (auth()->check() && auth()->user()->business) {
            $subscription = auth()->user()->business->activeSubscription();
            $this->currentPlanId = $subscription ? $subscription->plan_id : null;
        }
    }

    public function render()
    {
        // Get plans and features
        $plans = Plan::with('features')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Get all public features
        $features = Feature::where('is_public', true)
            ->orderBy('name')
            ->get();

        // Group features by type
        $featureGroups = [
            'general' => [],
            'quotas' => [],
        ];

        foreach ($features as $feature) {
            if ($feature->type === 'boolean') {
                $featureGroups['general'][] = $feature;
            } else {
                $featureGroups['quotas'][] = $feature;
            }
        }

        return view('livewire.pricing-table', [
            'plans' => $plans,
            'featureGroups' => $featureGroups,
            'currentPlanId' => $this->currentPlanId,
        ]);
    }

    public function toggleBillingCycle()
    {
        $this->billingCycle = $this->billingCycle === 'monthly' ? 'yearly' : 'monthly';
    }
}