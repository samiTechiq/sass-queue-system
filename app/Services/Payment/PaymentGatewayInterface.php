<?php

namespace App\Services\Payment;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Invoice;
use App\Models\Business;
use App\Models\Subscription;
use App\Models\PaymentMethod;

interface PaymentGatewayInterface
{
    /**
     * Create a customer in the payment gateway
     */
    public function createCustomer(Business $business): string;

    /**
     * Create a setup intent for adding a payment method
     */
    public function createSetupIntent(Business $business): array;

    /**
     * Save a payment method from a setup intent
     */
    public function savePaymentMethod(Business $business, string $paymentMethodId): PaymentMethod;

    /**
     * Create a subscription
     */
    public function createSubscription(
        Business $business,
        Plan $plan,
        string $billingCycle = 'monthly',
        ?string $paymentMethodId = null,
        ?int $trialDays = null
    ): Subscription;

    /**
     * Update a subscription's plan
     */
    public function changePlan(Subscription $subscription, Plan $newPlan): bool;

    /**
     * Update a subscription's billing cycle
     */
    public function changeBillingCycle(Subscription $subscription, string $newBillingCycle): bool;

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(Subscription $subscription, bool $immediately = false): bool;

    /**
     * Resume a canceled subscription
     */
    public function resumeSubscription(Subscription $subscription): bool;

    /**
     * Handle webhook events from the payment gateway
     */
    public function handleWebhook(array $payload): array;
}

class StripeGateway implements PaymentGatewayInterface
{
    private $stripe;

    public function __construct()
    {
        // $this->stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a customer in Stripe
     */
    public function createCustomer(Business $business): string
    {
        // Check if business already has a Stripe ID
        if ($business->stripe_id) {
            return $business->stripe_id;
        }

        // Create a new customer in Stripe
        $customer = $this->stripe->customers->create([
            'name' => $business->name,
            'email' => $business->email,
            'phone' => $business->phone,
            'metadata' => [
                'business_id' => $business->id,
                'platform' => 'QueueMaster',
            ],
        ]);

        // Save the Stripe customer ID to the business
        $business->stripe_id = $customer->id;
        $business->save();

        return $customer->id;
    }

    /**
     * Create a setup intent for adding a payment method
     */
    public function createSetupIntent(Business $business): array
    {
        $customerId = $this->createCustomer($business);

        $setupIntent = $this->stripe->setupIntents->create([
            'customer' => $customerId,
            'payment_method_types' => ['card'],
        ]);

        return [
            'client_secret' => $setupIntent->client_secret,
            'public_key' => config('services.stripe.key'),
        ];
    }

    /**
     * Save a payment method from a setup intent
     */
    public function savePaymentMethod(Business $business, string $paymentMethodId): PaymentMethod
    {
        $stripePaymentMethod = $this->stripe->paymentMethods->retrieve($paymentMethodId);

        // Attach payment method to customer
        if ($business->stripe_id) {
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $business->stripe_id,
            ]);
        }

        // Set as default if it's the first payment method
        $isDefault = $business->paymentMethods()->count() === 0;

        // Create local payment method record
        $paymentMethod = new PaymentMethod([
            'business_id' => $business->id,
            'gateway' => 'stripe',
            'gateway_payment_method_id' => $paymentMethodId,
            'type' => $stripePaymentMethod->type,
            'is_default' => $isDefault,
        ]);

        // Add card details if it's a card
        if ($stripePaymentMethod->type === 'card') {
            $card = $stripePaymentMethod->card;
            $paymentMethod->last_four = $card->last4;
            $paymentMethod->brand = $card->brand;
            $paymentMethod->exp_month = $card->exp_month;
            $paymentMethod->exp_year = $card->exp_year;
        }

        $paymentMethod->save();

        // Set as default payment method in Stripe
        if ($isDefault && $business->stripe_id) {
            $this->stripe->customers->update($business->stripe_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);
        }

        return $paymentMethod;
    }

    /**
     * Create a subscription
     */
    public function createSubscription(
        Business $business,
        Plan $plan,
        string $billingCycle = 'monthly',
        ?string $paymentMethodId = null,
        ?int $trialDays = null
    ): Subscription {
        // Ensure customer exists
        $customerId = $this->createCustomer($business);

        // Get price ID based on billing cycle
        $priceId = $plan->getStripePriceId($billingCycle);

        if (!$priceId) {
            throw new \Exception("No Stripe price ID found for plan {$plan->name} with {$billingCycle} billing cycle");
        }

        // Get payment method
        if (!$paymentMethodId) {
            $defaultPaymentMethod = $business->paymentMethods()
                ->where('is_default', true)
                ->first();

            if ($defaultPaymentMethod) {
                $paymentMethodId = $defaultPaymentMethod->gateway_payment_method_id;
            }
        }

        // Setup subscription parameters
        $subscriptionParams = [
            'customer' => $customerId,
            'items' => [
                [
                    'price' => $priceId,
                ],
            ],
            'expand' => ['latest_invoice.payment_intent'],
            'metadata' => [
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
            ],
        ];

        // Add payment method if available
        if ($paymentMethodId) {
            $subscriptionParams['default_payment_method'] = $paymentMethodId;
        }

        // Add trial if specified
        if ($trialDays !== null) {
            $subscriptionParams['trial_period_days'] = $trialDays;
        }

        // Create subscription in Stripe
        $stripeSubscription = $this->stripe->subscriptions->create($subscriptionParams);

        // Create local subscription record
        $subscription = new Subscription([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'gateway' => 'stripe',
            'gateway_id' => $stripeSubscription->id,
            'gateway_status' => $stripeSubscription->status,
            'auto_renew' => true,
        ]);

        // Set subscription dates
        if ($stripeSubscription->trial_end) {
            $subscription->trial_ends_at = Carbon::createFromTimestamp($stripeSubscription->trial_end);
            $subscription->status = 'trialing';
        } else {
            $subscription->status = $stripeSubscription->status;
        }

        $subscription->next_billing_date = Carbon::createFromTimestamp($stripeSubscription->current_period_end);
        $subscription->save();

        return $subscription;
    }

    /**
     * Update a subscription's plan
     */
    public function changePlan(Subscription $subscription, Plan $newPlan): bool
    {
        if ($subscription->gateway !== 'stripe' || !$subscription->gateway_id) {
            return false;
        }

        // Get price ID for the new plan
        $priceId = $newPlan->getStripePriceId($subscription->billing_cycle);

        if (!$priceId) {
            return false;
        }

        // Get the subscription from Stripe
        $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->gateway_id);

        // Update the subscription item
        $this->stripe->subscriptionItems->update(
            $stripeSubscription->items->data[0]->id,
            ['price' => $priceId]
        );

        // Update the local subscription
        $subscription->plan_id = $newPlan->id;
        $subscription->save();

        return true;
    }

    /**
     * Update a subscription's billing cycle
     */
    public function changeBillingCycle(Subscription $subscription, string $newBillingCycle): bool
    {
        if ($subscription->gateway !== 'stripe' || !$subscription->gateway_id) {
            return false;
        }

        // Get the current plan
        $plan = $subscription->plan;

        // Get price ID for the new billing cycle
        $priceId = $plan->getStripePriceId($newBillingCycle);

        if (!$priceId) {
            return false;
        }

        // Get the subscription from Stripe
        $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->gateway_id);

        // Update the subscription item
        $this->stripe->subscriptionItems->update(
            $stripeSubscription->items->data[0]->id,
            ['price' => $priceId]
        );

        // Update the local subscription
        $subscription->billing_cycle = $newBillingCycle;
        $subscription->save();

        return true;
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(Subscription $subscription, bool $immediately = false): bool
    {
        if ($subscription->gateway !== 'stripe' || !$subscription->gateway_id) {
            return false;
        }

        if ($immediately) {
            // Cancel immediately
            $this->stripe->subscriptions->cancel($subscription->gateway_id);

            $subscription->status = 'canceled';
            $subscription->ends_at = now();
        } else {
            // Cancel at period end
            $this->stripe->subscriptions->update($subscription->gateway_id, [
                'cancel_at_period_end' => true,
            ]);
        }

        // Update local subscription
        $subscription->cancelled_at = now();
        $subscription->auto_renew = false;

        if (!$immediately) {
            $subscription->ends_at = $subscription->next_billing_date;
        }

        $subscription->save();

        return true;
    }

    /**
     * Resume a canceled subscription
     */
    public function resumeSubscription(Subscription $subscription): bool
    {
        if ($subscription->gateway !== 'stripe' || !$subscription->gateway_id) {
            return false;
        }

        // Only can resume if canceled at period end
        try {
            $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->gateway_id);

            if ($stripeSubscription->status !== 'active' || $stripeSubscription->cancel_at_period_end !== true) {
                return false;
            }

            // Resume subscription
            $this->stripe->subscriptions->update($subscription->gateway_id, [
                'cancel_at_period_end' => false,
            ]);

            // Update local subscription
            $subscription->canceled_at = null;
            $subscription->ends_at = null;
            $subscription->auto_renew = true;
            $subscription->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle webhook events from Stripe
     */
    public function handleWebhook(array $payload): array
    {
        $eventType = $payload['type'] ?? null;
        $eventObject = $payload['data']['object'] ?? null;

        if (!$eventType || !$eventObject) {
            return ['success' => false, 'message' => 'Invalid webhook payload'];
        }

        switch ($eventType) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                return $this->handleSubscriptionUpdated($eventObject);

            case 'customer.subscription.deleted':
                return $this->handleSubscriptionDeleted($eventObject);

            case 'invoice.payment_succeeded':
                return $this->handleInvoicePaymentSucceeded($eventObject);

            case 'invoice.payment_failed':
                return $this->handleInvoicePaymentFailed($eventObject);

            default:
                return ['success' => true, 'message' => 'Event not handled'];
        }
    }

    /**
     * Handle subscription updated event
     */
    private function handleSubscriptionUpdated(array $eventObject): array
    {
        $subscriptionId = $eventObject['id'] ?? null;

        if (!$subscriptionId) {
            return ['success' => false, 'message' => 'No subscription ID in event'];
        }

        $subscription = Subscription::where('gateway', 'stripe')
            ->where('gateway_id', $subscriptionId)
            ->first();

        if (!$subscription) {
            return ['success' => false, 'message' => 'Subscription not found'];
        }

        // Update subscription status
        $subscription->gateway_status = $eventObject['status'];
        $subscription->status = $this->mapStripeStatusToLocal($eventObject['status']);

        // Update dates
        if (isset($eventObject['trial_end'])) {
            $subscription->trial_ends_at = Carbon::createFromTimestamp($eventObject['trial_end']);
        }

        if (isset($eventObject['current_period_end'])) {
            $subscription->next_billing_date = Carbon::createFromTimestamp($eventObject['current_period_end']);
        }

        if (isset($eventObject['canceled_at'])) {
            $subscription->canceled_at = Carbon::createFromTimestamp($eventObject['canceled_at']);
        }

        $subscription->save();

        return ['success' => true, 'message' => 'Subscription updated'];
    }

    /**
     * Handle subscription deleted event
     */
    private function handleSubscriptionDeleted(array $eventObject): array
    {
        $subscriptionId = $eventObject['id'] ?? null;

        if (!$subscriptionId) {
            return ['success' => false, 'message' => 'No subscription ID in event'];
        }

        $subscription = Subscription::where('gateway', 'stripe')
            ->where('gateway_id', $subscriptionId)
            ->first();

        if (!$subscription) {
            return ['success' => false, 'message' => 'Subscription not found'];
        }

        // Update subscription
        $subscription->status = 'canceled';
        $subscription->canceled_at = $subscription->canceled_at ?? now();
        $subscription->ends_at = now();
        $subscription->auto_renew = false;
        $subscription->save();

        return ['success' => true, 'message' => 'Subscription deleted'];
    }

    /**
     * Handle invoice payment succeeded event
     */
    private function handleInvoicePaymentSucceeded(array $eventObject): array
    {
        $invoiceId = $eventObject['id'] ?? null;
        $subscriptionId = $eventObject['subscription'] ?? null;

        if (!$invoiceId) {
            return ['success' => false, 'message' => 'No invoice ID in event'];
        }

        // Find subscription if available
        $subscription = null;
        if ($subscriptionId) {
            $subscription = Subscription::where('gateway', 'stripe')
                ->where('gateway_id', $subscriptionId)
                ->first();
        }

        // Find or create invoice
        $invoice = Invoice::where('gateway', 'stripe')
            ->where('gateway_invoice_id', $invoiceId)
            ->first();

        if (!$invoice) {
            // Create new invoice
            $businessId = null;

            if ($subscription) {
                $businessId = $subscription->business_id;
            } else if (isset($eventObject['customer'])) {
                $business = Business::where('stripe_id', $eventObject['customer'])->first();
                if ($business) {
                    $businessId = $business->id;
                }
            }

            if (!$businessId) {
                return ['success' => false, 'message' => 'Cannot determine business for invoice'];
            }

            $invoice = new Invoice([
                'business_id' => $businessId,
                'subscription_id' => $subscription ? $subscription->id : null,
                'gateway' => 'stripe',
                'gateway_invoice_id' => $invoiceId,
                'number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                'total' => $eventObject['amount_paid'] / 100, // Convert from cents
                'currency' => strtoupper($eventObject['currency']),
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $invoice->save();
        } else {
            // Update existing invoice
            $invoice->status = 'paid';
            $invoice->paid_at = now();
            $invoice->save();
        }

        return ['success' => true, 'message' => 'Invoice payment processed'];
    }

    /**
     * Handle invoice payment failed event
     */
    private function handleInvoicePaymentFailed(array $eventObject): array
    {
        $subscriptionId = $eventObject['subscription'] ?? null;

        if (!$subscriptionId) {
            return ['success' => true, 'message' => 'No subscription in failed invoice'];
        }

        $subscription = Subscription::where('gateway', 'stripe')
            ->where('gateway_id', $subscriptionId)
            ->first();

        if (!$subscription) {
            return ['success' => false, 'message' => 'Subscription not found'];
        }

        // Update subscription status
        $subscription->status = 'past_due';
        $subscription->save();

        // TODO: Send notification to business about failed payment

        return ['success' => true, 'message' => 'Invoice payment failure handled'];
    }

    /**
     * Map Stripe subscription status to local status
     */
    private function mapStripeStatusToLocal(string $stripeStatus): string
    {
        $statusMap = [
            'active' => 'active',
            'trialing' => 'trialing',
            'past_due' => 'past_due',
            'unpaid' => 'past_due',
            'canceled' => 'canceled',
            'incomplete' => 'inactive',
            'incomplete_expired' => 'inactive',
        ];

        return $statusMap[$stripeStatus] ?? 'inactive';
    }
}

class PayPalGateway implements PaymentGatewayInterface
{
    // Similar implementation for PayPal...
    // This would include PayPal API calls for subscriptions

    public function createCustomer(Business $business): string
    {
        // PayPal doesn't have a direct customer concept like Stripe
        // Return business ID as reference
        return (string) $business->id;
    }

    public function createSetupIntent(Business $business): array
    {
        // Implementation would use PayPal's payment setup APIs
        // Return client token or similar
        return [];
    }

    public function savePaymentMethod(Business $business, string $paymentMethodId): PaymentMethod
    {
        // Implementation for saving PayPal payment methods
        // This is a placeholder - real implementation would handle PayPal's payment method format
        $paymentMethod = new PaymentMethod([
            'business_id' => $business->id,
            'gateway' => 'paypal',
            'gateway_payment_method_id' => $paymentMethodId,
            'type' => 'paypal',
            'is_default' => $business->paymentMethods()->count() === 0,
        ]);

        $paymentMethod->save();

        return $paymentMethod;
    }

    public function createSubscription(
        Business $business,
        Plan $plan,
        string $billingCycle = 'monthly',
        ?string $paymentMethodId = null,
        ?int $trialDays = null
    ): Subscription {
        // Implementation for creating PayPal subscriptions
        // This is a placeholder - real implementation would create a subscription in PayPal

        $subscription = new Subscription([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'gateway' => 'paypal',
            'gateway_id' => 'pp_' . uniqid(),
            'gateway_status' => 'active',
            'status' => 'active',
            'auto_renew' => true,
        ]);

        if ($trialDays) {
            $subscription->trial_ends_at = now()->addDays($trialDays);
            $subscription->status = 'trialing';
        }

        $subscription->next_billing_date = $billingCycle === 'yearly'
            ? now()->addYear()
            : now()->addMonth();

        $subscription->save();

        return $subscription;
    }

    public function changePlan(Subscription $subscription, Plan $newPlan): bool
    {
        // Implementation for changing plan in PayPal
        // This is a placeholder - real implementation would update the subscription in PayPal

        $subscription->plan_id = $newPlan->id;
        return $subscription->save();
    }

    public function changeBillingCycle(Subscription $subscription, string $newBillingCycle): bool
    {
        // Implementation for changing billing cycle in PayPal
        // This is a placeholder - real implementation would update the subscription in PayPal

        $subscription->billing_cycle = $newBillingCycle;
        return $subscription->save();
    }

    public function cancelSubscription(Subscription $subscription, bool $immediately = false): bool
    {
        // Implementation for canceling subscription in PayPal
        // This is a placeholder - real implementation would cancel the subscription in PayPal

        $subscription->canceled_at = now();
        $subscription->auto_renew = false;

        if ($immediately) {
            $subscription->status = 'canceled';
            $subscription->ends_at = now();
        } else {
            $subscription->ends_at = $subscription->next_billing_date;
        }

        return $subscription->save();
    }

    public function resumeSubscription(Subscription $subscription): bool
    {
        // Implementation for resuming subscription in PayPal
        // This is a placeholder - real implementation would resume the subscription in PayPal

        if ($subscription->ends_at && $subscription->ends_at->isPast()) {
            return false;
        }

        $subscription->canceled_at = null;
        $subscription->ends_at = null;
        $subscription->auto_renew = true;

        return $subscription->save();
    }

    public function handleWebhook(array $payload): array
    {
        // Implementation for handling PayPal webhooks
        // This is a placeholder - real implementation would handle various PayPal event types

        return ['success' => true, 'message' => 'PayPal webhook handled'];
    }
}

// Factory to create the appropriate gateway
class PaymentGatewayFactory
{
    public static function create(string $gateway = null): PaymentGatewayInterface
    {
        // Default to configured gateway if not specified
        $gateway = $gateway ?? config('services.payment.default', 'stripe');

        switch ($gateway) {
            case 'paypal':
                return new PayPalGateway();
            case 'stripe':
            default:
                return new StripeGateway();
        }
    }
}