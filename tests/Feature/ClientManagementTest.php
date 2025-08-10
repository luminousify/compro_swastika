<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sales;
    private Client $client;

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
        
        $this->client = Client::factory()->create([
            'name' => 'Test Client',
            'url' => 'https://example.com',
            'order' => 1,
        ]);
    }

    public function test_admin_can_access_clients_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/clients');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.clients.index');
        $response->assertViewHas('clients');
    }

    public function test_sales_can_access_clients_index(): void
    {
        $response = $this->actingAs($this->sales)->get('/admin/clients');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.clients.index');
    }

    public function test_clients_are_displayed_in_order(): void
    {
        Client::query()->delete(); // Clear existing
        
        Client::factory()->create(['name' => 'Client C', 'order' => 3]);
        Client::factory()->create(['name' => 'Client A', 'order' => 1]);
        Client::factory()->create(['name' => 'Client B', 'order' => 2]);
        
        $response = $this->actingAs($this->admin)->get('/admin/clients');
        
        $response->assertStatus(200);
        $clients = $response->viewData('clients');
        
        $this->assertEquals('Client A', $clients[0]->name);
        $this->assertEquals('Client B', $clients[1]->name);
        $this->assertEquals('Client C', $clients[2]->name);
    }

    public function test_admin_can_create_client_with_logo(): void
    {
        $logo = UploadedFile::fake()->image('logo.png', 200, 100);
        
        $clientData = [
            'name' => 'New Client',
            'url' => 'https://newclient.com',
            'logo' => $logo,
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/clients', $clientData);
        
        $response->assertRedirect('/admin/clients');
        $response->assertSessionHas('success', 'Client created successfully');
        
        $this->assertDatabaseHas('clients', [
            'name' => 'New Client',
            'url' => 'https://newclient.com',
        ]);
        
        $client = Client::where('name', 'New Client')->first();
        $this->assertNotNull($client->logo_path);
        Storage::disk('public')->assertExists($client->logo_path);
    }

    public function test_client_url_validation(): void
    {
        // Test invalid URL
        $response = $this->actingAs($this->admin)->post('/admin/clients', [
            'name' => 'Test Client',
            'url' => 'not-a-valid-url',
        ]);
        
        $response->assertSessionHasErrors(['url']);
        
        // Test valid URL without logo
        $response = $this->actingAs($this->admin)->post('/admin/clients', [
            'name' => 'Test Client',
            'url' => 'https://validurl.com',
        ]);
        
        $response->assertRedirect('/admin/clients');
    }

    public function test_admin_can_edit_client(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/clients/{$this->client->id}/edit");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.clients.edit');
        $response->assertViewHas('client', $this->client);
    }

    public function test_admin_can_update_client(): void
    {
        $updateData = [
            'name' => 'Updated Client Name',
            'url' => 'https://updated-url.com',
        ];
        
        $response = $this->actingAs($this->admin)->put("/admin/clients/{$this->client->id}", $updateData);
        
        $response->assertRedirect('/admin/clients');
        $response->assertSessionHas('success', 'Client updated successfully');
        
        $this->client->refresh();
        $this->assertEquals('Updated Client Name', $this->client->name);
        $this->assertEquals('https://updated-url.com', $this->client->url);
    }

    public function test_updating_client_logo_deletes_old_one(): void
    {
        // Set initial logo
        $this->client->logo_path = 'logos/old-logo.png';
        $this->client->save();
        Storage::disk('public')->put('logos/old-logo.png', 'fake content');
        
        $newLogo = UploadedFile::fake()->image('new-logo.png', 200, 100);
        
        $response = $this->actingAs($this->admin)->put("/admin/clients/{$this->client->id}", [
            'name' => $this->client->name,
            'url' => $this->client->url,
            'logo' => $newLogo,
        ]);
        
        $response->assertRedirect('/admin/clients');
        
        // Old logo should be deleted
        Storage::disk('public')->assertMissing('logos/old-logo.png');
        
        // New logo should exist
        $this->client->refresh();
        $this->assertNotNull($this->client->logo_path);
        Storage::disk('public')->assertExists($this->client->logo_path);
    }

    public function test_admin_can_delete_client(): void
    {
        // Set logo to test cleanup
        $this->client->logo_path = 'logos/test-logo.png';
        $this->client->save();
        Storage::disk('public')->put('logos/test-logo.png', 'fake content');
        
        $response = $this->actingAs($this->admin)->delete("/admin/clients/{$this->client->id}");
        
        $response->assertRedirect('/admin/clients');
        $response->assertSessionHas('success', 'Client deleted successfully');
        
        $this->assertDatabaseMissing('clients', ['id' => $this->client->id]);
        Storage::disk('public')->assertMissing('logos/test-logo.png');
    }

    public function test_client_ordering_can_be_updated(): void
    {
        $client1 = Client::factory()->create(['name' => 'Client 1', 'order' => 1]);
        $client2 = Client::factory()->create(['name' => 'Client 2', 'order' => 2]);
        $client3 = Client::factory()->create(['name' => 'Client 3', 'order' => 3]);
        
        $response = $this->actingAs($this->admin)->post('/admin/clients/reorder', [
            'clients' => [
                ['id' => $client3->id, 'order' => 1],
                ['id' => $client1->id, 'order' => 2],
                ['id' => $client2->id, 'order' => 3],
            ]
        ]);
        
        $response->assertJson(['success' => true]);
        
        $client1->refresh();
        $client2->refresh();
        $client3->refresh();
        
        $this->assertEquals(2, $client1->order);
        $this->assertEquals(3, $client2->order);
        $this->assertEquals(1, $client3->order);
    }

    public function test_homepage_displays_maximum_12_clients(): void
    {
        // Create 15 clients
        Client::query()->delete();
        for ($i = 1; $i <= 15; $i++) {
            Client::factory()->create(['name' => "Client $i", 'order' => $i]);
        }
        
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check that only first 12 clients are shown
        for ($i = 1; $i <= 12; $i++) {
            $response->assertSee("Client $i");
        }
        
        // 13th and beyond should not be shown
        $response->assertDontSee("Client 13");
        $response->assertDontSee("Client 14");
        $response->assertDontSee("Client 15");
    }

    public function test_client_search_functionality(): void
    {
        Client::factory()->create(['name' => 'ABC Corporation']);
        Client::factory()->create(['name' => 'XYZ Industries']);
        
        $response = $this->actingAs($this->admin)->get('/admin/clients?search=abc');
        
        $response->assertStatus(200);
        $response->assertSee('ABC Corporation');
        $response->assertDontSee('XYZ Industries');
    }

    public function test_sales_can_manage_clients(): void
    {
        // Sales can create
        $response = $this->actingAs($this->sales)->post('/admin/clients', [
            'name' => 'Sales Client',
            'url' => 'https://salesclient.com',
        ]);
        
        $response->assertRedirect('/admin/clients');
        $this->assertDatabaseHas('clients', ['name' => 'Sales Client']);
        
        // Sales can edit
        $client = Client::where('name', 'Sales Client')->first();
        $response = $this->actingAs($this->sales)->put("/admin/clients/{$client->id}", [
            'name' => 'Updated Sales Client',
            'url' => 'https://updated.com',
        ]);
        
        $response->assertRedirect('/admin/clients');
        
        // Sales can delete
        $response = $this->actingAs($this->sales)->delete("/admin/clients/{$client->id}");
        
        $response->assertRedirect('/admin/clients');
    }

    public function test_client_logo_validation(): void
    {
        // Test file too large (over 2MB)
        $largeLogo = UploadedFile::fake()->create('logo.png', 3000); // 3MB
        
        $response = $this->actingAs($this->admin)->post('/admin/clients', [
            'name' => 'Test Client',
            'url' => 'https://test.com',
            'logo' => $largeLogo,
        ]);
        
        $response->assertSessionHasErrors(['logo']);
        
        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('logo.pdf', 100);
        
        $response = $this->actingAs($this->admin)->post('/admin/clients', [
            'name' => 'Test Client',
            'url' => 'https://test.com',
            'logo' => $invalidFile,
        ]);
        
        $response->assertSessionHasErrors(['logo']);
    }

    public function test_client_pagination_works(): void
    {
        // Create 25 clients
        for ($i = 1; $i <= 25; $i++) {
            Client::factory()->create(['name' => "Client $i"]);
        }
        
        $response = $this->actingAs($this->admin)->get('/admin/clients');
        
        $response->assertStatus(200);
        $clients = $response->viewData('clients');
        
        $this->assertEquals(20, $clients->perPage());
        $this->assertTrue($clients->hasPages());
    }

    public function test_client_without_url_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/clients', [
            'name' => 'Client Without URL',
            'url' => null,
        ]);
        
        $response->assertRedirect('/admin/clients');
        
        $this->assertDatabaseHas('clients', [
            'name' => 'Client Without URL',
            'url' => null,
        ]);
    }
}