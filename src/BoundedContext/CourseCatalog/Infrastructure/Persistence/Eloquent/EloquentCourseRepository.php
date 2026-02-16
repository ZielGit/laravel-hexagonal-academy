<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent;

use BoundedContext\CourseCatalog\Domain\Exception\CourseNotFoundException;
use BoundedContext\CourseCatalog\Domain\Model\Course;
use BoundedContext\CourseCatalog\Domain\Repository\CourseRepositoryInterface;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseStatus;
use BoundedContext\CourseCatalog\Domain\ValueObject\InstructorId;
use Shared\Application\Bus\EventBusInterface;
use Shared\Domain\Event\EventStoreInterface;

/**
 * Eloquent Course Repository Implementation
 *
 * Adapter (in Hexagonal Architecture terms) that implements
 * the CourseRepositoryInterface using Event Sourcing:
 *
 * WRITES → Event Store (append-only log of events)
 * READS  → Eloquent Read Model (projected from events)
 */
final class EloquentCourseRepository implements CourseRepositoryInterface
{
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly EventBusInterface $eventBus
    ) {}

    /**
     * {@inheritdoc}
     *
     * Saves events to the event store and publishes domain events.
     * The read model is updated asynchronously via event projections.
     */
    public function save(Course $course): void
    {
        $courseId = $course->getAggregateId();
        $events = $course->pullDomainEvents();

        if (empty($events)) {
            return;
        }

        // Determine expected version for optimistic concurrency
        $expectedVersion = $course->getAggregateVersion() - count($events);

        // Persist events to the event store
        $this->eventStore->append(
            $courseId,
            $events,
            $expectedVersion
        );

        // Publish domain events to the event bus
        // Listeners will update the read model
        $this->eventBus->publishBatch($events);
    }

    /**
     * {@inheritdoc}
     *
     * Reconstitutes the aggregate by replaying all its events
     */
    public function findById(CourseId $courseId): ?Course
    {
        if (! $this->eventStore->exists($courseId)) {
            return null;
        }

        $events = $this->eventStore->load($courseId);

        if (empty($events)) {
            return null;
        }

        return Course::reconstituteFromEvents($courseId, $events);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdOrFail(CourseId $courseId): Course
    {
        $course = $this->findById($courseId);

        if ($course === null) {
            throw CourseNotFoundException::withId($courseId);
        }

        return $course;
    }

    /**
     * {@inheritdoc}
     *
     * NOTE: This reads from the read model (projected table)
     * not from the event store, for performance.
     */
    public function findByInstructor(InstructorId $instructorId): array
    {
        // Get IDs from read model (fast)
        $courseIds = CourseReadModel::query()
            ->where('instructor_id', $instructorId->toString())
            ->pluck('course_id')
            ->toArray();

        // Reconstitute aggregates from event store
        return array_filter(
            array_map(
                fn ($id) => $this->findById(CourseId::fromString($id)),
                $courseIds
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findPublished(): array
    {
        return $this->findByStatus(CourseStatus::PUBLISHED);
    }

    /**
     * {@inheritdoc}
     */
    public function findByStatus(CourseStatus $status): array
    {
        $courseIds = CourseReadModel::query()
            ->where('status', $status->value)
            ->pluck('course_id')
            ->toArray();

        return array_filter(
            array_map(
                fn ($id) => $this->findById(CourseId::fromString($id)),
                $courseIds
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function exists(CourseId $courseId): bool
    {
        return $this->eventStore->exists($courseId);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CourseId $courseId): void
    {
        // In Event Sourcing, deletion is handled via domain events
        // The aggregate itself emits a deletion event
        // We don't physically delete from the event store
        $course = $this->findByIdOrFail($courseId);
        $course->archive('Deleted by user');
        $this->save($course);
    }

    /**
     * {@inheritdoc}
     */
    public function countByInstructor(InstructorId $instructorId): int
    {
        return CourseReadModel::query()
            ->where('instructor_id', $instructorId->toString())
            ->count();
    }
}
