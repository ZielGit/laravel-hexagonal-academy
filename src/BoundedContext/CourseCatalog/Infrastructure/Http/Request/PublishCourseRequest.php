<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Publish Course Form Request
 */
final class PublishCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('instructor') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
