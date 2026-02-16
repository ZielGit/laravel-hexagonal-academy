<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Course Form Request
 *
 * Validates HTTP input before reaching the Controller.
 *
 * @OA\Schema(
 *     schema="CreateCourseRequest",
 *     required={"title","description","level"},
 *
 *     @OA\Property(property="title", type="string", minLength=5, maxLength=200),
 *     @OA\Property(property="description", type="string", minLength=20),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="level", type="string", enum={"beginner","intermediate","advanced","expert"}),
 * )
 */
final class CreateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated users with instructor role can create courses
        return $this->user()?->hasRole('instructor') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'currency' => ['nullable', 'string', 'size:3'],
            'level' => ['required', 'string', 'in:beginner,intermediate,advanced,expert'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Course title is required',
            'title.min' => 'Course title must be at least 5 characters',
            'title.max' => 'Course title cannot exceed 200 characters',
            'description.required' => 'Course description is required',
            'description.min' => 'Course description must be at least 20 characters',
            'level.required' => 'Course level is required',
            'level.in' => 'Course level must be: beginner, intermediate, advanced or expert',
        ];
    }
}
