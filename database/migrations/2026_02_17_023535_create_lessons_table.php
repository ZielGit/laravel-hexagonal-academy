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
        Schema::create('lessons', function (Blueprint $table) {
            $table->uuid('lesson_id')->primary();
            $table->uuid('module_id')->index();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'article', 'quiz', 'exercise'])->default('video');
            $table->unsignedSmallInteger('duration_minutes')->default(0);
            $table->unsignedSmallInteger('order');
            $table->string('video_url')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->timestamps();

            $table->foreign('module_id')
                  ->references('module_id')
                  ->on('modules')
                  ->onDelete('cascade');

            // Ensure unique order within a module
            $table->unique(['module_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
