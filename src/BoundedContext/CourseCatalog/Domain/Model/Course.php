<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Domain\Model;

use BoundedContext\CourseCatalog\Domain\Event\CourseArchived;
use BoundedContext\CourseCatalog\Domain\Event\CourseCreated;
use BoundedContext\CourseCatalog\Domain\Event\CoursePublished;
use BoundedContext\CourseCatalog\Domain\Event\CourseUpdated;
use BoundedContext\CourseCatalog\Domain\Event\LessonAddedToModule;
use BoundedContext\CourseCatalog\Domain\Event\ModuleAddedToCourse;
use BoundedContext\CourseCatalog\Domain\Exception\CourseAlreadyPublishedException;
use BoundedContext\CourseCatalog\Domain\Exception\CourseCannotBeArchivedException;
use BoundedContext\CourseCatalog\Domain\Exception\CourseCannotBeEditedException;
use BoundedContext\CourseCatalog\Domain\Exception\CourseCannotBePublishedException;
use BoundedContext\CourseCatalog\Domain\Exception\DuplicateModuleException;
use BoundedContext\CourseCatalog\Domain\Exception\MaximumModulesExceededException;
use BoundedContext\CourseCatalog\Domain\Exception\ModuleNotFoundException;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseDescription;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseDuration;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseLevel;
use BoundedContext\CourseCatalog\Domain\ValueObject\CoursePrice;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseStatus;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseTitle;
use BoundedContext\CourseCatalog\Domain\ValueObject\InstructorId;
use BoundedContext\CourseCatalog\Domain\ValueObject\ModuleId;
use DateTimeImmutable;
use Shared\Domain\Aggregate\AggregateRoot;

/**
 * Course Aggregate Root
 *
 * The Course is the main aggregate in the Course Catalog bounded context.
 * It uses Event Sourcing to maintain its state through domain events.
 *
 * Business Rules:
 * - A course must have a title, description, and instructor
 * - Only draft courses can be edited
 * - Published courses require at least 1 module with 3+ lessons
 * - Maximum 50 modules per course
 * - Course price can be free or paid
 */
final class Course extends AggregateRoot
{
    private const MAX_MODULES = 50;

    private const MIN_MODULES_TO_PUBLISH = 1;

    private const MIN_LESSONS_TO_PUBLISH = 3;

    private CourseTitle $title;

    private CourseDescription $description;

    private CoursePrice $price;

    private CourseLevel $level;

    private CourseStatus $status;

    private InstructorId $instructorId;

    private ?DateTimeImmutable $publishedAt = null;

    private CourseDuration $duration;

    /**
     * @var array<string, Module>
     */
    private array $modules = [];

    // ============================================
    // CONSTRUCTOR - Private for Event Sourcing
    // ============================================

    private function __construct(CourseId $courseId)
    {
        $this->aggregateId = $courseId;
        $this->duration = CourseDuration::fromMinutes(0);
    }

    // ============================================
    // FACTORY METHOD - Create New Course
    // ============================================

    /**
     * Create a new draft course
     */
    public static function create(
        CourseId $courseId,
        CourseTitle $title,
        CourseDescription $description,
        CoursePrice $price,
        CourseLevel $level,
        InstructorId $instructorId
    ): self {
        $course = new self($courseId);

        $course->recordThat(
            new CourseCreated(
                aggregateId: $courseId->toString(),
                title: $title->toString(),
                description: $description->toString(),
                priceCents: $price->getCents(),
                currency: $price->getCurrency(),
                level: $level->value,
                instructorId: $instructorId->toString(),
                aggregateVersion: 1
            )
        );

        return $course;
    }

    // ============================================
    // COMMAND METHODS - Business Logic
    // ============================================

    /**
     * Update course details
     *
     * @throws CourseCannotBeEditedException
     */
    public function update(
        ?CourseTitle $title = null,
        ?CourseDescription $description = null,
        ?CoursePrice $price = null,
        ?CourseLevel $level = null
    ): void {
        if (! $this->status->canBeEdited()) {
            throw CourseCannotBeEditedException::alreadyPublished(
                CourseId::fromString($this->aggregateId->toString())
            );
        }

        $changes = [];

        if ($title !== null && ! $title->equals($this->title)) {
            $changes['title'] = [
                'old' => $this->title->toString(),
                'new' => $title->toString(),
            ];
        }

        if ($description !== null && $description->toString() !== $this->description->toString()) {
            $changes['description'] = [
                'old' => $this->description->getExcerpt(),
                'new' => $description->getExcerpt(),
            ];
        }

        if ($price !== null && ! $price->equals($this->price)) {
            $changes['price'] = [
                'old' => $this->price->toArray(),
                'new' => $price->toArray(),
            ];
        }

        if ($level !== null && $level !== $this->level) {
            $changes['level'] = [
                'old' => $this->level->value,
                'new' => $level->value,
            ];
        }

        if (! empty($changes)) {
            $this->recordThat(
                new CourseUpdated(
                    aggregateId: $this->aggregateId->toString(),
                    changes: $changes,
                    aggregateVersion: $this->getAggregateVersion() + 1
                )
            );
        }
    }

    /**
     * Publish the course making it available to students
     *
     * @throws CourseAlreadyPublishedException
     * @throws CourseCannotBePublishedException
     */
    public function publish(): void
    {
        if ($this->status->isPublished()) {
            throw CourseAlreadyPublishedException::withId(
                CourseId::fromString($this->aggregateId->toString())
            );
        }

        if (! $this->status->canBePublished()) {
            throw CourseCannotBePublishedException::invalidStatus(
                CourseId::fromString($this->aggregateId->toString()),
                $this->status->value
            );
        }

        if (! $this->hasMinimumContent()) {
            throw CourseCannotBePublishedException::insufficientContent(
                CourseId::fromString($this->aggregateId->toString())
            );
        }

        $this->recordThat(
            new CoursePublished(
                aggregateId: $this->aggregateId->toString(),
                title: $this->title->toString(),
                publishedAt: new DateTimeImmutable,
                aggregateVersion: $this->getAggregateVersion() + 1
            )
        );
    }

    /**
     * Archive the course
     *
     * @throws CourseCannotBeArchivedException
     */
    public function archive(string $reason): void
    {
        if (! $this->status->canBeArchived()) {
            throw CourseCannotBeArchivedException::invalidStatus(
                CourseId::fromString($this->aggregateId->toString()),
                $this->status->value
            );
        }

        $this->recordThat(
            new CourseArchived(
                aggregateId: $this->aggregateId->toString(),
                reason: $reason,
                aggregateVersion: $this->getAggregateVersion() + 1
            )
        );
    }

    /**
     * Add a module to the course
     *
     * @throws MaximumModulesExceededException
     * @throws DuplicateModuleException
     */
    public function addModule(ModuleId $moduleId, string $moduleTitle): void
    {
        if (count($this->modules) >= self::MAX_MODULES) {
            throw MaximumModulesExceededException::forCourse(
                CourseId::fromString($this->aggregateId->toString()),
                self::MAX_MODULES
            );
        }

        // Check for duplicate module title
        foreach ($this->modules as $module) {
            if ($module->getTitle() === $moduleTitle) {
                throw DuplicateModuleException::withTitle(
                    $moduleTitle,
                    CourseId::fromString($this->aggregateId->toString())
                );
            }
        }

        $order = count($this->modules) + 1;

        $this->recordThat(
            new ModuleAddedToCourse(
                aggregateId: $this->aggregateId->toString(),
                moduleId: $moduleId->toString(),
                moduleTitle: $moduleTitle,
                order: $order,
                aggregateVersion: $this->getAggregateVersion() + 1
            )
        );
    }

    /**
     * Add a lesson to a module
     *
     * @throws ModuleNotFoundException
     */
    public function addLessonToModule(
        ModuleId $moduleId,
        string $lessonId,
        string $lessonTitle,
        int $durationMinutes
    ): void {
        if (! isset($this->modules[$moduleId->toString()])) {
            throw ModuleNotFoundException::inCourse(
                $moduleId,
                CourseId::fromString($this->aggregateId->toString())
            );
        }

        $module = $this->modules[$moduleId->toString()];
        $order = count($module->getLessons()) + 1;

        $this->recordThat(
            new LessonAddedToModule(
                aggregateId: $this->aggregateId->toString(),
                moduleId: $moduleId->toString(),
                lessonId: $lessonId,
                lessonTitle: $lessonTitle,
                durationMinutes: $durationMinutes,
                order: $order,
                aggregateVersion: $this->getAggregateVersion() + 1
            )
        );
    }

    // ============================================
    // EVENT APPLY METHODS - State Reconstruction
    // ============================================

    /**
     * Apply CourseCreated event to rebuild state
     */
    protected function applyCourseCreated(CourseCreated $event): void
    {
        $this->title = CourseTitle::fromString($event->getTitle());
        $this->description = CourseDescription::fromString($event->getDescription());
        $this->price = CoursePrice::fromCents(
            $event->getPriceCents(),
            $event->getCurrency()
        );
        $this->level = CourseLevel::fromString($event->getLevel());
        $this->instructorId = InstructorId::fromString($event->getInstructorId());
        $this->status = CourseStatus::DRAFT;
    }

    /**
     * Apply CourseUpdated event
     */
    protected function applyCourseUpdated(CourseUpdated $event): void
    {
        $changes = $event->getChanges();

        if ($event->hasChanged('title')) {
            $this->title = CourseTitle::fromString($changes['title']['new']);
        }

        if ($event->hasChanged('description')) {
            $this->description = CourseDescription::fromString($changes['description']['new']);
        }

        if ($event->hasChanged('price')) {
            $newPrice = $changes['price']['new'];
            $this->price = CoursePrice::fromCents(
                $newPrice['cents'],
                $newPrice['currency']
            );
        }

        if ($event->hasChanged('level')) {
            $this->level = CourseLevel::fromString($changes['level']['new']);
        }
    }

    /**
     * Apply CoursePublished event
     */
    protected function applyCoursePublished(CoursePublished $event): void
    {
        $this->status = CourseStatus::PUBLISHED;
        $this->publishedAt = $event->getPublishedAt();
    }

    /**
     * Apply CourseArchived event
     */
    protected function applyCourseArchived(CourseArchived $event): void
    {
        $this->status = CourseStatus::ARCHIVED;
    }

    /**
     * Apply ModuleAddedToCourse event
     */
    protected function applyModuleAddedToCourse(ModuleAddedToCourse $event): void
    {
        $module = new Module(
            $event->getModuleId(),
            $event->getModuleTitle(),
            $event->getOrder()
        );

        $this->modules[$event->getModuleId()] = $module;
    }

    /**
     * Apply LessonAddedToModule event
     */
    protected function applyLessonAddedToModule(LessonAddedToModule $event): void
    {
        $module = $this->modules[$event->getModuleId()];

        $lesson = new Lesson(
            $event->getLessonId(),
            $event->getLessonTitle(),
            $event->getDurationMinutes(),
            $event->getOrder()
        );

        $module->addLesson($lesson);

        // Recalculate total duration
        $totalMinutes = 0;
        foreach ($this->modules as $m) {
            $totalMinutes += $m->getTotalDuration();
        }
        $this->duration = CourseDuration::fromMinutes($totalMinutes);
    }

    // ============================================
    // QUERY METHODS - State Access
    // ============================================

    public function getTitle(): CourseTitle
    {
        return $this->title;
    }

    public function getDescription(): CourseDescription
    {
        return $this->description;
    }

    public function getPrice(): CoursePrice
    {
        return $this->price;
    }

    public function getLevel(): CourseLevel
    {
        return $this->level;
    }

    public function getStatus(): CourseStatus
    {
        return $this->status;
    }

    public function getInstructorId(): InstructorId
    {
        return $this->instructorId;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getDuration(): CourseDuration
    {
        return $this->duration;
    }

    /**
     * @return array<Module>
     */
    public function getModules(): array
    {
        return array_values($this->modules);
    }

    public function getModuleCount(): int
    {
        return count($this->modules);
    }

    public function getTotalLessonsCount(): int
    {
        return array_reduce(
            $this->modules,
            fn ($total, $module) => $total + count($module->getLessons()),
            0
        );
    }

    /**
     * Check if course has minimum content to be published
     */
    private function hasMinimumContent(): bool
    {
        if (count($this->modules) < self::MIN_MODULES_TO_PUBLISH) {
            return false;
        }

        return $this->getTotalLessonsCount() >= self::MIN_LESSONS_TO_PUBLISH;
    }
}
