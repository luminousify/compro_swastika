<?php

namespace Tests\Feature\Public;

use App\Models\Milestone;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMilestoneControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
    }

    public function test_milestones_page_renders_successfully(): void
    {
        $response = $this->get('/milestones');
        
        $response->assertStatus(200);
        $response->assertViewIs('milestones.index');
    }

    public function test_milestones_displayed_in_chronological_order(): void
    {
        Milestone::factory()->create(['year' => 2020, 'text' => 'Founded company']);
        Milestone::factory()->create(['year' => 2023, 'text' => 'Expanded globally']);
        Milestone::factory()->create(['year' => 2021, 'text' => 'Launched product']);
        
        $response = $this->get('/milestones');
        
        $milestones = $response->viewData('milestones');
        
        // Should be ordered by year descending
        $this->assertEquals(2023, $milestones[0]->year);
        $this->assertEquals(2021, $milestones[1]->year);
        $this->assertEquals(2020, $milestones[2]->year);
    }

    public function test_milestones_page_shows_year_and_description(): void
    {
        Milestone::factory()->create([
            'year' => 2022,
            'text' => '<p>Achieved <strong>ISO certification</strong></p>',
        ]);
        
        $response = $this->get('/milestones');
        
        $response->assertSee('2022');
        $response->assertSee('Achieved');
        $response->assertSee('ISO certification');
    }

    public function test_milestones_page_handles_empty_state(): void
    {
        // No milestones
        $response = $this->get('/milestones');
        
        $response->assertStatus(200);
        $response->assertSee('No milestones');
    }

    public function test_milestones_page_groups_by_decade(): void
    {
        Milestone::factory()->create(['year' => 2020]);
        Milestone::factory()->create(['year' => 2021]);
        Milestone::factory()->create(['year' => 2010]);
        Milestone::factory()->create(['year' => 2015]);
        
        $response = $this->get('/milestones');
        
        $response->assertSee('2020s');
        $response->assertSee('2010s');
    }

    public function test_milestones_page_has_seo_tags(): void
    {
        $response = $this->get('/milestones');
        
        $response->assertSee('<title>', false);
        $response->assertSee('Our Journey');
    }
}