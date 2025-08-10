<?php

namespace Tests\Unit;

use App\Enums\MediaType;
use App\Models\Client;
use App\Models\Division;
use App\Models\Machine;
use App\Models\Media;
use App\Models\Product;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_model_relationships_and_scopes(): void
    {
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'order' => 1
        ]);

        $product1 = Product::create([
            'division_id' => $division->id,
            'name' => 'Product 1',
            'description' => 'First product',
            'order' => 2
        ]);

        $product2 = Product::create([
            'division_id' => $division->id,
            'name' => 'Product 2',
            'description' => 'Second product',
            'order' => 1
        ]);

        // Test division relationship
        $this->assertInstanceOf(Division::class, $product1->division);
        $this->assertEquals($division->id, $product1->division->id);

        // Test media relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $product1->media());

        // Test ordered scope
        $orderedProducts = Product::ordered()->get();
        $this->assertEquals('Product 2', $orderedProducts->first()->name);
        $this->assertEquals('Product 1', $orderedProducts->last()->name);

        // Test that products are ordered in division relationship
        $divisionProducts = $division->products;
        $this->assertEquals('Product 2', $divisionProducts->first()->name);
        $this->assertEquals('Product 1', $divisionProducts->last()->name);
    }

    public function test_technology_model_relationships_and_scopes(): void
    {
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'order' => 1
        ]);

        $tech1 = Technology::create([
            'division_id' => $division->id,
            'name' => 'Technology 1',
            'description' => 'First technology',
            'order' => 2
        ]);

        $tech2 = Technology::create([
            'division_id' => $division->id,
            'name' => 'Technology 2',
            'description' => 'Second technology',
            'order' => 1
        ]);

        // Test division relationship
        $this->assertInstanceOf(Division::class, $tech1->division);
        $this->assertEquals($division->id, $tech1->division->id);

        // Test media relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $tech1->media());

        // Test ordered scope
        $orderedTechnologies = Technology::ordered()->get();
        $this->assertEquals('Technology 2', $orderedTechnologies->first()->name);
        $this->assertEquals('Technology 1', $orderedTechnologies->last()->name);

        // Test that technologies are ordered in division relationship
        $divisionTechnologies = $division->technologies;
        $this->assertEquals('Technology 2', $divisionTechnologies->first()->name);
        $this->assertEquals('Technology 1', $divisionTechnologies->last()->name);
    }

    public function test_machine_model_relationships_and_scopes(): void
    {
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'order' => 1
        ]);

        $machine1 = Machine::create([
            'division_id' => $division->id,
            'name' => 'Machine 1',
            'description' => 'First machine',
            'order' => 2
        ]);

        $machine2 = Machine::create([
            'division_id' => $division->id,
            'name' => 'Machine 2',
            'description' => 'Second machine',
            'order' => 1
        ]);

        // Test division relationship
        $this->assertInstanceOf(Division::class, $machine1->division);
        $this->assertEquals($division->id, $machine1->division->id);

        // Test media relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $machine1->media());

        // Test ordered scope
        $orderedMachines = Machine::ordered()->get();
        $this->assertEquals('Machine 2', $orderedMachines->first()->name);
        $this->assertEquals('Machine 1', $orderedMachines->last()->name);

        // Test that machines are ordered in division relationship
        $divisionMachines = $division->machines;
        $this->assertEquals('Machine 2', $divisionMachines->first()->name);
        $this->assertEquals('Machine 1', $divisionMachines->last()->name);
    }

    public function test_media_accessor_methods(): void
    {
        $user = User::factory()->create();
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'order' => 1
        ]);

        // Test image media
        $imageMedia = Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'test/image.jpg',
            'caption' => 'Test Image',
            'is_home_slider' => false,
            'is_featured' => true,
            'width' => 1920,
            'height' => 1080,
            'bytes' => 1024000,
            'order' => 1,
            'uploaded_by' => $user->id
        ]);

        // Test URL accessor (will return placeholder since file doesn't exist)
        $url = $imageMedia->url;
        $this->assertStringContainsString('data:image/svg+xml;base64,', $url);

        // Test WebP URL accessor
        $webpUrl = $imageMedia->webp_url;
        $this->assertStringContainsString('storage/test/image.webp', $webpUrl);

        // Test responsive srcset accessor
        $srcset = $imageMedia->responsive_srcset;
        $this->assertStringContainsString('768w', $srcset);
        $this->assertStringContainsString('1280w', $srcset);
        $this->assertStringContainsString('1920w', $srcset);

        // Test video media
        $videoMedia = Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => MediaType::VIDEO,
            'path_or_embed' => 'https://youtube.com/watch?v=test',
            'caption' => 'Test Video',
            'order' => 2,
            'uploaded_by' => $user->id
        ]);

        // Test video URL accessor
        $this->assertEquals('https://youtube.com/watch?v=test', $videoMedia->url);

        // Test video WebP URL (should be null)
        $this->assertNull($videoMedia->webp_url);

        // Test video srcset (should be empty)
        $this->assertEquals('', $videoMedia->responsive_srcset);
    }

    public function test_client_media_relationship(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'logo_path' => 'clients/test-logo.png',
            'url' => 'https://testclient.com',
            'order' => 1
        ]);

        $media = Media::create([
            'mediable_type' => Client::class,
            'mediable_id' => $client->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'clients/additional-image.jpg',
            'caption' => 'Additional Client Image',
            'order' => 1,
            'uploaded_by' => $user->id
        ]);

        // Test client media relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $client->media());
        $this->assertCount(1, $client->media);
        $this->assertEquals($media->id, $client->media->first()->id);
    }

    public function test_polymorphic_media_with_multiple_entities(): void
    {
        $user = User::factory()->create();
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'order' => 1
        ]);

        $product = Product::create([
            'division_id' => $division->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'order' => 1
        ]);

        $technology = Technology::create([
            'division_id' => $division->id,
            'name' => 'Test Technology',
            'description' => 'Test Description',
            'order' => 1
        ]);

        $machine = Machine::create([
            'division_id' => $division->id,
            'name' => 'Test Machine',
            'description' => 'Test Description',
            'order' => 1
        ]);

        // Create media for each entity
        $divisionMedia = Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'divisions/test.jpg',
            'order' => 1,
            'uploaded_by' => $user->id
        ]);

        $productMedia = Media::create([
            'mediable_type' => Product::class,
            'mediable_id' => $product->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'products/test.jpg',
            'order' => 1,
            'uploaded_by' => $user->id
        ]);

        $technologyMedia = Media::create([
            'mediable_type' => Technology::class,
            'mediable_id' => $technology->id,
            'type' => MediaType::VIDEO,
            'path_or_embed' => 'https://youtube.com/watch?v=tech',
            'order' => 1,
            'uploaded_by' => $user->id
        ]);

        $machineMedia = Media::create([
            'mediable_type' => Machine::class,
            'mediable_id' => $machine->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'machines/test.jpg',
            'order' => 1,
            'uploaded_by' => $user->id
        ]);

        // Test that each entity has its media
        $this->assertCount(1, $division->media);
        $this->assertEquals($divisionMedia->id, $division->media->first()->id);

        $this->assertCount(1, $product->media);
        $this->assertEquals($productMedia->id, $product->media->first()->id);

        $this->assertCount(1, $technology->media);
        $this->assertEquals($technologyMedia->id, $technology->media->first()->id);

        $this->assertCount(1, $machine->media);
        $this->assertEquals($machineMedia->id, $machine->media->first()->id);

        // Test that media points back to correct entities
        $this->assertInstanceOf(Division::class, $divisionMedia->mediable);
        $this->assertInstanceOf(Product::class, $productMedia->mediable);
        $this->assertInstanceOf(Technology::class, $technologyMedia->mediable);
        $this->assertInstanceOf(Machine::class, $machineMedia->mediable);
    }

    public function test_media_ordering_within_relationships(): void
    {
        $user = User::factory()->create();
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'order' => 1
        ]);

        // Create media with different orders
        $media3 = Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'test/image3.jpg',
            'order' => 3,
            'uploaded_by' => $user->id
        ]);

        $media1 = Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'test/image1.jpg',
            'order' => 1,
            'uploaded_by' => $user->id
        ]);

        $media2 = Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'test/image2.jpg',
            'order' => 2,
            'uploaded_by' => $user->id
        ]);

        // Test that media is ordered correctly in relationship
        $orderedMedia = $division->media;
        $this->assertEquals($media1->id, $orderedMedia->get(0)->id);
        $this->assertEquals($media2->id, $orderedMedia->get(1)->id);
        $this->assertEquals($media3->id, $orderedMedia->get(2)->id);
    }
}