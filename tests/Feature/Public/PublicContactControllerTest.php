<?php

namespace Tests\Feature\Public;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('company_name', 'PT Swastika Investama Prima');
        Setting::setValue('company_address', 'Jl. Sudirman No. 123, Jakarta');
        Setting::setValue('company_phone', '+62-21-1234567');
        Setting::setValue('company_email', 'info@swastika.co.id');
        Setting::setValue('company_map', 'https://maps.google.com/embed?pb=!1m18!1m12!1m3!1d3966.3!2d106.8!3d-6.2!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1');
    }

    public function test_contact_page_renders_successfully(): void
    {
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
    }

    public function test_contact_page_shows_company_info(): void
    {
        $response = $this->get('/contact');
        
        $response->assertSee('PT Swastika Investama Prima');
        $response->assertSee('Jl. Sudirman No. 123, Jakarta');
        $response->assertSee('+62-21-1234567');
        $response->assertSee('info@swastika.co.id');
    }

    public function test_contact_page_shows_embedded_map(): void
    {
        $response = $this->get('/contact');
        
        $response->assertSee('https://maps.google.com/embed?pb=!1m18!1m12!1m3!1d3966.3!2d106.8!3d-6.2!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1');
    }

    public function test_contact_page_shows_contact_form(): void
    {
        $response = $this->get('/contact');
        
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="subject"', false);
        $response->assertSee('name="message"', false);
    }

    public function test_contact_page_has_proper_seo_meta(): void
    {
        $response = $this->get('/contact');
        
        $response->assertSee('<title>Contact Us - PT Swastika Investama Prima</title>', false);
        $response->assertSee('<meta name="description" content="Get in touch with us for any inquiries or business opportunities">', false);
    }

    public function test_contact_page_works_without_map_setting(): void
    {
        Setting::setValue('company_map', null);
        
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        $response->assertDontSee('iframe', false);
    }

    public function test_contact_page_works_with_minimal_settings(): void
    {
        Setting::setValue('company_address', null);
        Setting::setValue('company_phone', null);
        Setting::setValue('company_email', null);
        
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        $response->assertSee('PT Swastika Investama Prima'); // Company name should still show
    }
}