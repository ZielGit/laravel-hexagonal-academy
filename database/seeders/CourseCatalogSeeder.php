<?php

namespace Database\Seeders;

use BoundedContext\CourseCatalog\Application\UseCase\CreateCourse\CreateCourseCommand;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Shared\Application\Bus\CommandBusInterface;

class CourseCatalogSeeder extends Seeder
{
    public function __construct(
        private readonly CommandBusInterface $commandBus
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instructors = \App\Models\User::where('role', 'instructor')->get();

        if ($instructors->isEmpty()) {
            $this->command->warn('⚠️  No instructors found. Run UserSeeder first.');

            return;
        }

        $courses = $this->getCourseData();

        foreach ($courses as $index => $courseData) {
            // Alternate between instructors
            $instructor = $instructors[$index % $instructors->count()];

            $command = new CreateCourseCommand(
                courseId: Uuid::uuid4()->toString(),
                title: $courseData['title'],
                description: $courseData['description'],
                price: $courseData['price'],
                currency: 'USD',
                level: $courseData['level'],
                instructorId: $instructor->id,
            );

            try {
                $this->commandBus->dispatch($command);
                $this->command->info("✅ Created course: {$courseData['title']}");
            } catch (\Exception $e) {
                $this->command->error("❌ Failed to create course: {$e->getMessage()}");
            }
        }
    }

    /**
     * Sample course data
     */
    private function getCourseData(): array
    {
        return [
            [
                'title' => 'Laravel Hexagonal Architecture: A Complete Guide',
                'description' => 'Learn how to build scalable, maintainable Laravel applications using Hexagonal Architecture, DDD, and CQRS patterns. This comprehensive course covers everything from basic principles to advanced implementation techniques with real-world examples.',
                'price' => 99.99,
                'level' => 'advanced',
            ],
            [
                'title' => 'Domain-Driven Design for PHP Developers',
                'description' => 'Master the art of Domain-Driven Design in PHP. Learn about bounded contexts, aggregates, value objects, domain events, and how to apply tactical and strategic DDD patterns in your projects. Perfect for intermediate to advanced developers.',
                'price' => 79.99,
                'level' => 'intermediate',
            ],
            [
                'title' => 'Event Sourcing and CQRS in Practice',
                'description' => 'Deep dive into Event Sourcing and Command Query Responsibility Segregation. Build event-sourced systems from scratch, learn about event stores, projections, and how to handle eventual consistency in distributed systems.',
                'price' => 89.99,
                'level' => 'advanced',
            ],
            [
                'title' => 'Clean Architecture for Beginners',
                'description' => 'Start your journey into clean code and clean architecture. Learn the SOLID principles, dependency inversion, and how to structure your applications for long-term maintainability. No prior advanced knowledge required.',
                'price' => 49.99,
                'level' => 'beginner',
            ],
            [
                'title' => 'Microservices with PHP and Laravel',
                'description' => 'Build scalable microservices architectures using PHP and Laravel. Learn about service boundaries, inter-service communication, API gateways, event-driven architecture, and deployment strategies for microservices.',
                'price' => 119.99,
                'level' => 'expert',
            ],
            [
                'title' => 'Testing Strategies for Laravel Applications',
                'description' => 'Comprehensive guide to testing in Laravel: unit tests, integration tests, feature tests, and E2E tests. Learn TDD, mocking, test doubles, and how to write maintainable test suites for hexagonal architecture.',
                'price' => 0.00, // Free course
                'level' => 'intermediate',
            ],
            [
                'title' => 'RESTful API Design Best Practices',
                'description' => 'Learn how to design clean, consistent, and well-documented REST APIs. Cover versioning, authentication, rate limiting, error handling, HATEOAS, and OpenAPI/Swagger documentation.',
                'price' => 59.99,
                'level' => 'intermediate',
            ],
            [
                'title' => 'Introduction to Software Architecture',
                'description' => 'Perfect for beginners wanting to understand software architecture. Learn about layered architecture, hexagonal architecture, clean architecture, and when to use each pattern. Includes practical examples.',
                'price' => 0.00, // Free course
                'level' => 'beginner',
            ],
        ];
    }
}
