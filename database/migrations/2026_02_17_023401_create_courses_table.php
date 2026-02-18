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
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('course_id')->primary();
            $table->string('title', 200);
            $table->text('description');
            $table->unsignedInteger('price_cents')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'expert']);
            $table->enum('status', ['draft', 'published', 'archived', 'suspended'])
                  ->default('draft')
                  ->index();
            $table->uuid('instructor_id')->index();
            $table->unsignedSmallInteger('total_modules')->default(0);
            $table->unsignedSmallInteger('total_lessons')->default(0);
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['status', 'published_at']);
            $table->index(['instructor_id', 'status']);
            $table->index('level');
            $table->index('price_cents'); // For filtering free vs paid courses
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
