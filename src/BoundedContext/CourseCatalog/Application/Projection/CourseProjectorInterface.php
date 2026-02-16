<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Application\Projection;

use BoundedContext\CourseCatalog\Domain\Event\CourseArchived;
use BoundedContext\CourseCatalog\Domain\Event\CourseCreated;
use BoundedContext\CourseCatalog\Domain\Event\CoursePublished;
use BoundedContext\CourseCatalog\Domain\Event\CourseUpdated;
use BoundedContext\CourseCatalog\Domain\Event\LessonAddedToModule;
use BoundedContext\CourseCatalog\Domain\Event\ModuleAddedToCourse;

/**
 * Course Projector Interface
 *
 * Defines how the course read model is updated
 * in response to domain events.
 */
interface CourseProjectorInterface
{
    public function onCourseCreated(CourseCreated $event): void;

    public function onCourseUpdated(CourseUpdated $event): void;

    public function onCoursePublished(CoursePublished $event): void;

    public function onCourseArchived(CourseArchived $event): void;

    public function onModuleAddedToCourse(ModuleAddedToCourse $event): void;

    public function onLessonAddedToModule(LessonAddedToModule $event): void;
}
