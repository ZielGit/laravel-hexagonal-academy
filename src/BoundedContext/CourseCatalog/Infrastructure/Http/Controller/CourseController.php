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
use OpenApi\Attributes as OA;
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
#[OA\Info(
    version: "1.0.0",
    title: "Laravel Hexagonal Academy API",
    description: "API description",
    contact: new OA\Contact(
        email: "contact@example.com"
    ),
    license: new OA\License(
        name: "Apache 2.0",
        url: "https://www.apache.org/licenses/LICENSE-2.0.html"
    )
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local development server"
)]
#[OA\Server(
    url: "https://api.example.com",
    description: "Production server"
)]
final class CourseController extends Controller
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus
    ) {}

    #[OA\Post(
        path: "/api/v1/courses",
        summary: "Create a new course",
        tags: ["Courses"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CreateCourseRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Course created successfully"
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized"
            )
        ]
    )]
    public function create(CreateCourseRequest $request): JsonResponse
    {
        $command = new CreateCourseCommand(
            courseId: Uuid::uuid4()->toString(),
            title: $request->input('title'),
            description: $request->input('description'),
            price: (float) $request->input('price', 0),
            currency: $request->input('currency', 'USD'),
            level: $request->input('level'),
            instructorId: $request->user()->uuid,
        );

        $response = $this->commandBus->dispatch($command);

        return response()->json([
            'message' => 'Course created successfully',
            'data' => $response->toArray(),
        ], 201);
    }

    #[OA\Get(
        path: "/api/v1/courses",
        summary: "List all published courses",
        tags: ["Courses"],
        parameters: [
            new OA\Parameter(
                name: "level",
                in: "query",
                required: false,
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of courses"
            )
        ]
    )]
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

    #[OA\Get(
        path: "/api/v1/courses/{courseId}",
        summary: "Get a course by ID",
        tags: ["Courses"],
        parameters: [
            new OA\Parameter(
                name: "courseId",
                in: "path",
                required: true,
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Course details"
            ),
            new OA\Response(
                response: 404,
                description: "Course not found"
            )
        ]
    )]
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
            instructorId: $request->user()->uuid,
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
            ->byInstructor($request->user()->uuid)
            ->withTrashed()
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return CourseCollection::make($courses);
    }
}
