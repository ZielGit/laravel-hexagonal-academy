<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Application\UseCase\CreateCourse;

/**
 * Create Course Response
 *
 * Data Transfer Object returned after successful course creation.
 * Keeps the Application layer decoupled from HTTP concerns.
 */
final class CreateCourseResponse
{
    public function __construct(
        public readonly string $courseId,
        public readonly string $title,
        public readonly string $status,
    ) {}

    public function toArray(): array
    {
        return [
            'course_id' => $this->courseId,
            'title' => $this->title,
            'status' => $this->status,
        ];
    }
}
