<?php

namespace Tests\Feature;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilestoneManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sales;
    private Milestone $milestone;

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
        
        $this->milestone = Milestone::factory()->create([
            'year' => 2020,
            'text' => 'Test milestone',
            'order' => 1,
        ]);
    }

    public function test_admin_can_access_milestones_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/milestones');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.milestones.index');
        $response->assertViewHas('milestones');
    }

    public function test_sales_can_access_milestones_index(): void
    {
        $response = $this->actingAs($this->sales)->get('/admin/milestones');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.milestones.index');
    }

    public function test_milestones_are_displayed_in_chronological_order(): void
    {
        Milestone::query()->delete(); // Clear existing
        
        Milestone::factory()->create(['year' => 2022, 'order' => 3]);
        Milestone::factory()->create(['year' => 2020, 'order' => 1]);
        Milestone::factory()->create(['year' => 2021, 'order' => 2]);
        
        $response = $this->actingAs($this->admin)->get('/admin/milestones');
        
        $response->assertStatus(200);
        $milestones = $response->viewData('milestones');
        
        // Should be ordered by year descending
        $this->assertEquals(2022, $milestones[0]->year);
        $this->assertEquals(2021, $milestones[1]->year);
        $this->assertEquals(2020, $milestones[2]->year);
    }

    public function test_admin_can_create_milestone(): void
    {
        $milestoneData = [
            'year' => 2023,
            'text' => '<p>Company achieved major milestone</p>',
        ];
        
        $response = $this->actingAs($this->admin)->post('/admin/milestones', $milestoneData);
        
        $response->assertRedirect('/admin/milestones');
        $response->assertSessionHas('success', 'Milestone created successfully');
        
        $this->assertDatabaseHas('milestones', [
            'year' => 2023,
            'text' => '<p>Company achieved major milestone</p>',
        ]);
    }

    public function test_milestone_year_validation_enforced(): void
    {
        // Test year too early
        $response = $this->actingAs($this->admin)->post('/admin/milestones', [
            'year' => 1899,
            'text' => 'Too early',
        ]);
        
        $response->assertSessionHasErrors(['year']);
        
        // Test year too late
        $response = $this->actingAs($this->admin)->post('/admin/milestones', [
            'year' => 2101,
            'text' => 'Too late',
        ]);
        
        $response->assertSessionHasErrors(['year']);
        
        // Test valid year
        $response = $this->actingAs($this->admin)->post('/admin/milestones', [
            'year' => 2000,
            'text' => 'Valid year',
        ]);
        
        $response->assertRedirect('/admin/milestones');
    }

    public function test_milestone_year_must_be_unique(): void
    {
        Milestone::factory()->create(['year' => 2019]);
        
        $response = $this->actingAs($this->admin)->post('/admin/milestones', [
            'year' => 2019,
            'text' => 'Duplicate year',
        ]);
        
        $response->assertSessionHasErrors(['year']);
    }

    public function test_admin_can_edit_milestone(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/milestones/{$this->milestone->id}/edit");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.milestones.edit');
        $response->assertViewHas('milestone', $this->milestone);
    }

    public function test_admin_can_update_milestone(): void
    {
        $updateData = [
            'year' => 2021,
            'text' => '<p>Updated description with <strong>rich text</strong></p>',
        ];
        
        $response = $this->actingAs($this->admin)->put("/admin/milestones/{$this->milestone->id}", $updateData);
        
        $response->assertRedirect('/admin/milestones');
        $response->assertSessionHas('success', 'Milestone updated successfully');
        
        $this->milestone->refresh();
        $this->assertEquals(2021, $this->milestone->year);
        $this->assertEquals('<p>Updated description with <strong>rich text</strong></p>', $this->milestone->text);
    }

    public function test_admin_can_delete_milestone(): void
    {
        $response = $this->actingAs($this->admin)->delete("/admin/milestones/{$this->milestone->id}");
        
        $response->assertRedirect('/admin/milestones');
        $response->assertSessionHas('success', 'Milestone deleted successfully');
        
        $this->assertDatabaseMissing('milestones', ['id' => $this->milestone->id]);
    }

    public function test_milestone_ordering_can_be_updated(): void
    {
        $milestone1 = Milestone::factory()->create(['year' => 2018, 'order' => 1]);
        $milestone2 = Milestone::factory()->create(['year' => 2019, 'order' => 2]);
        $milestone3 = Milestone::factory()->create(['year' => 2020, 'order' => 3]);
        
        $response = $this->actingAs($this->admin)->post('/admin/milestones/reorder', [
            'milestones' => [
                ['id' => $milestone3->id, 'order' => 1],
                ['id' => $milestone1->id, 'order' => 2],
                ['id' => $milestone2->id, 'order' => 3],
            ]
        ]);
        
        $response->assertJson(['success' => true]);
        
        $milestone1->refresh();
        $milestone2->refresh();
        $milestone3->refresh();
        
        $this->assertEquals(2, $milestone1->order);
        $this->assertEquals(3, $milestone2->order);
        $this->assertEquals(1, $milestone3->order);
    }

    public function test_milestone_search_functionality(): void
    {
        Milestone::factory()->create([
            'year' => 2015,
            'text' => 'Launched new product line',
        ]);
        
        Milestone::factory()->create([
            'year' => 2016,
            'text' => 'Expanded to international markets',
        ]);
        
        $response = $this->actingAs($this->admin)->get('/admin/milestones?search=product');
        
        $response->assertStatus(200);
        $response->assertSee('2015');
        $response->assertSee('Launched new product line');
        $response->assertDontSee('2016');
    }

    public function test_sales_can_manage_milestones(): void
    {
        // Sales can create
        $response = $this->actingAs($this->sales)->post('/admin/milestones', [
            'year' => 2024,
            'text' => 'Sales created milestone',
        ]);
        
        $response->assertRedirect('/admin/milestones');
        $this->assertDatabaseHas('milestones', ['year' => 2024]);
        
        // Sales can edit
        $milestone = Milestone::where('year', 2024)->first();
        $response = $this->actingAs($this->sales)->put("/admin/milestones/{$milestone->id}", [
            'year' => 2024,
            'text' => 'Sales updated milestone',
        ]);
        
        $response->assertRedirect('/admin/milestones');
        
        // Sales can delete
        $response = $this->actingAs($this->sales)->delete("/admin/milestones/{$milestone->id}");
        
        $response->assertRedirect('/admin/milestones');
    }

    public function test_milestone_description_supports_rich_text(): void
    {
        $richTextContent = '<h2>Major Achievement</h2>
<p>In this year, we accomplished:</p>
<ul>
<li>Expanded to 5 new countries</li>
<li>Launched 3 new products</li>
<li>Increased revenue by 50%</li>
</ul>
<p><strong>This was a pivotal year for our company.</strong></p>';
        
        $response = $this->actingAs($this->admin)->post('/admin/milestones', [
            'year' => 2025,
            'text' => $richTextContent,
        ]);
        
        $response->assertRedirect('/admin/milestones');
        
        $milestone = Milestone::where('year', 2025)->first();
        $this->assertEquals($richTextContent, $milestone->text);
    }

    public function test_milestone_pagination_works(): void
    {
        // Create 25 milestones
        for ($year = 1995; $year <= 2019; $year++) {
            Milestone::factory()->create(['year' => $year]);
        }
        
        $response = $this->actingAs($this->admin)->get('/admin/milestones');
        
        $response->assertStatus(200);
        $milestones = $response->viewData('milestones');
        
        $this->assertEquals(15, $milestones->perPage());
        $this->assertTrue($milestones->hasPages());
    }
}