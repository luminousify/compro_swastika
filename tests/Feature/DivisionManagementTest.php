<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Machine;
use App\Models\Media;
use App\Models\Product;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DivisionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sales;
    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'force_password_change' => false,
        ]);
        
        $this->sales = User::factory()->create([
            'role' => 'sales',
            'force_password_change' => false,
        ]);
        
        $this->division = Division::factory()->create([
            'name' => 'Test Division',
            'slug' => 'test-division',
            'description' => 'Test description',
            'order' => 1,
        ]);
    }

    public function test_admin_can_access_divisions_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/divisions');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.divisions.index');
        $response->assertViewHas('divisions');
    }

    public function test_sales_can_access_divisions_index(): void
    {
        $response = $this->actingAs($this->sales)->get('/admin/divisions');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.divisions.index');
    }

    public function test_unauthenticated_users_cannot_access_divisions(): void
    {
        $response = $this->get('/admin/divisions');
        
        $response->assertRedirect('/login');
    }

    public function test_divisions_index_displays_divisions_with_counts(): void
    {
        // Create related content
        Product::factory()->count(3)->create(['division_id' => $this->division->id]);
        Technology::factory()->count(2)->create(['division_id' => $this->division->id]);
        Machine::factory()->count(1)->create(['division_id' => $this->division->id]);
        
        $response = $this->actingAs($this->admin)->get('/admin/divisions');
        
        $response->assertStatus(200);
        $response->assertSee('Test Division');
        $response->assertSee('3'); // Products count
        $response->assertSee('2'); // Technologies count
        $response->assertSee('1'); // Machines count
    }

    public function test_divisions_index_supports_search(): void
    {
        Division::factory()->create(['name' => 'Automotive Division']);
        Division::factory()->create(['name' => 'Industrial Division']);
        
        $response = $this->actingAs($this->admin)->get('/admin/divisions?search=automotive');
        
        $response->assertStatus(200);
        $response->assertSee('Automotive Division');
        $response->assertDontSee('Industrial Division');
    }

    public function test_admin_can_access_create_division_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/divisions/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.divisions.create');
    }

    public function test_admin_can_create_division(): void
    {
        $divisionData = [
            'name' => 'New Division',
            'description' => 'New division description',
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/divisions', $divisionData);
        
        $response->assertRedirect('/admin/divisions');
        $response->assertSessionHas('success', 'Division created successfully');
        
        $this->assertDatabaseHas('divisions', [
            'name' => 'New Division',
            'slug' => 'new-division',
            'description' => 'New division description',
        ]);
    }

    public function test_division_slug_is_auto_generated(): void
    {
        $divisionData = [
            'name' => 'Division With Spaces & Special Characters!',
            'description' => 'Test description',
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/divisions', $divisionData);
        
        $response->assertRedirect('/admin/divisions');
        
        $this->assertDatabaseHas('divisions', [
            'name' => 'Division With Spaces & Special Characters!',
            'slug' => 'division-with-spaces-special-characters',
        ]);
    }

    public function test_division_slug_must_be_unique(): void
    {
        Division::factory()->create(['slug' => 'existing-slug']);
        
        $divisionData = [
            'name' => 'Existing Slug',
            'description' => 'Test',
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/divisions', $divisionData);
        
        $response->assertRedirect('/admin/divisions');
        
        $division = Division::where('name', 'Existing Slug')->first();
        $this->assertEquals('existing-slug-1', $division->slug);
    }

    public function test_admin_can_upload_hero_image_for_division(): void
    {
        $image = UploadedFile::fake()->image('hero.jpg', 1920, 1080);
        
        $divisionData = [
            'name' => 'Division with Hero',
            'description' => 'Test description',
            'hero_image' => $image,
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/divisions', $divisionData);
        
        $response->assertRedirect('/admin/divisions');
        
        $division = Division::where('name', 'Division with Hero')->first();
        $this->assertNotNull($division);
        
        // Check that media was created
        $media = Media::where('mediable_type', Division::class)
                     ->where('mediable_id', $division->id)
                     ->first();
        
        $this->assertNotNull($media);
        $this->assertEquals('image', $media->type->value);
        Storage::disk('public')->assertExists($media->path_or_embed);
    }

    public function test_division_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/divisions', []);
        
        $response->assertSessionHasErrors(['name', 'description']);
    }

    public function test_admin_can_access_edit_division_form(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/divisions/{$this->division->slug}/edit");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.divisions.edit');
        $response->assertViewHas('division', $this->division);
    }

    public function test_admin_can_update_division(): void
    {
        $updateData = [
            'name' => 'Updated Division Name',
            'description' => 'Updated description',
            'slug' => 'custom-slug',
        ];
        
        $response = $this->actingAs($this->admin)->put("/admin/divisions/{$this->division->slug}", $updateData);
        
        $response->assertRedirect('/admin/divisions');
        $response->assertSessionHas('success', 'Division updated successfully');
        
        $this->division->refresh();
        $this->assertEquals('Updated Division Name', $this->division->name);
        $this->assertEquals('Updated description', $this->division->description);
        $this->assertEquals('custom-slug', $this->division->slug);
    }

    public function test_admin_can_delete_division_without_content(): void
    {
        $emptyDivision = Division::factory()->create(['name' => 'Empty Division']);
        
        $response = $this->actingAs($this->admin)->delete("/admin/divisions/{$emptyDivision->slug}");
        
        $response->assertRedirect('/admin/divisions');
        $response->assertSessionHas('success', 'Division deleted successfully');
        
        $this->assertDatabaseMissing('divisions', ['id' => $emptyDivision->id]);
    }

    public function test_deleting_division_with_content_shows_warning(): void
    {
        // Create related content
        Product::factory()->count(2)->create(['division_id' => $this->division->id]);
        Technology::factory()->create(['division_id' => $this->division->id]);
        
        $response = $this->actingAs($this->admin)->get("/admin/divisions/{$this->division->slug}/delete");
        
        $response->assertStatus(200);
        $response->assertSee('This division contains:');
        $response->assertSee('2 Products');
        $response->assertSee('1 Technology');
        $response->assertSee('Are you sure you want to delete this division and all its content?');
    }

    public function test_cascade_delete_removes_all_related_content(): void
    {
        // Create related content
        $product = Product::factory()->create(['division_id' => $this->division->id]);
        $technology = Technology::factory()->create(['division_id' => $this->division->id]);
        $machine = Machine::factory()->create(['division_id' => $this->division->id]);
        
        // Create media for division
        $media = Media::factory()->create([
            'mediable_type' => Division::class,
            'mediable_id' => $this->division->id,
        ]);
        
        $response = $this->actingAs($this->admin)->delete("/admin/divisions/{$this->division->slug}?confirm=true");
        
        $response->assertRedirect('/admin/divisions');
        
        // Check everything is deleted
        $this->assertDatabaseMissing('divisions', ['id' => $this->division->id]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('technologies', ['id' => $technology->id]);
        $this->assertDatabaseMissing('machines', ['id' => $machine->id]);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_division_ordering_can_be_updated(): void
    {
        $division1 = Division::factory()->create(['name' => 'Division 1', 'order' => 1]);
        $division2 = Division::factory()->create(['name' => 'Division 2', 'order' => 2]);
        $division3 = Division::factory()->create(['name' => 'Division 3', 'order' => 3]);
        
        $response = $this->actingAs($this->admin)->post('/admin/divisions/reorder', [
            'divisions' => [
                ['id' => $division3->id, 'order' => 1],
                ['id' => $division1->id, 'order' => 2],
                ['id' => $division2->id, 'order' => 3],
            ]
        ]);
        
        $response->assertJson(['success' => true]);
        
        $division1->refresh();
        $division2->refresh();
        $division3->refresh();
        
        $this->assertEquals(2, $division1->order);
        $this->assertEquals(3, $division2->order);
        $this->assertEquals(1, $division3->order);
    }

    public function test_divisions_are_displayed_in_order(): void
    {
        // Clear existing divisions first
        Division::query()->delete();
        
        Division::factory()->create(['name' => 'Third', 'order' => 3]);
        Division::factory()->create(['name' => 'First', 'order' => 1]);
        Division::factory()->create(['name' => 'Second', 'order' => 2]);
        
        $response = $this->actingAs($this->admin)->get('/admin/divisions');
        
        $response->assertStatus(200);
        $divisions = $response->viewData('divisions');
        
        $this->assertEquals('First', $divisions[0]->name);
        $this->assertEquals('Second', $divisions[1]->name);
        $this->assertEquals('Third', $divisions[2]->name);
    }

    public function test_division_detail_page_shows_nested_content(): void
    {
        Product::factory()->count(3)->create(['division_id' => $this->division->id]);
        Technology::factory()->count(2)->create(['division_id' => $this->division->id]);
        Machine::factory()->count(1)->create(['division_id' => $this->division->id]);
        
        $response = $this->actingAs($this->admin)->get("/admin/divisions/{$this->division->slug}");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.divisions.show');
        $response->assertViewHas('division');
        $response->assertViewHas('products');
        $response->assertViewHas('technologies');
        $response->assertViewHas('machines');
    }

    public function test_sales_can_manage_divisions(): void
    {
        // Sales should be able to create
        $response = $this->actingAs($this->sales)->post('/admin/divisions', [
            'name' => 'Sales Division',
            'description' => 'Created by sales',
        ]);
        
        $response->assertRedirect('/admin/divisions');
        $this->assertDatabaseHas('divisions', ['name' => 'Sales Division']);
        
        // Sales should be able to edit
        $division = Division::where('name', 'Sales Division')->first();
        $response = $this->actingAs($this->sales)->put("/admin/divisions/{$division->slug}", [
            'name' => 'Updated by Sales',
            'description' => 'Updated description',
            'slug' => $division->slug,
        ]);
        
        $response->assertRedirect('/admin/divisions');
        $this->assertDatabaseHas('divisions', ['name' => 'Updated by Sales']);
    }

    public function test_division_form_shows_current_hero_image(): void
    {
        // Create division with hero image
        $media = Media::factory()->create([
            'mediable_type' => Division::class,
            'mediable_id' => $this->division->id,
            'type' => 'image',
        ]);
        
        $response = $this->actingAs($this->admin)->get("/admin/divisions/{$this->division->slug}/edit");
        
        $response->assertStatus(200);
        $response->assertSee('Current Hero Image');
    }

    public function test_updating_hero_image_deletes_old_one(): void
    {
        // Create initial hero image
        $oldMedia = Media::factory()->create([
            'mediable_type' => Division::class,
            'mediable_id' => $this->division->id,
            'type' => 'image',
            'path_or_embed' => 'images/old-hero.jpg',
        ]);
        
        Storage::disk('public')->put('images/old-hero.jpg', 'fake content');
        
        $newImage = UploadedFile::fake()->image('new-hero.jpg', 1920, 1080);
        
        $response = $this->actingAs($this->admin)->put("/admin/divisions/{$this->division->slug}", [
            'name' => $this->division->name,
            'description' => $this->division->description,
            'slug' => $this->division->slug,
            'hero_image' => $newImage,
        ]);
        
        $response->assertRedirect('/admin/divisions');
        
        // Old media should be deleted
        $this->assertDatabaseMissing('media', ['id' => $oldMedia->id]);
        
        // New media should exist
        $newMedia = Media::where('mediable_type', Division::class)
                        ->where('mediable_id', $this->division->id)
                        ->first();
        
        $this->assertNotNull($newMedia);
        $this->assertNotEquals('images/old-hero.jpg', $newMedia->path_or_embed);
    }
}