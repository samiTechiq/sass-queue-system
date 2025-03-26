<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\BusinessController;
use App\Http\Middleware\EnsureFeatureAccess;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Controllers\Auth\BusinessRegistrationController;




// Guest routes
Route::middleware('guest')->group(function () {
    // Standard login
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    // Business registration
    Route::get('register', [BusinessRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [BusinessRegistrationController::class, 'register']);

    // Password reset
    // Route::get('forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    // Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    // Route::get('reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    // Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (redirects based on role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Business admin routes
    Route::middleware(['role:business_admin,admin'])->prefix('business')->group(function () {
        Route::get('/settings', [BusinessController::class, 'settings'])->name('business.settings');
        Route::put('/settings', [BusinessController::class, 'updateSettings'])->name('business.settings.update');
        Route::get('/staff', [BusinessController::class, 'staffManagement'])->name('business.staff');
        Route::get('/subscription', [SubscriptionController::class, 'show'])->name('business.subscription');
    });

    // Staff routes (all business users)
    Route::middleware('role:business_admin,staff')->prefix('queues')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('queues.index');
        Route::get('/{queue}', [QueueController::class, 'show'])->name('queues.show');
        // More queue management routes...
    });
});

// Public routes
Route::get('/pricing', [SubscriptionController::class, 'index'])->name('pricing');

// Authentication required routes
Route::middleware(['auth'])->group(function () {
    // Subscription pages
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/plans', [SubscriptionController::class, 'index'])->name('plans');
        Route::get('/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');

        // Manage existing subscription (requires active subscription)
        Route::middleware('subscription.active')->group(function () {
            Route::get('/manage', [SubscriptionController::class, 'manage'])->name('manage');
            Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
            Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
            Route::get('/features', [SubscriptionController::class, 'features'])->name('features');

            // Payment methods
            Route::get('/payment-methods', [SubscriptionController::class, 'paymentMethods'])->name('payment-methods');
            Route::post('/payment-methods', [SubscriptionController::class, 'addPaymentMethod'])->name('payment-methods.add');
            Route::put('/payment-methods/{paymentMethod}/default', [SubscriptionController::class, 'setDefaultPaymentMethod'])->name('payment-methods.default');
            Route::delete('/payment-methods/{paymentMethod}', [SubscriptionController::class, 'deletePaymentMethod'])->name('payment-methods.delete');

            // Invoices
            Route::get('/invoices', [SubscriptionController::class, 'invoices'])->name('invoices');
        });

        // Upgrade/subscription required pages
        Route::get('/required', [SubscriptionController::class, 'required'])->name('required');
        Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
    });

    // Feature-restricted routes - examples for queue management
    Route::prefix('queues')->name('queues.')->group(function () {
        // Basic queue management (requires active subscription)
        Route::middleware('subscription.active')->group(function () {
            Route::get('/', [QueueController::class, 'index'])->name('index');
            Route::get('/create', [QueueController::class, 'create'])->name('create');
            Route::post('/', [QueueController::class, 'store'])->name('store');
            Route::get('/{queue}', [QueueController::class, 'show'])->name('show');
            Route::get('/{queue}/edit', [QueueController::class, 'edit'])->name('edit');
            Route::put('/{queue}', [QueueController::class, 'update'])->name('update');
            Route::delete('/{queue}', [QueueController::class, 'destroy'])->name('destroy');

            // Queue entries
            Route::post('/{queue}/entries', [QueueEntryController::class, 'store'])->name('entries.store');
            Route::put('/{queue}/entries/{entry}', [QueueEntryController::class, 'update'])->name('entries.update');
            Route::delete('/{queue}/entries/{entry}', [QueueEntryController::class, 'destroy'])->name('entries.destroy');
        });

        // Feature-specific routes
        Route::middleware(['subscription.active', 'subscription.feature:' . App\Services\FeatureService::FEATURE_SMS_NOTIFICATIONS])
            ->post('/{queue}/entries/{entry}/notify-sms', [QueueEntryController::class, 'sendSmsNotification'])
            ->name('entries.notify-sms');

        Route::middleware(['subscription.active', 'subscription.feature:' . App\Services\FeatureService::FEATURE_ADVANCED_REPORTING])
            ->get('/{queue}/reports', [QueueController::class, 'generateReport'])
            ->name('reports');

        Route::middleware(['subscription.active', 'subscription.feature:' . App\Services\FeatureService::FEATURE_CUSTOMER_FEEDBACK])
            ->get('/{queue}/feedback', [QueueController::class, 'showFeedback'])
            ->name('feedback');

        // Route::middleware(['subscription.active', 'subscription.feature:' . App\Services\FeatureService::FEATURE_API_ACCESS])
        //     ->get('/api-settings', [ApiSettingsController::class, 'index'])
        //     ->name('api-settings');
    });
});

// Payment webhooks (no CSRF protection)
// Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripeWebhook'])
//     ->name('webhooks.stripe')
//     ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Route::post('/webhooks/paypal', [WebhookController::class, 'handlePayPalWebhook'])
//     ->name('webhooks.paypal')
//     ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::get('/', function () {
    return view('welcome');
});