<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Persistence\Projection;

use BoundedContext\CourseCatalog\Application\Projection\CourseProjectorInterface;
use BoundedContext\CourseCatalog\Domain\Event\CourseArchived;
use BoundedContext\CourseCatalog\Domain\Event\CourseCreated;
use BoundedContext\CourseCatalog\Domain\Event\CoursePublished;
use BoundedContext\CourseCatalog\Domain\Event\CourseUpdated;
use BoundedContext\CourseCatalog\Domain\Event\LessonAddedToModule;
use BoundedContext\CourseCatalog\Domain\Event\ModuleAddedToCourse;
use BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent\CourseReadModel;
use Illuminate\Database\ConnectionInterface;

/**
 * Eloquent Course Projector
 *
 * Listens to domain events and updates the read model (courses table).
 * This is the "write side" of the CQRS read model.
 *
 * Flow:
 * 1. Domain event is dispatched
 * 2. Projector receives it
 * 3. Projector updates the read model (courses table)
 * 4. Query handlers read from the updated table
 */
final class EloquentCourseProjector implements CourseProjectorInterface
{
    public function __construct(
        private readonly ConnectionInterface $db
    ) {}

    /**
     * Project CourseCreated → Insert new row in courses table
     */
    public function onCourseCreated(CourseCreated $event): void
    {
        CourseReadModel::create([
            'course_id' => $event->getAggregateId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'price_cents' => $event->getPriceCents(),
            'currency' => $event->getCurrency(),
            'level' => $event->getLevel(),
            'status' => 'draft',
            'instructor_id' => $event->getInstructorId(),
            'total_modules' => 0,
            'total_lessons' => 0,
            'duration_minutes' => 0,
            'published_at' => null,
        ]);
    }

    /**
     * Project CourseUpdated → Update existing row
     */
    public function onCourseUpdated(CourseUpdated $event): void
    {
        $changes = $event->getChanges();
        $updates = [];

        if ($event->hasChanged('title')) {
            $updates['title'] = $changes['title']['new'];
        }

        if ($event->hasChanged('description')) {
            $updates['description'] = $changes['description']['new'];
        }

        if ($event->hasChanged('price')) {
            $updates['price_cents'] = $changes['price']['new']['cents'];
            $updates['currency'] = $changes['price']['new']['currency'];
        }

        if ($event->hasChanged('level')) {
            $updates['level'] = $changes['level']['new'];
        }

        if (! empty($updates)) {
            CourseReadModel::where('course_id', $event->getAggregateId())
                ->update($updates);
        }
    }

    /**
     * Project CoursePublished → Update status and published_at
     */
    public function onCoursePublished(CoursePublished $event): void
    {
        CourseReadModel::where('course_id', $event->getAggregateId())
            ->update([
                'status' => 'published',
                'published_at' => $event->getPublishedAt()->format('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Project CourseArchived → Update status
     */
    public function onCourseArchived(CourseArchived $event): void
    {
        CourseReadModel::where('course_id', $event->getAggregateId())
            ->update([
                'status' => 'archived',
                'deleted_at' => now(),
            ]);
    }

    /**
     * Project ModuleAddedToCourse → Increment total_modules counter
     */
    public function onModuleAddedToCourse(ModuleAddedToCourse $event): void
    {
        CourseReadModel::where('course_id', $event->getAggregateId())
            ->increment('total_modules');
    }

    /**
     * Project LessonAddedToModule → Increment counters + update duration
     */
    public function onLessonAddedToModule(LessonAddedToModule $event): void
    {
        CourseReadModel::where('course_id', $event->getAggregateId())
            ->update([
                'total_lessons' => $this->db->raw('total_lessons + 1'),
                'duration_minutes' => $this->db->raw(
                    'duration_minutes + '.$event->getDurationMinutes()
                ),
            ]);
    }
}
