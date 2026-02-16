<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Course API Resource
 *
 * Transforms the Course Read Model into a JSON response.
 */
final class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => [
                'amount' => $this->price,
                'cents' => $this->price_cents,
                'currency' => $this->currency,
                'is_free' => $this->is_free,
            ],
            'level' => $this->level,
            'status' => $this->status,
            'instructor_id' => $this->instructor_id,
            'stats' => [
                'total_modules' => $this->total_modules,
                'total_lessons' => $this->total_lessons,
                'duration' => $this->duration_formatted,
                'duration_minutes' => $this->duration_minutes,
            ],
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
