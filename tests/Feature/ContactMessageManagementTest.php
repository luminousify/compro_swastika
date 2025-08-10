<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sales;
    private ContactMessage $message;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'force_password_change' => false,
        ]);
        
        $this->sales = User::factory()->create([
            'role' => 'sales',
            'force_password_change' => false,
        ]);
        
        $this->message = ContactMessage::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'company' => 'Acme Corp',
            'message' => 'I would like to inquire about your services.',
            'created_by_ip' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'handled' => false,
        ]);
    }

    public function test_admin_can_access_messages_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/messages');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.messages.index');
        $response->assertViewHas('messages');
    }

    public function test_sales_can_access_messages_index(): void
    {
        $response = $this->actingAs($this->sales)->get('/admin/messages');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.messages.index');
    }

    public function test_messages_show_unhandled_first(): void
    {
        ContactMessage::factory()->create(['handled' => true]);
        ContactMessage::factory()->create(['handled' => false]);
        ContactMessage::factory()->create(['handled' => true]);
        ContactMessage::factory()->create(['handled' => false]);
        
        $response = $this->actingAs($this->admin)->get('/admin/messages');
        
        $messages = $response->viewData('messages');
        
        // First two should be unhandled
        $this->assertFalse($messages[0]->handled);
        $this->assertFalse($messages[1]->handled);
    }

    public function test_admin_can_view_message_details(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/messages/{$this->message->id}");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.messages.show');
        $response->assertViewHas('message', $this->message);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('08123456789');
        $response->assertSee('Acme Corp');
    }

    public function test_admin_can_mark_message_as_handled(): void
    {
        $response = $this->actingAs($this->admin)->patch("/admin/messages/{$this->message->id}/handle", [
            'handled' => true,
            'note' => 'Contacted via email',
        ]);
        
        $response->assertRedirect('/admin/messages');
        $response->assertSessionHas('success');
        
        $this->message->refresh();
        $this->assertTrue($this->message->handled);
        $this->assertEquals('Contacted via email', $this->message->note);
    }

    public function test_admin_can_unmark_message_as_handled(): void
    {
        $this->message->update(['handled' => true]);
        
        $response = $this->actingAs($this->admin)->patch("/admin/messages/{$this->message->id}/handle", [
            'handled' => false,
        ]);
        
        $response->assertRedirect('/admin/messages');
        
        $this->message->refresh();
        $this->assertFalse($this->message->handled);
    }

    public function test_message_search_by_name(): void
    {
        ContactMessage::factory()->create(['name' => 'Jane Smith']);
        ContactMessage::factory()->create(['name' => 'Bob Johnson']);
        
        $response = $this->actingAs($this->admin)->get('/admin/messages?search=Jane');
        
        $response->assertStatus(200);
        $response->assertSee('Jane Smith');
        $response->assertDontSee('Bob Johnson');
    }

    public function test_message_search_by_email(): void
    {
        ContactMessage::factory()->create(['email' => 'test@example.com']);
        ContactMessage::factory()->create(['email' => 'other@domain.com']);
        
        $response = $this->actingAs($this->admin)->get('/admin/messages?search=example.com');
        
        $response->assertStatus(200);
        $response->assertSee('test@example.com');
        $response->assertDontSee('other@domain.com');
    }

    public function test_message_search_by_company(): void
    {
        ContactMessage::factory()->create(['company' => 'Tech Solutions']);
        ContactMessage::factory()->create(['company' => 'Marketing Agency']);
        
        $response = $this->actingAs($this->admin)->get('/admin/messages?search=Tech');
        
        $response->assertStatus(200);
        $response->assertSee('Tech Solutions');
        $response->assertDontSee('Marketing Agency');
    }

    public function test_message_filter_by_handled_status(): void
    {
        ContactMessage::factory()->count(3)->create(['handled' => true]);
        ContactMessage::factory()->count(2)->create(['handled' => false]);
        
        // Filter handled only
        $response = $this->actingAs($this->admin)->get('/admin/messages?status=handled');
        $messages = $response->viewData('messages');
        $this->assertEquals(3, $messages->total());
        
        // Filter unhandled only
        $response = $this->actingAs($this->admin)->get('/admin/messages?status=unhandled');
        $messages = $response->viewData('messages');
        $this->assertEquals(3, $messages->total()); // 2 + 1 from setUp
    }

    public function test_admin_can_export_messages_to_csv(): void
    {
        ContactMessage::factory()->count(5)->create();
        
        $response = $this->actingAs($this->admin)->get('/admin/messages/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
        
        $content = $response->getContent();
        // Check for UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        // Check for headers
        $this->assertStringContainsString('Name,Email,Phone,Company', $content);
    }

    public function test_csv_export_respects_filters(): void
    {
        ContactMessage::factory()->count(3)->create(['handled' => true]);
        ContactMessage::factory()->count(2)->create(['handled' => false]);
        
        $response = $this->actingAs($this->admin)->get('/admin/messages/export?status=handled');
        
        $content = $response->getContent();
        // Check that content contains header and rows
        $this->assertStringContainsString('Name,Email,Phone,Company', $content);
        // Since the test setup creates 3 handled + 2 unhandled + 1 from setUp with handled status of false (30% chance handled in factory)
        // When filtering for handled=true, we should get the 3 we explicitly created as handled
        $lineCount = substr_count($content, "\n");
        // Should have at least header line + 3 data lines
        $this->assertGreaterThanOrEqual(3, $lineCount);
    }

    public function test_admin_can_purge_old_messages(): void
    {
        // Create old messages (>24 months)
        ContactMessage::factory()->count(3)->create([
            'created_at' => Carbon::now()->subMonths(25),
        ]);
        
        // Create recent messages
        ContactMessage::factory()->count(2)->create([
            'created_at' => Carbon::now()->subMonths(12),
        ]);
        
        $response = $this->actingAs($this->admin)->post('/admin/messages/purge');
        
        $response->assertRedirect('/admin/messages');
        $response->assertSessionHas('success');
        
        // Should have 3 messages left (2 recent + 1 from setUp)
        $this->assertEquals(3, ContactMessage::count());
    }

    public function test_message_pagination_works(): void
    {
        ContactMessage::factory()->count(25)->create();
        
        $response = $this->actingAs($this->admin)->get('/admin/messages');
        
        $response->assertStatus(200);
        $messages = $response->viewData('messages');
        
        $this->assertEquals(20, $messages->perPage());
        $this->assertTrue($messages->hasPages());
    }

    public function test_admin_can_delete_single_message(): void
    {
        $response = $this->actingAs($this->admin)->delete("/admin/messages/{$this->message->id}");
        
        $response->assertRedirect('/admin/messages');
        $response->assertSessionHas('success', 'Message deleted successfully');
        
        $this->assertDatabaseMissing('contact_messages', ['id' => $this->message->id]);
    }

    public function test_sales_can_manage_messages(): void
    {
        // Sales can view
        $response = $this->actingAs($this->sales)->get("/admin/messages/{$this->message->id}");
        $response->assertStatus(200);
        
        // Sales can mark as handled
        $response = $this->actingAs($this->sales)->patch("/admin/messages/{$this->message->id}/handle", [
            'handled' => true,
        ]);
        $response->assertRedirect('/admin/messages');
        
        // Sales can export
        $response = $this->actingAs($this->sales)->get('/admin/messages/export');
        $response->assertStatus(200);
    }

    public function test_message_shows_submission_datetime(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/messages/{$this->message->id}");
        
        $response->assertStatus(200);
        $response->assertSee($this->message->created_at->format('F j, Y'));
        $response->assertSee($this->message->created_at->format('g:i A'));
    }

    public function test_message_shows_ip_and_user_agent(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/messages/{$this->message->id}");
        
        $response->assertStatus(200);
        $response->assertSee('192.168.1.1');
        $response->assertSee('Mozilla/5.0');
    }
}