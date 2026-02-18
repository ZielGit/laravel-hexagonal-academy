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
        Schema::create('event_store', function (Blueprint $table) {
            $table->uuid('event_id')->primary();
            $table->uuid('aggregate_id')->index();
            $table->string('aggregate_type', 100)->index();
            $table->unsignedInteger('aggregate_version');
            $table->string('event_type', 100)->index();
            $table->json('event_data');
            $table->timestamp('occurred_on', 6); // 6 = microsecond precision
            $table->timestamp('recorded_on', 6)->useCurrent();

            // Composite index for efficient aggregate reconstruction
            $table->index(['aggregate_id', 'aggregate_version']);

            // Index for querying by event type
            $table->index(['event_type', 'recorded_on']);

            // Unique constraint to prevent duplicate events
            $table->unique(['aggregate_id', 'aggregate_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_store');
    }
};
