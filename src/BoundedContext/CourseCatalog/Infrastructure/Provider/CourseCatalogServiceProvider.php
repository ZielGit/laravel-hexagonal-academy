<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Provider;

use BoundedContext\CourseCatalog\Application\Projection\CourseProjectorInterface;
use BoundedContext\CourseCatalog\Application\UseCase\CreateCourse\CreateCourseCommand;
use BoundedContext\CourseCatalog\Application\UseCase\CreateCourse\CreateCourseHandler;
use BoundedContext\CourseCatalog\Domain\Repository\CourseRepositoryInterface;
use BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent\EloquentCourseRepository;
use BoundedContext\CourseCatalog\Infrastructure\Persistence\Projection\EloquentCourseProjector;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Course Catalog Service Provider
 *
 * Registers all Course Catalog bounded context services:
 * - Repository implementations
 * - Command handlers
 * - Event listeners
 * - Projectors
 */
final class CourseCatalogServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerProjectors();
        $this->app->bind(CreateCourseHandler::class, function ($app) {
            return new CreateCourseHandler(
                $app->make(CourseRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this->registerCommandHandlers();
        $this->registerEventListeners();
    }

    /**
     * Register repositories
     */
    private function registerRepositories(): void
    {
        $this->app->bind(
            CourseRepositoryInterface::class,
            EloquentCourseRepository::class
        );
    }

    /**
     * Register projectors
     */
    private function registerProjectors(): void
    {
        $this->app->singleton(
            CourseProjectorInterface::class,
            EloquentCourseProjector::class
        );
    }

    /**
     * Register command handlers with Laravel's bus
     */
    private function registerCommandHandlers(): void
    {
        Bus::map([
            CreateCourseCommand::class => CreateCourseHandler::class,
            // TODO: Add more command handlers
            // PublishCourseCommand::class => PublishCourseHandler::class,
            // UpdateCourseCommand::class => UpdateCourseHandler::class,
        ]);
    }

    /**
     * Register event listeners for projections
     */
    private function registerEventListeners(): void
    {
        $projector = $this->app->make(CourseProjectorInterface::class);

        Event::listen(
            \BoundedContext\CourseCatalog\Domain\Event\CourseCreated::class,
            [$projector, 'onCourseCreated']
        );
        Event::listen(
            \BoundedContext\CourseCatalog\Domain\Event\CourseUpdated::class,
            [$projector, 'onCourseUpdated']
        );
        Event::listen(
            \BoundedContext\CourseCatalog\Domain\Event\CoursePublished::class,
            [$projector, 'onCoursePublished']
        );
        Event::listen(
            \BoundedContext\CourseCatalog\Domain\Event\CourseArchived::class,
            [$projector, 'onCourseArchived']
        );
        Event::listen(
            \BoundedContext\CourseCatalog\Domain\Event\ModuleAddedToCourse::class,
            [$projector, 'onModuleAddedToCourse']
        );
        Event::listen(
            \BoundedContext\CourseCatalog\Domain\Event\LessonAddedToModule::class,
            [$projector, 'onLessonAddedToModule']
        );
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            CourseRepositoryInterface::class,
            CourseProjectorInterface::class,
            CreateCourseHandler::class,
        ];
    }
}
