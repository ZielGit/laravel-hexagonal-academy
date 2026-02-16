<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Http\Controller;

use BoundedContext\CourseCatalog\Application\UseCase\CreateCourse\CreateCourseCommand;
use BoundedContext\CourseCatalog\Application\UseCase\PublishCourse\PublishCourseCommand;
use BoundedContext\CourseCatalog\Infrastructure\Http\Request\CreateCourseRequest;
use BoundedContext\CourseCatalog\Infrastructure\Http\Request\PublishCourseRequest;
use BoundedContext\CourseCatalog\Infrastructure\Http\Resource\CourseCollection;
use BoundedContext\CourseCatalog\Infrastructure\Http\Resource\CourseResource;
use BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent\CourseReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller;
use Ramsey\Uuid\Uuid;
use Shared\Application\Bus\CommandBusInterface;
use Shared\Application\Bus\QueryBusInterface;

/**
 * Course Controller
 *
 * HTTP adapter that translates HTTP requests into
 * Application layer commands and queries.
 *
 * Responsibilities:
 * - Validate HTTP request (delegated to Form Requests)
 * - Build and dispatch commands/queries
 * - Transform responses into JSON
 *
 * NOT responsible for:
 * - Business logic (that's the Domain's job)
 * - Data access (that's the Repository's job)
 */
final class CourseController extends Controller
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus
    ) {}

    /**
     * POST /api/v1/courses
     *
     * @OA\Post(
     *     path="/api/v1/courses",
     *     summary="Create a new course",
     *     tags={"Courses"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/CreateCourseRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Course created successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function create(CreateCourseRequest $request): JsonResponse
    {
        $command = new CreateCourseCommand(
            courseId: Uuid::uuid4()->toString(),
            title: $request->input('title'),
            description: $request->input('description'),
            price: (float) $request->input('price', 0),
            currency: $request->input('currency', 'USD'),
            level: $request->input('level'),
            instructorId: $request->user()->id,
        );

        $response = $this->commandBus->dispatch($command);

        return response()->json([
            'message' => 'Course created successfully',
            'data' => $response->toArray(),
        ], 201);
    }

    /**
     * GET /api/v1/courses
     *
     * @OA\Get(
     *     path="/api/v1/courses",
     *     summary="List all published courses",
     *     tags={"Courses"},
     *
     *     @OA\Parameter(name="level", in="query", required=false),
     *     @OA\Parameter(name="per_page", in="query", required=false),
     *
     *     @OA\Response(response=200, description="List of courses")
     * )
     */
    public function index(Request $request): ResourceCollection
    {
        $courses = CourseReadModel::query()
            ->published()
            ->when(
                $request->has('level'),
                fn ($q) => $q->byLevel($request->input('level'))
            )
            ->when(
                $request->has('free'),
                fn ($q) => $q->free()
            )
            ->orderByDesc('published_at')
            ->paginate($request->input('per_page', 15));

        return CourseCollection::make($courses);
    }

    /**
     * GET /api/v1/courses/{courseId}
     *
     * @OA\Get(
     *     path="/api/v1/courses/{courseId}",
     *     summary="Get a course by ID",
     *     tags={"Courses"},
     *
     *     @OA\Parameter(name="courseId", in="path", required=true),
     *
     *     @OA\Response(response=200, description="Course details"),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function show(string $courseId): JsonResponse
    {
        $course = CourseReadModel::with(['modules', 'lessons'])
            ->where('course_id', $courseId)
            ->firstOrFail();

        return response()->json([
            'data' => CourseResource::make($course),
        ]);
    }

    /**
     * POST /api/v1/courses/{courseId}/publish
     */
    public function publish(PublishCourseRequest $request, string $courseId): JsonResponse
    {
        $command = new PublishCourseCommand(
            courseId: $courseId,
            instructorId: $request->user()->id,
        );

        $this->commandBus->dispatch($command);

        return response()->json([
            'message' => 'Course published successfully',
        ]);
    }

    /**
     * GET /api/v1/instructor/courses
     *
     * Instructor's own courses (all statuses)
     */
    public function myCoures(Request $request): ResourceCollection
    {
        $courses = CourseReadModel::query()
            ->byInstructor($request->user()->id)
            ->withTrashed()
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return CourseCollection::make($courses);
    }
}
