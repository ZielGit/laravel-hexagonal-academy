<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Course API Collection
 *
 * Transforms a paginated list of courses into JSON.
 */
final class CourseCollection extends ResourceCollection
{
    public $collects = CourseResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage(),
            ],
        ];
    }
}
