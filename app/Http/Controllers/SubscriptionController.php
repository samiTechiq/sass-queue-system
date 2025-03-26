<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Payment\PaymentGatewayFactory;

class SubscriptionController extends Controller
{
    /**
     * Show subscription plans
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $business = auth()->user()->business;
        $currentSubscription = $business->activeSubscription();

        return view('subscription.plans', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    /**
     * Show subscription management page
     */
    public function manage()
    {
        $business = auth()->user()->business;
        $subscription = $business->activeSubscription();
        $paymentMethods = $business->paymentMethods()->get();

        if (!$subscription) {
            return redirect()->route('subscription.plans');
        }

        return view('subscription.manage', [
            'business' => $business,
            'subscription' => $subscription,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Show checkout page for a plan
     */
    public function checkout(Plan $plan, Request $request)
    {
        $business = auth()->user()->business;
        $currentSubscription = $business->activeSubscription();
        $paymentMethods = $business->paymentMethods()->get();
        $billingCycle = $request->get('billing_cycle', 'monthly');

        // Get setup intent for Stripe
        $gateway = PaymentGatewayFactory::create();
        $setupIntent = $gateway->createSetupIntent($business);

        return view('subscription.checkout', [
            'plan' => $plan,
            'business' => $business,
            'currentSubscription' => $currentSubscription,
            'paymentMethods' => $paymentMethods,
            'setupIntent' => $setupIntent,
            'billingCycle' => $billingCycle,
        ]);
    }

    /**
     * Create a new subscription
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'payment_method' => 'nullable|string',
            'payment_method_id' => 'nullable|string',
        ]);

        $business = auth()->user()->business;
        $plan = Plan::findOrFail($validated['plan_id']);
        $gateway = PaymentGatewayFactory::create();

        // Check for existing subscription
        $currentSubscription = $business->activeSubscription();

        if ($currentSubscription) {
            // If same plan, just update billing cycle if needed
            if ($currentSubscription->plan_id == $plan->id) {
                if ($currentSubscription->billing_cycle != $validated['billing_cycle']) {
                    $gateway->changeBillingCycle($currentSubscription, $validated['billing_cycle']);
                }

                return redirect()->route('subscription.manage')
                    ->with('success', 'Subscription updated successfully.');
            }

            // Upgrade/downgrade to new plan
            if ($gateway->changePlan($currentSubscription, $plan)) {
                if ($currentSubscription->billing_cycle != $validated['billing_cycle']) {
                    $gateway->changeBillingCycle($currentSubscription, $validated['billing_cycle']);
                }

                return redirect()->route('subscription.manage')
                    ->with('success', 'Subscription updated to ' . $plan->name . ' plan.');
            } else {
                return back()->with('error', 'Failed to update subscription. Please try again.');
            }
        }

        // Handle payment method
        $paymentMethodId = null;

        if (!empty($validated['payment_method'])) {
            // Using an existing payment method
            $paymentMethod = PaymentMethod::where('business_id', $business->id)
                ->where('id', $validated['payment_method'])
                ->first();

            if ($paymentMethod) {
                $paymentMethodId = $paymentMethod->gateway_payment_method_id;
            }
        } else if (!empty($validated['payment_method_id'])) {
            // Using a new payment method
            try {
                $paymentMethod = $gateway->savePaymentMethod($business, $validated['payment_method_id']);
                $paymentMethodId = $paymentMethod->gateway_payment_method_id;
            } catch (\Exception $e) {
                Log::error('Payment method save error: ' . $e->getMessage());
                return back()->with('error', 'Failed to save payment method: ' . $e->getMessage());
            }
        }

        // Create the subscription
        try {
            $subscription = $gateway->createSubscription(
                $business,
                $plan,
                $validated['billing_cycle'],
                $paymentMethodId,
                $plan->trial_days
            );

            return redirect()->route('subscription.manage')
                ->with('success', 'Subscription created successfully. You are now on the ' . $plan->name . ' plan.');
        } catch (\Exception $e) {
            Log::error('Subscription creation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to create subscription: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'immediately' => 'nullable|boolean',
        ]);

        $immediately = $validated['immediately'] ?? false;
        $reason = $validated['reason'] ?? null;

        $business = auth()->user()->business;
        $subscription = $business->activeSubscription();

        if (!$subscription) {
            return back()->with('error', 'No active subscription found.');
        }

        $gateway = PaymentGatewayFactory::create($subscription->gateway);

        if ($gateway->cancelSubscription($subscription, $immediately)) {
            if ($reason) {
                $subscription->cancellation_reason = $reason;
                $subscription->save();
            }

            return redirect()->route('subscription.manage')
                ->with('success', 'Your subscription has been canceled.');
        }

        return back()->with('error', 'Failed to cancel subscription. Please try again.');
    }

    /**
     * Resume a canceled subscription
     */
    public function resume(Request $request)
    {
        $business = Auth()->user()->business;
        $subscription = $business->activeSubscription();

        if (!$subscription || !$subscription->isCanceled()) {
            return back()->with('error', 'No canceled subscription found to resume.');
        }

        $gateway = PaymentGatewayFactory::create($subscription->gateway);

        if ($gateway->resumeSubscription($subscription)) {
            return redirect()->route('subscription.manage')
                ->with('success', 'Your subscription has been resumed.');
        }

        return back()->with('error', 'Failed to resume subscription. Please try again.');
    }

    /**
     * Show feature usage
     */
    public function features()
    {
        $business = auth()->user()->business;
        $features = $business->getFeatures();

        return view('subscription.features', [
            'business' => $business,
            'features' => $features,
        ]);
    }

    /**
     * Show payment methods page
     */
    public function paymentMethods()
    {
        $business = auth()->user()->business;
        $paymentMethods = $business->paymentMethods()->get();

        // Get setup intent for Stripe
        $gateway = PaymentGatewayFactory::create();
        $setupIntent = $gateway->createSetupIntent($business);

        return view('subscription.payment-methods', [
            'business' => $business,
            'paymentMethods' => $paymentMethods,
            'setupIntent' => $setupIntent,
        ]);
    }

    /**
     * Add a new payment method
     */
    public function addPaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $business = auth()->user()->business;
        $gateway = PaymentGatewayFactory::create();

        try {
            $paymentMethod = $gateway->savePaymentMethod($business, $validated['payment_method_id']);

            return redirect()->route('subscription.payment-methods')
                ->with('success', 'Payment method added successfully.');
        } catch (\Exception $e) {
            Log::error('Payment method save error: ' . $e->getMessage());
            return back()->with('error', 'Failed to save payment method: ' . $e->getMessage());
        }
    }

    /**
     * Set default payment method
     */
    public function setDefaultPaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $business = auth()->user()->business;

        if ($paymentMethod->business_id !== $business->id) {
            return back()->with('error', 'Invalid payment method.');
        }

        $paymentMethod->setAsDefault();

        return back()->with('success', 'Default payment method updated.');
    }

    /**
     * Delete payment method
     */
    public function deletePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $business = auth()->user()->business;

        if ($paymentMethod->business_id !== $business->id) {
            return back()->with('error', 'Invalid payment method.');
        }

        if ($paymentMethod->is_default) {
            return back()->with('error', 'Cannot delete default payment method. Please set another method as default first.');
        }

        $paymentMethod->delete();

        return back()->with('success', 'Payment method deleted successfully.');
    }

    /**
     * Show invoice list
     */
    public function invoices()
    {
        $business = auth()->user()->business;
        $invoices = $business->invoices()->orderByDesc('created_at')->paginate(10);

        return view('subscription.invoices', [
            'business' => $business,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show subscription required page
     */
    public function required()
    {
        return view('subscription.required');
    }

    /**
     * Show upgrade required page
     */
    public function upgrade()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $business = auth()->user()->business;
        $currentSubscription = $business->activeSubscription();

        return view('subscription.upgrade', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }
}