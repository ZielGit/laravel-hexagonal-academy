<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Provider;

use Illuminate\Support\ServiceProvider;
use Shared\Application\Bus\CommandBusInterface;
use Shared\Application\Bus\EventBusInterface;
use Shared\Application\Bus\QueryBusInterface;
use Shared\Domain\Event\EventStoreInterface;
use Shared\Infrastructure\Bus\LaravelCommandBus;
use Shared\Infrastructure\Bus\LaravelEventBus;
use Shared\Infrastructure\Bus\LaravelQueryBus;
use Shared\Infrastructure\Persistence\EventStore\EloquentEventStore;
use Shared\Infrastructure\Persistence\EventStore\EventSerializer;

/**
 * Shared Service Provider
 *
 * Registers shared infrastructure components:
 * - Command/Query/Event buses
 * - Event Store
 * - Event Serializer
 */
final class SharedServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->registerEventSerializer();
        $this->registerEventStore();
        $this->registerBuses();
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this->registerEventMappings();
    }

    private function registerEventSerializer(): void
    {
        // Singleton: una sola instancia del serializer en toda la app
        $this->app->singleton(EventSerializer::class, function () {
            return new EventSerializer;
        });
    }

    private function registerEventStore(): void
    {
        $this->app->singleton(EventStoreInterface::class, function ($app) {
            return new EloquentEventStore(
                $app->make('db.connection'),
                $app->make(EventSerializer::class)
            );
        });
    }

    /**
     * Register CQRS buses
     */
    private function registerBuses(): void
    {
        $this->app->singleton(CommandBusInterface::class, function ($app) {
            return new LaravelCommandBus(
                $app->make(\Illuminate\Contracts\Bus\Dispatcher::class)
            );
        });

        $this->app->singleton(QueryBusInterface::class, function ($app) {
            return new LaravelQueryBus(
                $app->make(\Illuminate\Contracts\Bus\Dispatcher::class)
            );
        });

        $this->app->singleton(EventBusInterface::class, function ($app) {
            return new LaravelEventBus(
                $app->make(\Illuminate\Contracts\Events\Dispatcher::class)
            );
        });
    }

    /**
     * Register event type mappings for deserialization
     */
    private function registerEventMappings(): void
    {
        $serializer = $this->app->make(EventSerializer::class);

        $serializer->register(
            'CourseCreated',
            \BoundedContext\CourseCatalog\Domain\Event\CourseCreated::class
        );
        $serializer->register(
            'CourseUpdated',
            \BoundedContext\CourseCatalog\Domain\Event\CourseUpdated::class
        );
        $serializer->register(
            'CoursePublished',
            \BoundedContext\CourseCatalog\Domain\Event\CoursePublished::class
        );
        $serializer->register(
            'CourseArchived',
            \BoundedContext\CourseCatalog\Domain\Event\CourseArchived::class
        );
        $serializer->register(
            'ModuleAddedToCourse',
            \BoundedContext\CourseCatalog\Domain\Event\ModuleAddedToCourse::class
        );
        $serializer->register(
            'LessonAddedToModule',
            \BoundedContext\CourseCatalog\Domain\Event\LessonAddedToModule::class
        );
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            CommandBusInterface::class,
            QueryBusInterface::class,
            EventBusInterface::class,
            EventStoreInterface::class,
            EventSerializer::class,
        ];
    }
}
