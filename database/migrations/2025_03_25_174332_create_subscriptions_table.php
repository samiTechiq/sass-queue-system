<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create subscriptions table
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained();
            $table->string('status'); // active, canceled, past_due, trialing, etc.
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly

            // Payment gateway info
            $table->string('gateway')->default('stripe'); // stripe, paypal
            $table->string('gateway_id')->nullable();     // Stripe/PayPal subscription ID
            $table->string('gateway_status')->nullable(); // Status from the gateway

            // Dates
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->text('cancellation_reason')->nullable();
            $table->boolean('auto_renew')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};