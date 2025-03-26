<!-- resources/views/subscription/manage.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subscription Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Subscription Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Current Subscription</h3>
                            <div class="mt-2 flex items-center">
                                @if($subscription->status === 'active')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                                @elseif($subscription->status === 'trialing')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Trial
                                </span>
                                @elseif($subscription->status === 'past_due')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Past Due
                                </span>
                                @elseif($subscription->status === 'canceled')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Canceled
                                </span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                                @endif

                                <span class="ml-2 text-sm text-gray-500">
                                    {{ $subscription->plan->name }} ({{ ucfirst($subscription->billing_cycle) }})
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 md:mt-0">
                            @if($subscription->isCanceled())
                            @if($subscription->ends_at && $subscription->ends_at->isFuture())
                            <div class="text-sm text-gray-500 mb-3">
                                Your subscription will end on {{ $subscription->ends_at->format('F j, Y') }}.
                            </div>
                            <form action="{{ route('subscription.resume') }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Resume Subscription
                                </button>
                            </form>
                            @else
                            <a href="{{ route('subscription.plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Subscribe Again
                            </a>
                            @endif
                            @else
                            <a href="{{ route('subscription.plans') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Change Plan
                            </a>
                            <button x-data="{}" x-on:click="$dispatch('open-modal', 'cancel-subscription')" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Cancel Subscription
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Subscription Details -->
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-3">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">
                                    Plan
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $subscription->plan->name }}
                                </dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">
                                    Price
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    ${{ $subscription->billing_cycle === 'yearly' ? $subscription->plan->price_yearly : $subscription->plan->price_monthly }}/{{ $subscription->billing_cycle === 'yearly' ? 'year' : 'month' }}
                                </dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">
                                    Billing Cycle
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ ucfirst($subscription->billing_cycle) }}
                                </dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">
                                    Start Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $subscription->created_at->format('F j, Y') }}
                                </dd>
                            </div>

                            @if($subscription->onTrial())
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">
                                    Trial Ends
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $subscription->trial_ends_at->format('F j, Y') }}
                                </dd>
                            </div>
                            @endif

                            @if($subscription->next_billing_date)
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">
                                    Next Billing Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $subscription->next_billing_date->format('F j, Y') }}
                                </dd>
                            </div>
                            @endif

                            @if($subscription->canceled_at)
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">
                                    Canceled On
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $subscription->canceled_at->format('F j, Y') }}
                                </dd>
                            </div>

                            @if($subscription->ends_at)
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">
                                    Access Until
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $subscription->ends_at->format('F j, Y') }}
                                </dd>
                            </div>
                            @endif
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Payment Methods</h3>
                        <a href="{{ route('subscription.payment-methods') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Manage Payment Methods
                        </a>
                    </div>

                    @if($paymentMethods->isEmpty())
                    <div class="text-sm text-gray-500">
                        No payment methods added yet.
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($paymentMethods as $method)
                        <div class="flex items-center justify-between p-4 border rounded-md {{ $method->is_default ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}">
                            <div class="flex items-center">
                                @if($method->type === 'card')
                                <div class="flex-shrink-0 h-10 w-14 bg-gray-100 rounded-md flex items-center justify-center">
                                    @if($method->brand === 'visa')
                                    <svg class="h-6 w-10" viewBox="0 0 32 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.5734 0.986084L7.5 9.01305H4.94948L3.0485 2.50238C2.9329 2.0737 2.83371 1.90145 2.43874 1.70855C1.78952 1.39452 0.748407 1.09756 0 0.904572L0.0485577 0.986084H3.83657C4.33993 0.986084 4.79349 1.31725 4.89268 1.85308L5.88144 6.63221L8.36054 0.986084H11.5734ZM21.0825 6.16473C21.0825 3.71828 17.9697 3.55316 17.9939 2.54234C17.9939 2.2151 18.3019 1.86642 19.0481 1.78056C19.4188 1.73763 20.3348 1.7048 21.3479 2.1045L21.7672 0.142723C21.1908 -0.0501285 20.4457 -0.102057 19.5297 -0.102057C16.5712 -0.102057 14.5692 1.36343 14.5692 3.50001C14.5692 5.06519 16.0195 5.93545 17.1298 6.4559C18.2643 6.98864 18.6592 7.32711 18.6592 7.79458C18.6592 8.48851 17.8158 8.80254 17.021 8.80254C15.6676 8.80254 14.9457 8.50558 14.3693 8.28712L13.9501 10.3047C14.5265 10.5231 15.5881 10.7088 16.693 10.7088C19.8058 10.7088 21.0825 9.22489 21.0825 6.16473ZM26.8118 9.01305H29.6998L27.3176 0.986084H24.8627C24.4191 0.986084 24.0485 1.25091 23.875 1.66531L20.097 9.01305H23.2831L23.7752 7.68809H26.5522L26.8118 9.01305ZM24.4434 5.78632L25.5779 2.80036L26.2756 5.78632H24.4434ZM16.2481 0.986084L13.8417 9.01305H10.8347L13.241 0.986084H16.2481Z" fill="#2566AF" />
                                    </svg>
                                    @elseif($method->brand === 'mastercard')
                                    <svg class="h-6 w-10" viewBox="0 0 32 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.58506 19.2273V17.9906C6.58506 17.4138 6.2709 17.0369 5.68506 17.0369C5.39752 17.0369 5.06674 17.1465 4.8459 17.4684C4.67073 17.183 4.39752 17.0369 4.01213 17.0369C3.76982 17.0369 3.53044 17.1191 3.34083 17.3942V17.1055H2.83044V19.2273H3.34966V18.0864C3.34966 17.6959 3.56166 17.5088 3.92003 17.5088C4.27056 17.5088 4.43396 17.704 4.43396 18.0864V19.2273H4.94435V18.0864C4.94435 17.6959 5.16812 17.5088 5.51865 17.5088C5.87802 17.5088 6.03259 17.704 6.03259 18.0864V19.2273H6.58506ZM15.2647 17.1055H14.4253V16.4457H13.9149V17.1055H13.4368V17.5637H13.9149V18.5037C13.9149 18.9932 14.1299 19.2958 14.709 19.2958C14.9219 19.2958 15.1523 19.2273 15.3154 19.1314L15.1879 18.6913C15.043 18.7645 14.8888 18.8016 14.7613 18.8016C14.5219 18.8016 14.4253 18.6638 14.4253 18.4903V17.5637H15.2647V17.1055ZM20.1791 17.0369C19.9042 17.0369 19.7248 17.183 19.625 17.3942V17.1055H19.1214V19.2273H19.6318V18.0682C19.6318 17.696 19.8262 17.4913 20.1436 17.4913C20.2347 17.4913 20.3345 17.51 20.4078 17.5362L20.5176 17.0643C20.4257 17.0472 20.2879 17.0369 20.1791 17.0369ZM12.3733 17.2841C12.1541 17.1246 11.8498 17.0369 11.5278 17.0369C11.0053 17.0369 10.6547 17.2926 10.6547 17.7236C10.6547 18.0752 10.9219 18.2947 11.4857 18.3709L11.7524 18.409C12.0037 18.4471 12.1338 18.5286 12.1338 18.6516C12.1338 18.8182 11.9573 18.9183 11.6441 18.9183C11.3221 18.9183 11.0624 18.7863 10.9012 18.6638L10.6724 19.0449C10.9219 19.2169 11.2591 19.3046 11.6353 19.3046C12.2431 19.3046 12.6509 19.0115 12.6509 18.6311C12.6509 18.2568 12.3661 18.0449 11.8144 17.9714L11.5476 17.9333C11.3399 17.9031 11.1773 17.8481 11.1773 17.7047C11.1773 17.5475 11.3302 17.4581 11.5877 17.4581C11.8732 17.4581 12.1497 17.5857 12.2863 17.6619L12.3733 17.2841ZM25.5089 17.0369C25.234 17.0369 25.0546 17.183 24.9548 17.3942V17.1055H24.4512V19.2273H24.9616V18.0682C24.9616 17.696 25.156 17.4913 25.4733 17.4913C25.5645 17.4913 25.6643 17.51 25.7375 17.5362L25.8473 17.0643C25.7555 17.0472 25.6176 17.0369 25.5089 17.0369ZM17.7586 18.1624C17.7586 18.8562 18.2422 19.3046 18.9722 19.3046C19.2918 19.3046 19.5226 19.2365 19.7364 19.0637L19.5167 18.6262C19.3527 18.7476 19.1708 18.8166 18.9722 18.8166C18.5348 18.8166 18.2643 18.5365 18.2643 18.1624C18.2643 17.7966 18.5348 17.5082 18.9722 17.5082C19.1708 17.5082 19.3527 17.5773 19.5167 17.6986L19.7364 17.2611C19.5226 17.0884 19.2918 17.0203 18.9722 17.0203C18.2422 17.0203 17.7586 17.4686 17.7586 18.1624ZM23.1064 18.1661C23.1064 18.8599 23.5827 19.3046 24.2871 19.3046C24.6068 19.3046 24.8336 19.2279 25.0563 19.0365L24.8438 18.6173C24.681 18.7503 24.4927 18.8272 24.2782 18.8272C23.8843 18.8272 23.6121 18.5585 23.6121 18.1661C23.6121 17.7821 23.8843 17.5134 24.2782 17.5134C24.4927 17.5134 24.681 17.5903 24.8438 17.7233L25.0563 17.3041C24.8336 17.1127 24.6068 17.036 24.2871 17.036C23.5827 17.036 23.1064 17.4806 23.1064 18.1661ZM10.0345 19.2273V16.9891H9.5241V17.3942C9.34332 17.1554 9.07895 17.0369 8.75701 17.0369C8.09509 17.0369 7.58469 17.4842 7.58469 18.1643C7.58469 18.8444 8.09509 19.2917 8.75701 19.2917C9.07895 19.2917 9.34332 19.1732 9.5241 18.9344V19.2273H10.0345ZM8.13716 18.1643C8.13716 17.7966 8.39976 17.5082 8.79399 17.5082C9.17938 17.5082 9.45259 17.7882 9.45259 18.1643C9.45259 18.5365 9.17938 18.8203 8.79399 18.8203C8.39976 18.8203 8.13716 18.5286 8.13716 18.1643ZM16.3232 17.0369C15.6436 17.0369 15.1479 17.4772 15.1479 18.1643C15.1479 18.8515 15.6436 19.2917 16.3321 19.2917C16.6518 19.2917 16.9699 19.1958 17.2247 18.9761L16.9966 18.5843C16.8091 18.7361 16.5791 18.8203 16.3499 18.8203C16.0368 18.8203 15.728 18.6535 15.6705 18.2626H17.3138C17.322 18.1864 17.322 18.1189 17.322 18.0444C17.3138 17.4772 16.9038 17.0369 16.3232 17.0369ZM16.3143 17.4991C16.6164 17.4991 16.8091 17.6933 16.8534 18.0199H15.7433C15.7875 17.729 15.9711 17.4991 16.3143 17.4991ZM21.7386 18.1643C21.7386 18.8599 22.2401 19.3046 22.9622 19.3046C23.2819 19.3046 23.5066 19.235 23.7765 19.0668L23.5498 18.6253C23.3529 18.7539 23.1741 18.818 22.9798 18.818C22.5679 18.818 22.2872 18.5433 22.2872 18.1643C22.2872 17.7853 22.5679 17.5106 22.9798 17.5106C23.1741 17.5106 23.3529 17.5747 23.5498 17.7033L23.7765 17.2618C23.5066 17.0936 23.2819 17.024 22.9622 17.024C22.2401 17.024 21.7386 17.4686 21.7386 18.1643ZM27.1837 18.1661C27.1837 18.8599 27.66 19.3046 28.3644 19.3046C28.6841 19.3046 28.9109 19.2279 29.1335 19.0365L28.9211 18.6173C28.7582 18.7503 28.57 18.8272 28.3555 18.8272C27.9616 18.8272 27.6894 18.5585 27.6894 18.1661C27.6894 17.7821 27.9616 17.5134 28.3555 17.5134C28.57 17.5134 28.7582 17.5903 28.9211 17.7233L29.1335 17.3041C28.9109 17.1127 28.6841 17.036 28.3644 17.036C27.66 17.036 27.1837 17.4806 27.1837 18.1661ZM26.6968 19.2273V17.1055H26.1863V19.2273H26.6968ZM26.6437 16.6381C26.6437 16.4601 26.492 16.3246 26.3203 16.3246C26.1485 16.3246 25.9968 16.4601 25.9968 16.6381C25.9968 16.8162 26.1485 16.9516 26.3203 16.9516C26.492 16.9516 26.6437 16.8162 26.6437 16.6381ZM30.3361 18.5774V17.1055H29.8257V17.3942C29.6531 17.1554 29.3888 17.0369 29.0669 17.0369C28.4049 17.0369 27.8945 17.4842 27.8945 18.1643C27.8945 18.8444 28.4049 19.2917 29.0669 19.2917C29.3888 19.2917 29.6531 19.1732 29.8257 18.9344V19.2273H30.3361V18.5774ZM28.447 18.1643C28.447 17.7966 28.7096 17.5082 29.1038 17.5082C29.4892 17.5082 29.7624 17.7882 29.7624 18.1643C29.7624 18.5365 29.4892 18.8203 29.1038 18.8203C28.7096 18.8166 28.447 18.5286 28.447 18.1643Z" fill="#231F20" />
                                        <path d="M11.9978 1.95667H18.9991V14.6689H11.9978V1.95667Z" fill="#FF5F00" />
                                        <path d="M12.5417 8.31325C12.5417 5.74708 13.7333 3.45325 15.4985 1.95667C13.941 0.737417 11.9448 0 9.76985 0C4.37251 0 0 3.72295 0 8.31279C0 12.9026 4.37251 16.6255 9.76985 16.6255C11.9448 16.6255 13.941 15.8876 15.4985 14.6689C13.7377 13.1733 12.5417 10.8789 12.5417 8.31325Z" fill="#EB001B" />
                                        <path d="M30.9969 8.31325C30.9969 12.9026 26.6244 16.6255 21.2271 16.6255C19.0521 16.6255 17.056 15.8876 15.4984 14.6689C17.2636 13.1733 18.4552 10.8789 18.4552 8.31325C18.4552 5.74753 17.2636 3.4537 15.4984 1.95667C17.0516 0.737417 19.0476 0 21.2271 0C26.6244 0 30.9969 3.72295 30.9969 8.31279V8.31325Z" fill="#F79E1B" />
                                    </svg>
                                    @elseif($method->brand === 'amex')
                                    <svg class="h-6 w-10" viewBox="0 0 32 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M31.1642 0H0.835165C0.373626 0 0 0.373626 0 0.835165V19.1648C0 19.6264 0.373626 20 0.835165 20H31.1642C31.6258 20 32 19.6264 32 19.1648V0.835165C32 0.373626 31.6264 0 31.1642 0Z" fill="#016FD0" />
                                        <path d="M13.8794 10.0001L15.2882 6.45364H17.6351L16.2248 10.0001H13.8794ZM26.8351 12.2417H24.8549V11.1522H26.8923V9.58684H24.8549V8.6088H26.8923L26.9445 7.08367H23.1135V13.7662H26.8351V12.2417ZM22.1889 13.7642H20.4211L20.4099 9.42773L18.2131 13.7642H16.9078L14.6997 9.40488V13.7642H11.9275L11.4044 12.2417H8.41978L7.89772 13.7642H6.00073L8.99187 7.08367H11.2858L14.0895 13.4251V7.08367H16.5667L18.5352 10.9481L20.3069 7.08367H22.1889V13.7642ZM5.22819 7.08367L2.25879 13.7662H4.21073L4.73587 12.2417H7.72243L8.24551 13.7662H10.1998L7.22836 7.08367H5.22819ZM5.31118 10.6291L6.10034 8.64674L6.88541 10.6291H5.31118ZM10.3762 10.6291L9.59109 8.64674L8.80602 10.6291H10.3762Z" fill="white" />
                                    </svg>
                                    @else
                                    <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ ucfirst($method->brand) }} •••• {{ $method->last_four }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Expires {{ $method->exp_month }}/{{ $method->exp_year }}
                                    </div>
                                </div>
                                @elseif($method->type === 'paypal')
                                <div class="flex-shrink-0 h-10 w-14 bg-gray-100 rounded-md flex items-center justify-center">
                                    <svg class="h-6 w-10" viewBox="0 0 32 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.036 3.35L11.089 3.038L10.949 3.342L11.036 3.35ZM29.8147 0.0015H2.17457C0.974178 0.0015 0 0.975679 0 2.17608V11.8242C0 13.0261 0.974178 13.9988 2.17608 13.9988H29.8147C31.0166 13.9988 31.9908 13.0246 31.9908 11.8242V2.17608C31.9908 0.975679 31.0166 0.0015 29.8147 0.0015ZM7.65104 8.90333H5.96503L4.90355 3.35449H6.81484C7.33311 3.35449 7.73959 3.73835 7.80215 4.25208L8.36553 7.9433C8.39802 8.20166 8.26043 8.45393 8.03124 8.58849C7.91077 8.65863 7.7802 8.70109 7.65104 8.90333ZM13.5561 6.00441C13.3118 7.90962 11.8407 9.15582 9.87274 9.15582C9.03884 9.15582 8.31439 8.89443 7.84169 8.42779L7.7802 8.90333H6.13906L7.54214 2.09521H9.22815L9.04801 3.09632C9.52071 2.53598 10.2542 2.22218 11.1643 2.22218C12.7386 2.22218 13.7366 3.10392 13.7366 4.47168C13.7366 4.97933 13.6801 5.48698 13.5561 6.00441ZM11.089 3.038C11.3908 3.05365 11.6838 3.21068 11.8724 3.47206C12.0397 3.72584 12.1303 4.02754 12.1303 4.38144C12.1303 4.95741 12.0046 5.53338 11.7554 5.95602C11.5063 6.37865 11.0757 6.64003 10.6014 6.64003C10.3054 6.64003 10.0545 6.54744 9.87274 6.37865C9.69158 6.20683 9.59892 5.97663 9.59892 5.69751C9.59892 5.13665 9.71939 4.58186 9.97316 4.15923C10.2269 3.7366 10.6617 3.47522 11.089 3.38263V3.038ZM18.2064 6.33928C17.9623 8.24298 16.4912 9.49069 14.5277 9.49069C13.6938 9.49069 12.9694 9.22931 12.4967 8.76267L12.4352 9.23536H10.794L12.197 2.42554H13.883L13.7029 3.43117C14.1756 2.87083 14.9091 2.55551 15.8192 2.55551C17.3934 2.55603 18.3915 3.43574 18.3915 4.8035C18.3915 5.31115 18.335 5.81881 18.2064 6.33928ZM15.7485 3.37654C16.0503 3.3922 16.3433 3.54923 16.5319 3.81061C16.6992 4.06439 16.7898 4.36609 16.7898 4.71999C16.7898 5.29597 16.6642 5.87194 16.4149 6.29457C16.1657 6.71721 15.7351 6.97859 15.2609 6.97859C14.9649 6.97859 14.7139 6.886 14.5328 6.71721C14.3511 6.54539 14.259 6.31519 14.259 6.03607C14.259 5.47521 14.3795 4.92042 14.6333 4.49779C14.887 4.07515 15.3217 3.81378 15.7485 3.37654ZM19.9693 9.23536H18.2833L19.6939 2.42554H21.3799L19.9693 9.23536ZM22.0943 2.09546H23.7803L22.3697 8.90582H20.6837L22.0943 2.09546ZM25.3631 8.90328H23.6771L25.0878 2.09521H26.7737L25.3631 8.90328ZM29.8147 9.02573C29.2544 9.02573 28.8006 8.57194 28.8006 8.01159V5.98193H28.3264V4.57408H28.8006V3.44471H30.4866V4.57408H31.2461V5.98193H30.4866V7.65487C30.4866 7.82872 30.6267 7.97013 30.8017 7.97013H31.2461V9.02573H29.8147Z" fill="#253B80" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        PayPal Account
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($method->is_default)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Default
                            </span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Subscription Features -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Plan Features & Usage</h3>
                        <a href="{{ route('subscription.features') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            View All Features
                        </a>
                    </div>

                    @php
                    $features = $business->getFeatures();
                    $quotaFeatures = collect($features)->filter(function ($feature) {
                    return $feature['has_access'] && $feature['quota'] !== null;
                    })->take(3);
                    @endphp

                    <div class="space-y-4">
                        @foreach($quotaFeatures as $feature)
                        <div>
                            <div class="flex justify-between text-sm font-medium text-gray-900 mb-1">
                                <span>{{ $feature['name'] }}</span>
                                <span>{{ $feature['used'] }} / {{ $feature['quota'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ min(100, ($feature['used'] / $feature['quota']) * 100) }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Subscription Modal -->
    <x-modal name="cancel-subscription" :show="false" maxWidth="md">
        <form method="POST" action="{{ route('subscription.cancel') }}" class="p-6">
            @csrf

            <h2 class="text-lg font-medium text-gray-900">
                Cancel Subscription
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Are you sure you want to cancel your subscription? You'll continue to have access until {{ $subscription->next_billing_date ? $subscription->next_billing_date->format('F j, Y') : 'the end of your current period' }}.
            </p>

            <div class="mt-4">
                <x-label for="reason" value="Reason for canceling (optional)" />
                <x-textarea id="reason" name="reason" class="mt-1 block w-full" />
            </div>

            <div class="mt-4 flex items-center">
                <input id="immediately" name="immediately" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <label for="immediately" class="ml-2 text-sm text-gray-600">
                    Cancel immediately (You'll lose access right away)
                </label>
            </div>

            <div class="mt-6 flex justify-end">
                <x-button type="button" x-on:click="$dispatch('close')" class="bg-white border border-gray-300 text-gray-700 mr-3">
                    Nevermind
                </x-button>

                <x-button class="bg-red-600 hover:bg-red-700">
                    Cancel Subscription
                </x-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
