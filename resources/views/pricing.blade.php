<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>QueuePro - Smart Queue Management System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <body class="font-sans antialiased bg-gray-50">
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-2xl font-bold text-indigo-600">QueuePro</h1>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center sm:space-x-8">
                        <a href="{{ route('pricing') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Pricing</a>
                        <a href="#features" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Features</a>
                        @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Log in</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">Get Started</a>
                        @endauth
                    </div>
                    <div class="flex items-center sm:hidden">
                        <!-- Mobile menu button -->
                        <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state -->
            <div class="mobile-menu hidden sm:hidden">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="{{ route('pricing') }}" class="text-gray-600 hover:text-gray-900 block px-3 py-2 rounded-md text-base font-medium">Pricing</a>
                    <a href="#features" class="text-gray-600 hover:text-gray-900 block px-3 py-2 rounded-md text-base font-medium">Features</a>
                    @auth
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 block px-3 py-2 rounded-md text-base font-medium">Log in</a>
                    <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white block px-3 py-2 rounded-md text-base font-medium">Get Started</a>
                    @endauth
                </div>
            </div>
        </header>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Pricing Header -->
                <div class="text-center mb-12">
                    <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl lg:text-5xl">
                        Choose the right plan for your business
                    </h1>
                    <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                        Whether you're a small business or a large enterprise, we have a plan that fits your needs.
                        All plans include our core features with no hidden fees.
                    </p>
                </div>

                <!-- Pricing Toggle -->
                <div class="flex justify-center mb-12">
                    <div class="relative self-center bg-gray-100 rounded-lg p-1 flex">
                        <button type="button" id="monthly-toggle" class="relative w-1/2 bg-white border-gray-200 rounded-md shadow-sm py-2 text-sm font-medium text-gray-900 whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:z-10 sm:w-auto sm:px-8">
                            Monthly
                        </button>
                        <button type="button" id="annual-toggle" class="ml-0.5 relative w-1/2 border border-transparent rounded-md py-2 text-sm font-medium text-gray-700 whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:z-10 sm:w-auto sm:px-8">
                            Annual
                        </button>
                    </div>
                </div>

                <!-- Pricing Plans -->
                <div class="space-y-12 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-8">
                    <!-- Starter Plan -->
                    <div class="relative p-8 bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900">Starter</h3>
                            <p class="mt-4 flex items-baseline text-gray-900">
                                <span class="text-5xl font-extrabold tracking-tight monthly-price">$29</span>
                                <span class="text-5xl font-extrabold tracking-tight annual-price hidden">$290</span>
                                <span class="ml-1 text-xl font-semibold monthly-label">/month</span>
                                <span class="ml-1 text-xl font-semibold annual-label hidden">/year</span>
                            </p>
                            <p class="mt-6 text-gray-500">Perfect for small businesses or single locations.</p>

                            <ul role="list" class="mt-6 space-y-4">
                                <li class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="ml-3 text-gray-500">API access & integrations</span>
                                </li>
                                <li class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="ml-3 text-gray-500">Dedicated account manager</span>
                                </li>
                                <li class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="ml-3 text-gray-500">24/7 phone & email support</span>
                                </li>
                            </ul>
                        </div>

                        <a href="#" class="mt-8 block w-full bg-indigo-600 border border-transparent rounded-md py-3 px-6 text-center font-medium text-white hover:bg-indigo-700">Contact sales</a>
                    </div>
                </div>

                <!-- FAQs Section -->
                <div class="max-w-4xl mx-auto mt-24">
                    <h2 class="text-3xl font-extrabold text-gray-900 text-center">
                        Frequently asked questions
                    </h2>
                    <div class="mt-12">
                        <dl class="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-12">
                            <div>
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    Can I switch plans later?
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected on your next billing cycle.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    Is there a free trial?
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    Yes, all plans come with a 14-day free trial. No credit card required to get started.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    What payment methods do you accept?
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    We accept all major credit cards, debit cards, and PayPal. For Enterprise plans, we also support invoicing.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    Do you offer discounts for non-profits?
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    Yes, we offer special pricing for non-profit organizations. Please contact our sales team for more information.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    Can I cancel my subscription?
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    You can cancel your subscription at any time. There are no long-term contracts or cancellation fees.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-lg leading-6 font-medium text-gray-900">
                                    Do you offer custom solutions?
                                </dt>
                                <dd class="mt-2 text-base text-gray-500">
                                    Yes, for businesses with specific requirements, we can create custom solutions. Contact our sales team to discuss your needs.
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="mt-24 bg-indigo-50 rounded-lg">
                    <div class="px-6 py-16 sm:px-12 sm:py-20 lg:flex lg:items-center lg:justify-between">
                        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                            <span class="block">Ready to get started?</span>
                            <span class="block text-indigo-600">Sign up today with our 14-day free trial.</span>
                        </h2>
                        <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                            <div class="inline-flex rounded-md shadow">
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Get started
                                </a>
                            </div>
                            <div class="ml-3 inline-flex rounded-md shadow">
                                <a href="#" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50">
                                    Contact sales
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const monthlyToggle = document.getElementById('monthly-toggle');
                const annualToggle = document.getElementById('annual-toggle');
                const monthlyPrices = document.querySelectorAll('.monthly-price');
                const annualPrices = document.querySelectorAll('.annual-price');
                const monthlyLabels = document.querySelectorAll('.monthly-label');
                const annualLabels = document.querySelectorAll('.annual-label');

                monthlyToggle.addEventListener('click', function() {
                    // Update toggle buttons
                    monthlyToggle.classList.add('bg-white', 'border-gray-200', 'shadow-sm', 'text-gray-900');
                    monthlyToggle.classList.remove('border-transparent', 'text-gray-700');
                    annualToggle.classList.remove('bg-white', 'border-gray-200', 'shadow-sm', 'text-gray-900');
                    annualToggle.classList.add('border-transparent', 'text-gray-700');

                    // Show monthly prices, hide annual prices
                    monthlyPrices.forEach(el => el.classList.remove('hidden'));
                    annualPrices.forEach(el => el.classList.add('hidden'));
                    monthlyLabels.forEach(el => el.classList.remove('hidden'));
                    annualLabels.forEach(el => el.classList.add('hidden'));
                });

                annualToggle.addEventListener('click', function() {
                    // Update toggle buttons
                    annualToggle.classList.add('bg-white', 'border-gray-200', 'shadow-sm', 'text-gray-900');
                    annualToggle.classList.remove('border-transparent', 'text-gray-700');
                    monthlyToggle.classList.remove('bg-white', 'border-gray-200', 'shadow-sm', 'text-gray-900');
                    monthlyToggle.classList.add('border-transparent', 'text-gray-700');

                    // Show annual prices, hide monthly prices
                    annualPrices.forEach(el => el.classList.remove('hidden'));
                    monthlyPrices.forEach(el => el.classList.add('hidden'));
                    annualLabels.forEach(el => el.classList.remove('hidden'));
                    monthlyLabels.forEach(el => el.classList.add('hidden'));
                });
            });

        </script>
        @endpush
        <footer class="bg-gray-800">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
                <div class="xl:grid xl:grid-cols-3 xl:gap-8">
                    <div class="space-y-8 xl:col-span-1">
                        <h2 class="text-2xl font-bold text-white">QueuePro</h2>
                        <p class="text-gray-300 text-base">
                            Transforming how businesses manage queues with smart, efficient solutions that improve customer experience.
                        </p>
                        <div class="flex space-x-6">
                            <a href="#" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">Facebook</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">Twitter</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="mt-12 grid grid-cols-2 gap-8 xl:mt-0 xl:col-span-2">
                        <div class="md:grid md:grid-cols-2 md:gap-8">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Solutions</h3>
                                <ul class="mt-4 space-y-4">
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Retail
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Healthcare
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Government
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Banking
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="mt-12 md:mt-0">
                                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Support</h3>
                                <ul class="mt-4 space-y-4">
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Documentation
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            API
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Guides
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            FAQ
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="md:grid md:grid-cols-2 md:gap-8">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Company</h3>
                                <ul class="mt-4 space-y-4">
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            About
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Blog
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Careers
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Press
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="mt-12 md:mt-0">
                                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Legal</h3>
                                <ul class="mt-4 space-y-4">
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Privacy
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Terms
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            Cookie Policy
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="text-base text-gray-300 hover:text-white">
                                            SLA
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-12 border-t border-gray-700 pt-8">
                    <p class="text-base text-gray-400 xl:text-center">
                        &copy; {{ date('Y') }} QueuePro. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>

        <!-- JavaScript for mobile menu toggle -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenuButton = document.querySelector('.mobile-menu-button');
                const mobileMenu = document.querySelector('.mobile-menu');

                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            });

        </script>
    </body>
</html>
