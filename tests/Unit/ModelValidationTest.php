<?php

namespace Tests\Unit;

use App\Models\Division;
use App\Models\Milestone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_division_slug_uniqueness_constraint(): void
    {
        Division::create([
            'slug' => 'unique-slug',
            'name' => 'First Division',
            'order' => 1
        ]);

        // Attempting to create another division with the same slug should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        Division::create([
            'slug' => 'unique-slug',
            'name' => 'Second Division',
            'order' => 2
        ]);
    }

    public function test_milestone_year_validation(): void
    {
        // Test valid year range
        $milestone = Milestone::create([
            'year' => 2023,
            'text' => 'Valid milestone',
            'order' => 1
        ]);

        $this->assertEquals(2023, $milestone->year);
        $this->assertIsInt($milestone->year);
    }

    public function test_milestone_year_casting(): void
    {
        $milestone = Milestone::create([
            'year' => '2023', // String input
            'text' => 'Test milestone',
            'order' => 1
        ]);

        // Should be cast to integer
        $this->assertIsInt($milestone->year);
        $this->assertEquals(2023, $milestone->year);
    }

    public function test_division_route_key_name(): void
    {
        $division = new Division();
        $this->assertEquals('slug', $division->getRouteKeyName());
    }

    public function test_model_fillable_attributes(): void
    {
        $division = new Division();
        $expectedFillable = [
            'slug',
            'name',
            'description',
            'hero_image_path',
            'order',
        ];
        $this->assertEquals($expectedFillable, $division->getFillable());
    }

    public function test_media_boolean_casting(): void
    {
        $user = \App\Models\User::factory()->create();
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'order' => 1
        ]);

        $media = \App\Models\Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => \App\Enums\MediaType::IMAGE,
            'path_or_embed' => 'test/image.jpg',
            'is_home_slider' => '1', // String input
            'is_featured' => '0', // String input
            'order' => 1,
            'uploaded_by' => $user->id
        ]);

        // Should be cast to boolean
        $this->assertIsBool($media->is_home_slider);
        $this->assertIsBool($media->is_featured);
        $this->assertTrue($media->is_home_slider);
        $this->assertFalse($media->is_featured);
    }

    public function test_contact_message_boolean_casting(): void
    {
        $message = \App\Models\ContactMessage::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Test message',
            'handled' => '1', // String input
            'created_by_ip' => '192.168.1.1',
            'user_agent' => 'Test Agent'
        ]);

        // Should be cast to boolean
        $this->assertIsBool($message->handled);
        $this->assertTrue($message->handled);
    }
}