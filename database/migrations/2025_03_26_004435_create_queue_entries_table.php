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

        // Create queue entries table (people in specific queues)
        Schema::create('queue_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('waiting'); // waiting, called, serving, served, no_show, cancelled
            $table->integer('position')->nullable(); // Position in queue
            $table->timestamp('called_time')->nullable(); // When customer was called
            $table->timestamp('served_time')->nullable(); // When customer was served
            $table->foreignId('served_by')->nullable()->constrained('users')->nullOnDelete(); // Staff who served
            $table->integer('estimated_wait')->nullable(); // Estimated wait time in minutes
            $table->text('notes')->nullable(); // Staff notes
            $table->json('meta_data')->nullable(); // Additional data
            $table->timestamps();

            // Add index for common queries
            $table->index(['queue_id', 'status']);
            $table->index(['status', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
    }
};