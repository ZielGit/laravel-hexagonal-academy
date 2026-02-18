<?php

namespace Tests\Unit\CourseCatalog\Domain;

use BoundedContext\CourseCatalog\Domain\ValueObject\CourseDescription;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseDuration;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseId;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseLevel;
use BoundedContext\CourseCatalog\Domain\ValueObject\CoursePrice;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseStatus;
use BoundedContext\CourseCatalog\Domain\ValueObject\CourseTitle;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Value Objects Unit Tests
 */
class ValueObjectsTest extends TestCase
{
    /** @test */
    public function course_id_generates_valid_uuid(): void
    {
        $courseId = CourseId::generate();

        $this->assertIsString($courseId->toString());
        $this->assertEquals(36, strlen($courseId->toString()));
    }

    /** @test */
    public function course_title_validates_minimum_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CourseTitle::fromString('Test'); // Less than 5 characters
    }

    /** @test */
    public function course_title_validates_maximum_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CourseTitle::fromString(str_repeat('A', 201)); // More than 200
    }

    /** @test */
    public function course_title_accepts_valid_input(): void
    {
        $title = CourseTitle::fromString('Valid Course Title');
        $this->assertEquals('Valid Course Title', $title->toString());
    }

    /** @test */
    public function course_description_validates_minimum_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CourseDescription::fromString('Too short'); // Less than 20
    }

    /** @test */
    public function course_description_provides_excerpt(): void
    {
        $longDescription = str_repeat('A', 200);
        $description = CourseDescription::fromString($longDescription);

        $excerpt = $description->getExcerpt(50);
        $this->assertEquals(53, strlen($excerpt)); // 50 + '...'
    }

    /** @test */
    public function course_price_handles_free_courses(): void
    {
        $price = CoursePrice::free();

        $this->assertTrue($price->isFree());
        $this->assertEquals(0, $price->getCents());
        $this->assertEquals(0.00, $price->getAmount());
    }

    /** @test */
    public function course_price_converts_amount_to_cents(): void
    {
        $price = CoursePrice::fromAmount(99.99);

        $this->assertEquals(9999, $price->getCents());
        $this->assertEquals(99.99, $price->getAmount());
    }

    /** @test */
    public function course_level_provides_correct_labels(): void
    {
        $this->assertEquals('Beginner', CourseLevel::BEGINNER->getLabel());
        $this->assertEquals('Intermediate', CourseLevel::INTERMEDIATE->getLabel());
        $this->assertEquals('Advanced', CourseLevel::ADVANCED->getLabel());
        $this->assertEquals('Expert', CourseLevel::EXPERT->getLabel());
    }

    /** @test */
    public function course_status_checks_work_correctly(): void
    {
        $this->assertTrue(CourseStatus::DRAFT->isDraft());
        $this->assertFalse(CourseStatus::DRAFT->isPublished());

        $this->assertTrue(CourseStatus::PUBLISHED->isPublished());
        $this->assertFalse(CourseStatus::PUBLISHED->isDraft());
    }

    /** @test */
    public function course_duration_formats_correctly(): void
    {
        $duration = CourseDuration::fromMinutes(125);

        $this->assertEquals(125, $duration->getMinutes());
        $this->assertEquals(2.08, $duration->getHours());
        $this->assertEquals('2h 5m', $duration->getFormattedDuration());
    }

    /** @test */
    public function course_duration_validates_minimum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CourseDuration::fromMinutes(1); // Less than 5 minutes
    }
}
