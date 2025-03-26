<!-- resources/views/subscription/checkout.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Order Summary -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Order Summary</h3>

                            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                                <div class="flex justify-between mb-4">
                                    <div>
                                        <h4 class="text-base font-medium text-gray-900">{{ $plan->name }} Plan</h4>
                                        <p class="text-sm text-gray-500">{{ $plan->description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-base font-medium text-gray-900">
                                            ${{ $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly }}
                                        </span>
                                        <span class="text-sm text-gray-500 block">
                                            per {{ $billingCycle === 'yearly' ? 'year' : 'month' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm text-gray-500">Subtotal</span>
                                        <span class="text-sm text-gray-900">
                                            ${{ $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly }}
                                        </span>
                                    </div>

                                    @if($plan->trial_days > 0)
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm text-gray-500">Trial Period</span>
                                        <span class="text-sm text-gray-900">{{ $plan->trial_days }} days</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm text-gray-500">First Payment Due</span>
                                        <span class="text-sm text-gray-900">{{ now()->addDays($plan->trial_days)->format('F j, Y') }}</span>
                                    </div>
                                    @endif

                                    <div class="flex justify-between pt-4 border-t border-gray-200">
                                        <span class="text-base font-medium text-gray-900">Total</span>
                                        <span class="text-base font-medium text-gray-900">
                                            ${{ $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly }} / {{ $billingCycle === 'yearly' ? 'year' : 'month' }}
                                        </span>
                                    </div>
                                </div>

                                @if($billingCycle === 'yearly')
                                <div class="mt-4 bg-green-50 p-3 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-green-700">
                                                You're saving {{ round((1 - ($plan->price_yearly / ($plan->price_monthly * 12))) * 100) }}% with annual billing!
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="flex justify-between mb-6">
                                <span class="text-sm font-medium text-gray-700">Billing Cycle</span>
                                <div class="flex items-center">
                                    <a href="{{ route('subscription.checkout', ['plan' => $plan->id, 'billing_cycle' => 'monthly']) }}" class="mr-4 text-sm font-medium {{ $billingCycle === 'monthly' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Monthly
                                    </a>
                                    <a href="{{ route('subscription.checkout', ['plan' => $plan->id, 'billing_cycle' => 'yearly']) }}" class="text-sm font-medium {{ $billingCycle === 'yearly' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Yearly (Save {{ round((1 - ($plan->price_yearly / ($plan->price_monthly * 12))) * 100) }}%)
                                    </a>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4 class="text-base font-medium text-gray-900">Key Features</h4>
                                <ul class="space-y-2">
                                    @foreach($plan->features as $feature)
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm text-gray-500">
                                            @if($feature->type === 'boolean')
                                            {{ $feature->name }}
                                            @else
                                            {{ $feature->pivot->value === 'unlimited' ? 'Unlimited' : $feature->pivot->value }} {{ $feature->name }}
                                            @endif
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Payment Method</h3>

                            <form action="{{ route('subscription.subscribe') }}" method="POST" id="payment-form">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <input type="hidden" name="billing_cycle" value="{{ $billingCycle }}">

                                <!-- Existing Payment Methods -->
                                @if($paymentMethods->isNotEmpty())
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Use Existing Payment Method
                                    </label>

                                    @foreach($paymentMethods as $method)
                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="payment_method_{{ $method->id }}" name="payment_method" value="{{ $method->id }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ $loop->first ? 'checked' : '' }}>
                                        <label for="payment_method_{{ $method->id }}" class="ml-2 block text-sm text-gray-900">
                                            @if($method->type === 'card')
                                            {{ ucfirst($method->brand) }} •••• {{ $method->last_four }}
                                            @else
                                            PayPal Account
                                            @endif
                                        </label>
                                    </div>
                                    @endforeach

                                    <div class="flex items-center mb-2">
                                        <input type="radio" id="payment_method_new" name="payment_method" value="new" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="payment_method_new" class="ml-2 block text-sm text-gray-900">
                                            Use new payment method
                                        </label>
                                    </div>
                                </div>

                                <div id="new-payment-method-wrapper" class="hidden">
                                    @endif

                                    <!-- Stripe Elements Placeholder -->
                                    <div class="mb-6">
                                        <label for="card-element" class="block text-sm font-medium text-gray-700 mb-2">
                                            Credit or debit card
                                        </label>
                                        <div id="card-element" class="p-3 border border-gray-300 rounded-md shadow-sm">
                                            <!-- Stripe Elements will be inserted here -->
                                        </div>
                                        <!-- Used to display form errors -->
                                        <div id="card-errors" role="alert" class="mt-2 text-sm text-red-600"></div>
                                    </div>

                                    @if($paymentMethods->isNotEmpty())
                                </div>
                                @endif

                                <div class="mb-6">
                                    <div class="flex items-start">
                                        <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" required>
                                        <label for="terms" class="ml-2 block text-sm text-gray-500">
                                            I agree to the <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">terms and conditions</a> and <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">privacy policy</a>.
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" id="submit-button" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    @if($plan->trial_days > 0)
                                    Start {{ $plan->trial_days }}-Day Free Trial
                                    @else
                                    Subscribe Now
                                    @endif
                                </button>

                                <p class="mt-4 text-xs text-gray-500 text-center">
                                    @if($plan->trial_days > 0)
                                    You won't be charged until your free trial ends. You can cancel anytime.
                                    @else
                                    You will be charged immediately. You can cancel anytime.
                                    @endif
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create a Stripe client
            const stripe = Stripe('{{ config('
                services.stripe.key ') }}');

            // Create an instance of Elements
            const elements = stripe.elements();

            // Custom styling
            const style = {
                base: {
                    color: '#32325d'
                    , fontFamily: '"Helvetica Neue", Helvetica, sans-serif'
                    , fontSmoothing: 'antialiased'
                    , fontSize: '16px'
                    , '::placeholder': {
                        color: '#aab7c4'
                    }
                }
                , invalid: {
                    color: '#fa755a'
                    , iconColor: '#fa755a'
                }
            };

            // Create a Card Element and mount it
            const card = elements.create('card', {
                style: style
            });
            card.mount('#card-element');

            // Handle real-time validation errors from the card Element
            card.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            // Handle form submission
            const form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const submitButton = document.getElementById('submit-button');
                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';

                @if($paymentMethods - > isNotEmpty())
                // Check if using existing payment method
                const paymentMethodRadios = document.getElementsByName('payment_method');
                let useExisting = false;
                let existingPaymentMethodId = null;

                for (let i = 0; i < paymentMethodRadios.length; i++) {
                    if (paymentMethodRadios[i].checked && paymentMethodRadios[i].value !== 'new') {
                        useExisting = true;
                        existingPaymentMethodId = paymentMethodRadios[i].value;
                        break;
                    }
                }

                if (useExisting) {
                    // Submit form with existing payment method
                    form.submit();
                    return;
                }
                @endif

                // Use Stripe to handle payment method
                stripe.confirmCardSetup('{{ $setupIntent['
                    client_secret '] }}', {
                        payment_method: {
                            card: card
                            , billing_details: {
                                name: '{{ $business->name }}'
                            }
                        }
                    }).then(function(result) {
                    if (result.error) {
                        // Show error to customer
                        const errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        submitButton.disabled = false;
                        submitButton.textContent = '{{ $plan->trial_days > 0 ? "Start " . $plan->trial_days . "-Day Free Trial" : "Subscribe Now" }}';
                    } else {
                        // Add payment method ID to form
                        const hiddenInput = document.createElement('input');
                        hiddenInput.setAttribute('type', 'hidden');
                        hiddenInput.setAttribute('name', 'payment_method_id');
                        hiddenInput.setAttribute('value', result.setupIntent.payment_method);
                        form.appendChild(hiddenInput);

                        // Submit form
                        form.submit();
                    }
                });
            });

            @if($paymentMethods - > isNotEmpty())
            // Toggle new payment method form
            const paymentMethodRadios = document.getElementsByName('payment_method');
            const newPaymentMethodWrapper = document.getElementById('new-payment-method-wrapper');

            for (let i = 0; i < paymentMethodRadios.length; i++) {
                paymentMethodRadios[i].addEventListener('change', function() {
                    if (this.value === 'new') {
                        newPaymentMethodWrapper.classList.remove('hidden');
                    } else {
                        newPaymentMethodWrapper.classList.add('hidden');
                    }
                });
            }
            @endif
        });

    </script>
    @endpush
</x-app-layout>
