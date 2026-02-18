<?php

use BoundedContext\CourseCatalog\Infrastructure\Http\Controller\CourseController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
    ]);
});

// ============================================
// API v1 Routes
// ============================================

Route::prefix('v1')->group(function () {

    // ============================================
    // Public Routes (no authentication required)
    // ============================================

    Route::prefix('courses')->group(function () {
        // List published courses (public catalog)
        Route::get('/', [CourseController::class, 'index'])
            ->name('api.courses.index');

        // Get course details (public)
        Route::get('/{courseId}', [CourseController::class, 'show'])
            ->name('api.courses.show');
    });

    // ============================================
    // Protected Routes (require authentication)
    // ============================================

    Route::middleware(['auth:sanctum'])->group(function () {

        // ============================================
        // Instructor Routes
        // ============================================

        Route::prefix('instructor')->middleware(['role:instructor'])->group(function () {

            // Course Management
            Route::prefix('courses')->group(function () {
                // Create a new course
                Route::post('/', [CourseController::class, 'create'])
                    ->name('api.instructor.courses.create');

                // Get instructor's own courses
                Route::get('/', [CourseController::class, 'myCourses'])
                    ->name('api.instructor.courses.index');

                // Get specific course (instructor's own)
                Route::get('/{courseId}', [CourseController::class, 'show'])
                    ->middleware(['course.ownership'])
                    ->name('api.instructor.courses.show');

                // Update course
                Route::put('/{courseId}', [CourseController::class, 'update'])
                    ->middleware(['course.ownership'])
                    ->name('api.instructor.courses.update');

                // Publish course
                Route::post('/{courseId}/publish', [CourseController::class, 'publish'])
                    ->middleware(['course.ownership'])
                    ->name('api.instructor.courses.publish');

                // Archive course
                Route::post('/{courseId}/archive', [CourseController::class, 'archive'])
                    ->middleware(['course.ownership'])
                    ->name('api.instructor.courses.archive');
            });
        });
    });
});

// ============================================
// Fallback Route (404)
// ============================================

Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint not found',
        'available_versions' => ['v1'],
    ], 404);
});
