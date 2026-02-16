<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Repository;

use BoundedContext\CourseCatalog\Domain\Model\Course;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseStatus;
use BoundedContext\CourseCatalog\Domain\ValueObject\InstructorId;

/**
 * Course Repository Interface
 *
 * Port (in Hexagonal Architecture terms) that defines
 * how the domain interacts with the persistence layer.
 *
 * The domain only knows about this interface, NEVER about
 * its concrete implementation (Eloquent, Doctrine, etc.)
 */
interface CourseRepositoryInterface
{
    /**
     * Save a course (create or update)
     *
     * After persisting, publishes all pending domain events
     */
    public function save(Course $course): void;

    /**
     * Find a course by its ID
     *
     * Reconstitutes the aggregate by replaying its events
     */
    public function findById(CourseId $courseId): ?Course;

    /**
     * Find a course by ID or throw exception
     *
     * @throws \BoundedContext\CourseCatalog\Domain\Exception\CourseNotFoundException
     */
    public function findByIdOrFail(CourseId $courseId): Course;

    /**
     * Find all courses by instructor
     *
     * @return array<Course>
     */
    public function findByInstructor(InstructorId $instructorId): array;

    /**
     * Find all published courses
     *
     * @return array<Course>
     */
    public function findPublished(): array;

    /**
     * Find courses by status
     *
     * @return array<Course>
     */
    public function findByStatus(CourseStatus $status): array;

    /**
     * Check if a course exists
     */
    public function exists(CourseId $courseId): bool;

    /**
     * Delete a course (soft delete via event)
     */
    public function delete(CourseId $courseId): void;

    /**
     * Count total courses by instructor
     */
    public function countByInstructor(InstructorId $instructorId): int;
}
