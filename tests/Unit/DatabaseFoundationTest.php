<?php

namespace Tests\Unit;

use App\Enums\MediaType;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Division;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_tables_exist_with_proper_structure(): void
    {
        // Test that all required tables exist
        $this->assertTrue(\Schema::hasTable('users'));
        $this->assertTrue(\Schema::hasTable('settings'));
        $this->assertTrue(\Schema::hasTable('divisions'));
        $this->assertTrue(\Schema::hasTable('media'));
        $this->assertTrue(\Schema::hasTable('milestones'));
        $this->assertTrue(\Schema::hasTable('clients'));
        $this->assertTrue(\Schema::hasTable('contact_messages'));
    }

    public function test_user_model_relationships_and_scopes(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        
        // Test role casting
        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertEquals(UserRole::ADMIN, $user->role);
        
        // Test authorization methods
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSales());
        $this->assertTrue($user->canAccess('settings'));
        $this->assertTrue($user->canAccess('users'));
        
        // Test media relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->uploadedMedia());
    }

    public function test_setting_model_accessor_methods(): void
    {
        $setting = Setting::create([
            'data' => [
                'company_name' => 'Test Company',
                'logo' => 'test-logo.png',
                'visi' => 'Test Vision',
                'misi' => 'Test Mission',
                'home_hero' => [
                    'headline' => 'Test Headline',
                    'subheadline' => 'Test Subheadline'
                ]
            ]
        ]);
        
        $this->assertEquals('Test Company', $setting->getCompanyName());
        $this->assertEquals('test-logo.png', $setting->getLogo());
        $this->assertEquals('Test Vision', $setting->getVisi());
        $this->assertEquals('Test Mission', $setting->getMisi());
        $this->assertEquals([
            'headline' => 'Test Headline',
            'subheadline' => 'Test Subheadline'
        ], $setting->getHomeHero());
    }

    public function test_division_model_relationships_and_scopes(): void
    {
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'description' => 'Test Description',
            'order' => 1
        ]);
        
        // Test route key name
        $this->assertEquals('slug', $division->getRouteKeyName());
        
        // Test relationships exist
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $division->products());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $division->technologies());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $division->machines());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $division->media());
        
        // Test ordered scope
        $division2 = Division::create([
            'slug' => 'test-division-2',
            'name' => 'Test Division 2',
            'order' => 0
        ]);
        
        $orderedDivisions = Division::ordered()->get();
        $this->assertEquals('test-division-2', $orderedDivisions->first()->slug);
        $this->assertEquals('test-division', $orderedDivisions->last()->slug);
    }

    public function test_media_model_polymorphic_relationships(): void
    {
        $user = User::factory()->create();
        $division = Division::create([
            'slug' => 'test-division',
            'name' => 'Test Division',
            'order' => 1
        ]);
        
        $media = Media::create([
            'mediable_type' => Division::class,
            'mediable_id' => $division->id,
            'type' => MediaType::IMAGE,
            'path_or_embed' => 'test/image.jpg',
            'caption' => 'Test Image',
            'is_home_slider' => true,
            'is_featured' => false,
            'width' => 1920,
            'height' => 1080,
            'bytes' => 1024000,
            'order' => 1,
            'uploaded_by' => $user->id
        ]);
        
        // Test polymorphic relationship
        $this->assertInstanceOf(Division::class, $media->mediable);
        $this->assertEquals($division->id, $media->mediable->id);
        
        // Test uploader relationship
        $this->assertInstanceOf(User::class, $media->uploader);
        $this->assertEquals($user->id, $media->uploader->id);
        
        // Test type casting
        $this->assertInstanceOf(MediaType::class, $media->type);
        $this->assertEquals(MediaType::IMAGE, $media->type);
        
        // Test boolean casting
        $this->assertTrue($media->is_home_slider);
        $this->assertFalse($media->is_featured);
    }

    public function test_milestone_model_scopes(): void
    {
        Milestone::create(['year' => 2020, 'text' => 'Milestone 2020', 'order' => 2]);
        Milestone::create(['year' => 2020, 'text' => 'Another 2020', 'order' => 1]);
        Milestone::create(['year' => 2019, 'text' => 'Milestone 2019', 'order' => 1]);
        
        $milestones = Milestone::byYear()->get();
        
        // Should be ordered by year ASC, then order ASC
        $this->assertEquals(2019, $milestones->first()->year);
        $this->assertEquals(2020, $milestones->get(1)->year);
        $this->assertEquals(1, $milestones->get(1)->order); // First 2020 milestone
        $this->assertEquals(2020, $milestones->last()->year);
        $this->assertEquals(2, $milestones->last()->order); // Second 2020 milestone
    }

    public function test_database_constraints_and_indexes(): void
    {
        // Test unique constraint on division slug
        Division::create(['slug' => 'unique-slug', 'name' => 'Test', 'order' => 1]);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        Division::create(['slug' => 'unique-slug', 'name' => 'Test 2', 'order' => 2]);
    }

    public function test_contact_message_model_scopes(): void
    {
        $message1 = ContactMessage::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Test message',
            'handled' => false,
            'created_by_ip' => '192.168.1.1',
            'user_agent' => 'Test Agent'
        ]);
        
        $message2 = ContactMessage::create([
            'name' => 'Jane Smith',
            'company' => 'Test Company',
            'phone' => '+1234567890',
            'message' => 'Another message',
            'handled' => true,
            'created_by_ip' => '192.168.1.2',
            'user_agent' => 'Another Agent'
        ]);
        
        // Test unhandled scope
        $unhandled = ContactMessage::unhandled()->get();
        $this->assertCount(1, $unhandled);
        $this->assertEquals('John Doe', $unhandled->first()->name);
        
        // Test searchable scope
        $searchResults = ContactMessage::searchable('Jane')->get();
        $this->assertCount(1, $searchResults);
        $this->assertEquals('Jane Smith', $searchResults->first()->name);
        
        $ipSearchResults = ContactMessage::searchable('192.168.1.2')->get();
        $this->assertCount(1, $ipSearchResults);
        $this->assertEquals('Jane Smith', $ipSearchResults->first()->name);
    }

    public function test_client_model_scopes(): void
    {
        // Create more than 12 clients to test homepage limit
        for ($i = 1; $i <= 15; $i++) {
            Client::create([
                'name' => "Client {$i}",
                'logo_path' => "client-{$i}.png",
                'url' => "https://client{$i}.com",
                'order' => $i
            ]);
        }
        
        $homepageClients = Client::forHomepage()->get();
        $this->assertCount(12, $homepageClients);
        $this->assertEquals('Client 1', $homepageClients->first()->name);
        $this->assertEquals('Client 12', $homepageClients->last()->name);
    }
}