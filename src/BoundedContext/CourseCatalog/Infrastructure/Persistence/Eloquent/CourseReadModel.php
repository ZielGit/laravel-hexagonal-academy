<?php

declare(strict_types=1);

namespace BoundedContext\CourseCatalog\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Course Read Model (Eloquent)
 *
 * This is the projected view of the Course aggregate.
 * Updated by event projectors listening to domain events.
 *
 * Used ONLY for READ operations (CQRS Query side).
 * NEVER use this model for business logic.
 */
final class CourseReadModel extends Model
{
    use SoftDeletes;

    protected $table = 'courses';

    protected $primaryKey = 'course_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'price_cents',
        'currency',
        'level',
        'status',
        'instructor_id',
        'total_modules',
        'total_lessons',
        'duration_minutes',
        'published_at',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'total_modules' => 'integer',
        'total_lessons' => 'integer',
        'duration_minutes' => 'integer',
        'published_at' => 'datetime',
    ];

    // ============================================
    // Scopes for common queries
    // ============================================

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByInstructor($query, string $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeFree($query)
    {
        return $query->where('price_cents', 0);
    }

    // ============================================
    // Accessors
    // ============================================

    public function getPriceAttribute(): float
    {
        return $this->price_cents / 100;
    }

    public function getIsFreeAttribute(): bool
    {
        return $this->price_cents === 0;
    }

    public function getDurationFormattedAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $mins = $this->duration_minutes % 60;

        return $hours > 0
            ? sprintf('%dh %dm', $hours, $mins)
            : sprintf('%dm', $mins);
    }
}
