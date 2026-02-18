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
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('module_id')->primary();
            $table->uuid('course_id')->index();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('order');
            $table->unsignedSmallInteger('total_lessons')->default(0);
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->timestamps();

            $table->foreign('course_id')
                  ->references('course_id')
                  ->on('courses')
                  ->onDelete('cascade');

            // Ensure unique order within a course
            $table->unique(['course_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
