<div class="bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Billing Cycle Toggle -->
        <div class="flex justify-center mb-12">
            <div class="relative bg-gray-100 p-1 rounded-lg inline-flex">
                <button wire:click="$set('billingCycle', 'monthly')" type="button" class="relative py-2 px-6 {{ $billingCycle === 'monthly' ? 'bg-white rounded-md shadow-sm text-gray-900' : 'text-gray-700' }} transition-all duration-200 text-sm font-medium focus:outline-none">
                    Monthly
                </button>
                <button wire:click="$set('billingCycle', 'yearly')" type="button" class="relative py-2 px-6 {{ $billingCycle === 'yearly' ? 'bg-white rounded-md shadow-sm text-gray-900' : 'text-gray-700' }} transition-all duration-200 text-sm font-medium focus:outline-none">
                    Yearly
                    <span class="absolute -top-2 -right-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                        Save 20%
                    </span>
                </button>
            </div>
        </div>

        <!-- Plan Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ count($plans) }} gap-6 mb-12">
            @foreach ($plans as $plan)
            <div class="bg-white rounded-lg border {{ $plan->is_featured ? 'border-indigo-500 ring-2 ring-indigo-500 ring-opacity-50' : 'border-gray-200' }} shadow-sm overflow-hidden">
                <!-- Plan Header -->
                <div class="p-6 border-b border-gray-200 {{ $plan->is_featured ? 'bg-indigo-50' : '' }}">
                    @if ($plan->is_featured)
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 mb-3">
                        Most Popular
                    </span>
                    @endif

                    <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $plan->description }}</p>

                    <div class="mt-4 flex items-baseline">
                        <span class="text-3xl font-extrabold text-gray-900">
                            ${{ $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly }}
                        </span>
                        <span class="ml-1 text-sm font-medium text-gray-500">
                            /{{ $billingCycle === 'yearly' ? 'year' : 'month' }}
                        </span>
                    </div>

                    @if ($plan->trial_days > 0)
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $plan->trial_days }} days free trial
                    </p>
                    @endif
                </div>

                <!-- Plan Features -->
                <div class="p-6 h-72 overflow-y-auto">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Features included:</h4>

                    <ul class="space-y-3">
                        @foreach ($featureGroups['quotas'] as $feature)
                        @php
                        $value = $plan->features->where('id', $feature->id)->first() ?
                        $plan->features->where('id', $feature->id)->first()->pivot->value :
                        '0';
                        @endphp
                        <li class="flex items-start">
                            @if ($value === '0')
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            @else
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            @endif

                            <span class="text-sm text-gray-500">
                                @if ($value === 'unlimited')
                                Unlimited {{ $feature->name }}
                                @elseif ($value === '0')
                                {{ $feature->name }} not included
                                @else
                                {{ $value }} {{ $feature->unit ?? '' }} {{ $feature->name }}
                                @endif
                            </span>
                        </li>
                        @endforeach

                        @foreach ($featureGroups['general'] as $feature)
                        @php
                        $value = $plan->features->where('id', $feature->id)->first() ?
                        $plan->features->where('id', $feature->id)->first()->pivot->value :
                        'false';
                        @endphp
                        <li class="flex items-start">
                            @if ($value === 'true')
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            @else
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            @endif

                            <span class="text-sm text-gray-500">
                                {{ $feature->name }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Action Button -->
                <div class="p-6 border-t border-gray-200 bg-gray-50">
                    @auth
                    @if ($currentPlanId === $plan->id)
                    <button disabled class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-400 cursor-not-allowed">
                        Current Plan
                    </button>
                    @else
                    <a href="{{ route('subscription.checkout', ['plan' => $plan->id, 'billing_cycle' => $billingCycle]) }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $plan->is_featured ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-600 hover:bg-gray-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $currentPlanId ? 'Switch Plan' : 'Get Started' }}
                    </a>
                    @endif
                    @else
                    <a href="{{ route('register') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $plan->is_featured ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-600 hover:bg-gray-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Sign Up
                    </a>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>

        <!-- FAQ Section -->
        <div class="max-w-3xl mx-auto mt-16">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Frequently Asked Questions</h2>

            <div class="space-y-8" x-data="{ openQuestion: null }">
                @php
                $faqs = [
                [
                'question' => 'Can I change plans later?',
                'answer' => 'Yes, you can upgrade or downgrade your plan at any time. When upgrading, you\'ll be charged the prorated difference for the remainder of your billing cycle. When downgrading, the new rate will take effect at the start of your next billing cycle.'
                ],
                [
                'question' => 'Is there a long-term contract?',
                'answer' => 'No, all plans are subscription-based and can be canceled at any time. When you cancel, you\'ll retain access until the end of your current billing period.'
                ],
                [
                'question' => 'Do you offer a free trial?',
                'answer' => 'Yes, most of our plans include a 14-day free trial so you can test the features before committing. No credit card is required to start your trial.'
                ],
                [
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept all major credit cards (Visa, MasterCard, American Express, Discover) and PayPal for payment.'
                ],
                [
                'question' => 'Can I get a refund if I\'m not satisfied?',
                'answer' => 'If you\'re not satisfied with our service, please contact our support team within the first 30 days of your subscription for a full refund.'
                ]
                ];
                @endphp

                @foreach ($faqs as $index => $faq)
                <div class="border-b border-gray-200 pb-6">
                    <button @click="openQuestion = (openQuestion === {{ $index }}) ? null : {{ $index }}" class="flex justify-between items-center w-full text-left focus:outline-none">
                        <h3 class="text-lg font-medium text-gray-900">{{ $faq['question'] }}</h3>
                        <span class="ml-6 flex-shrink-0">
                            <svg :class="{'rotate-180': openQuestion === {{ $index }}}" class="h-5 w-5 transform transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                    <div x-show="openQuestion === {{ $index }}" x-collapse class="mt-4 prose prose-sm text-gray-500">
                        <p>{{ $faq['answer'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <p class="text-base text-gray-500">
                    Have more questions? <a href="{{ route('contact') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Contact our support team</a>.
                </p>
            </div>
        </div>
    </div>
</div>
