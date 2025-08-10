<?php

namespace Tests\Feature\Public;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
        Setting::setValue('visi', '<h3>Visi Kami</h3><p>Menjadi perusahaan terkemuka dalam industri.</p>');
        Setting::setValue('misi', '<h3>Misi Kami</h3><ul><li>Memberikan pelayanan terbaik</li><li>Inovasi berkelanjutan</li></ul>');
        Setting::setValue('meta_description', 'Visi dan Misi PT Swastika Investama Prima');
    }

    public function test_visi_misi_page_renders_successfully(): void
    {
        $response = $this->get('/visi-misi');
        
        $response->assertStatus(200);
        $response->assertViewIs('pages.visi-misi');
    }

    public function test_visi_misi_page_shows_visi_content(): void
    {
        $response = $this->get('/visi-misi');
        
        $response->assertSee('Visi Kami');
        $response->assertSee('Menjadi perusahaan terkemuka dalam industri.');
    }

    public function test_visi_misi_page_shows_misi_content(): void
    {
        $response = $this->get('/visi-misi');
        
        $response->assertSee('Misi Kami');
        $response->assertSee('Memberikan pelayanan terbaik');
        $response->assertSee('Inovasi berkelanjutan');
    }

    public function test_visi_misi_page_has_seo_tags(): void
    {
        $response = $this->get('/visi-misi');
        
        $response->assertSee('<meta name="description" content="Visi dan Misi PT Swastika Investama Prima">', false);
        $response->assertSee('<title>', false);
    }

    public function test_visi_misi_page_renders_html_content(): void
    {
        $response = $this->get('/visi-misi');
        
        // Should render HTML tags properly
        $response->assertSee('<h3>Visi Kami</h3>', false);
        $response->assertSee('<ul>', false);
        $response->assertSee('<li>', false);
    }
}