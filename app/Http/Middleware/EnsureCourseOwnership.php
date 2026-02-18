<?php

namespace App\Http\Middleware;

use BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent\CourseReadModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Course Ownership Middleware
 *
 * Verifies that the authenticated instructor owns the course
 * they're trying to access or modify.
 */
class EnsureCourseOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $courseId = $request->route('courseId');
        $userId = $request->user()->id;

        $course = CourseReadModel::where('course_id', $courseId)
            ->where('instructor_id', $userId)
            ->first();

        if (!$course) {
            return response()->json([
                'message' => 'Course not found or you do not have permission to access it',
            ], 403);
        }

        return $next($request);
    }
}
