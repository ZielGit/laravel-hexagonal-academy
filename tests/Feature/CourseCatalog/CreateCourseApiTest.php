<?php

namespace Tests\Feature\CourseCatalog;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test for Course Creation API
 *
 * Tests the complete HTTP flow: Request → Controller → Use Case → Response
 */
class CreateCourseApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function instructor_can_create_course(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();

        // Act
        $response = $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/v1/instructor/courses', [
                'title' => 'Advanced Laravel Testing',
                'description' => 'Learn comprehensive testing strategies for Laravel applications including unit, integration, and feature tests',
                'price' => 79.99,
                'currency' => 'USD',
                'level' => 'advanced',
            ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'course_id',
                    'title',
                    'status',
                ],
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Advanced Laravel Testing',
                    'status' => 'draft',
                ],
            ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'Advanced Laravel Testing',
            'instructor_id' => $instructor->id,
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();

        // Act
        $response = $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/v1/instructor/courses', [
                'title' => 'Test',
                // Missing description and level
            ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'level']);
    }

    /** @test */
    public function it_validates_title_length(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();

        // Act
        $response = $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/v1/instructor/courses', [
                'title' => 'Bad', // Too short
                'description' => 'Valid description with sufficient length',
                'level' => 'beginner',
            ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    /** @test */
    public function student_cannot_create_course(): void
    {
        // Arrange
        $student = User::factory()->create(['role' => 'student']);

        // Act
        $response = $this->actingAs($student, 'sanctum')
            ->postJson('/api/v1/instructor/courses', [
                'title' => 'Valid Course Title',
                'description' => 'Valid description with sufficient length',
                'level' => 'beginner',
            ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_course(): void
    {
        // Act
        $response = $this->postJson('/api/v1/instructor/courses', [
            'title' => 'Valid Course Title',
            'description' => 'Valid description with sufficient length',
            'level' => 'beginner',
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function it_stores_course_as_free_when_price_is_zero(): void
    {
        // Arrange
        $instructor = User::factory()->instructor()->create();

        // Act
        $response = $this->actingAs($instructor, 'sanctum')
            ->postJson('/api/v1/instructor/courses', [
                'title' => 'Free Introduction Course',
                'description' => 'A free introductory course for beginners',
                'price' => 0,
                'level' => 'beginner',
            ]);

        // Assert
        $response->assertStatus(201);

        $this->assertDatabaseHas('courses', [
            'title' => 'Free Introduction Course',
            'price_cents' => 0,
        ]);
    }
}
