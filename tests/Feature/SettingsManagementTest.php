<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sales;

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
        
        // Create initial settings
        Setting::create([
            'data' => [
                'company_name' => 'PT. Test Company',
                'company_address' => 'Test Address',
                'company_phone' => '021-12345678',
                'company_email' => 'test@example.com',
            ],
        ]);
    }

    public function test_admin_can_access_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.edit');
        $response->assertViewHas('settings');
    }

    public function test_sales_cannot_access_settings_page(): void
    {
        $response = $this->actingAs($this->sales)->get('/admin/settings');
        
        $response->assertStatus(403);
    }

    public function test_unauthenticated_users_cannot_access_settings(): void
    {
        $response = $this->get('/admin/settings');
        
        $response->assertRedirect('/login');
    }

    public function test_settings_form_displays_all_fields(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');
        
        $response->assertStatus(200);
        
        // Company Information fields
        $response->assertSee('Company Name');
        $response->assertSee('Company Address');
        $response->assertSee('Company Phone');
        $response->assertSee('Company Email');
        $response->assertSee('Company Website');
        $response->assertSee('Established Date');
        $response->assertSee('Director');
        $response->assertSee('NPWP');
        $response->assertSee('NIB');
        
        // Vision & Mission
        $response->assertSee('Vision');
        $response->assertSee('Mission');
        
        // Home Hero
        $response->assertSee('Hero Headline');
        $response->assertSee('Hero Subheadline');
        $response->assertSee('CTA Text');
        
        // About Snippet
        $response->assertSee('About Snippet');
        
        // Social Media
        $response->assertSee('Facebook');
        $response->assertSee('Instagram');
        $response->assertSee('LinkedIn');
        $response->assertSee('Twitter');
        $response->assertSee('YouTube');
        
        // Google Maps
        $response->assertSee('Google Maps Embed URL');
        $response->assertSee('Latitude');
        $response->assertSee('Longitude');
        
        // SEO
        $response->assertSee('Default Title');
        $response->assertSee('Default Description');
        $response->assertSee('Default Keywords');
    }

    public function test_admin_can_update_company_information(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'company_name' => 'PT. Updated Company',
            'company_address' => 'Updated Address',
            'company_phone' => '021-87654321',
            'company_email' => 'updated@example.com',
            'company_website' => 'https://updated.com',
            'established_date' => '2020-01-01',
            'director' => 'John Doe',
            'npwp' => '12.345.678.9-123.456',
            'nib' => '1234567890123',
        ]);
        
        $response->assertRedirect('/admin/settings');
        $response->assertSessionHas('success', 'Settings updated successfully');
        
        $settings = Setting::first();
        $this->assertEquals('PT. Updated Company', $settings->getCompanyName());
        $this->assertEquals('Updated Address', $settings->getCompanyAddress());
        $this->assertEquals('021-87654321', $settings->getCompanyPhone());
        $this->assertEquals('updated@example.com', $settings->getCompanyEmail());
    }

    public function test_admin_can_update_vision_and_mission(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'visi' => 'Updated Vision',
            'misi' => 'Updated Mission',
        ]);
        
        $response->assertRedirect('/admin/settings');
        
        $settings = Setting::first();
        $this->assertEquals('Updated Vision', $settings->data['visi']);
        $this->assertEquals('Updated Mission', $settings->data['misi']);
    }

    public function test_admin_can_update_home_hero_content(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'home_hero_headline' => 'New Headline',
            'home_hero_subheadline' => 'New Subheadline',
            'home_hero_cta_text' => 'Contact Us Now',
        ]);
        
        $response->assertRedirect('/admin/settings');
        
        $settings = Setting::first();
        $this->assertEquals('New Headline', $settings->getHomeHero()['headline']);
        $this->assertEquals('New Subheadline', $settings->getHomeHero()['subheadline']);
        $this->assertEquals('Contact Us Now', $settings->getHomeHero()['cta_text']);
    }

    public function test_admin_can_update_social_media_links(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'facebook' => 'https://facebook.com/company',
            'instagram' => 'https://instagram.com/company',
            'linkedin' => 'https://linkedin.com/company/company',
            'twitter' => 'https://twitter.com/company',
            'youtube' => 'https://youtube.com/c/company',
        ]);
        
        $response->assertRedirect('/admin/settings');
        
        $settings = Setting::first();
        $this->assertEquals('https://facebook.com/company', $settings->data['social_media']['facebook']);
        $this->assertEquals('https://instagram.com/company', $settings->data['social_media']['instagram']);
    }

    public function test_admin_can_upload_logo(): void
    {
        $logo = UploadedFile::fake()->image('logo.png', 300, 100);
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'logo' => $logo,
        ]);
        
        $response->assertRedirect('/admin/settings');
        
        $settings = Setting::first();
        $this->assertNotNull($settings->data['logo']);
        Storage::disk('public')->assertExists($settings->data['logo']);
    }

    public function test_admin_can_upload_favicon(): void
    {
        $favicon = UploadedFile::fake()->image('favicon.ico', 32, 32);
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'favicon' => $favicon,
        ]);
        
        $response->assertRedirect('/admin/settings');
        
        $settings = Setting::first();
        $this->assertNotNull($settings->data['favicon']);
        Storage::disk('public')->assertExists($settings->data['favicon']);
    }

    public function test_logo_upload_validates_file_type(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'logo' => $invalidFile,
        ]);
        
        $response->assertSessionHasErrors(['logo']);
    }

    public function test_logo_upload_validates_file_size(): void
    {
        $largeLogo = UploadedFile::fake()->image('logo.png', 300, 100)->size(3000); // 3MB
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'logo' => $largeLogo,
        ]);
        
        $response->assertSessionHasErrors(['logo']);
    }

    public function test_email_validation_works(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'company_email' => 'invalid-email',
        ]);
        
        $response->assertSessionHasErrors(['company_email']);
    }

    public function test_url_validation_for_social_media(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'facebook' => 'not-a-url',
        ]);
        
        $response->assertSessionHasErrors(['facebook']);
    }

    public function test_settings_update_clears_cache(): void
    {
        // Set cache value
        Cache::put('settings:all', 'cached_value', 3600);
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'company_name' => 'New Name',
        ]);
        
        $response->assertRedirect('/admin/settings');
        
        // Check cache was cleared
        $this->assertNull(Cache::get('settings:all'));
    }

    public function test_partial_update_preserves_other_settings(): void
    {
        $originalSettings = Setting::first();
        $originalName = $originalSettings->getCompanyName();
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'company_email' => 'newemail@example.com',
        ]);
        
        $response->assertRedirect('/admin/settings');
        
        $settings = Setting::first();
        $this->assertEquals('newemail@example.com', $settings->getCompanyEmail());
        $this->assertEquals($originalName, $settings->getCompanyName()); // Should remain unchanged
    }

    public function test_google_maps_embed_url_is_sanitized(): void
    {
        $embedUrl = '<iframe src="https://maps.google.com/embed"></iframe>';
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'google_maps_embed_url' => $embedUrl,
        ]);
        
        $response->assertRedirect('/admin/settings');
        
        $settings = Setting::first();
        // Should extract just the URL from iframe
        $this->assertStringContainsString('https://maps.google.com/embed', $settings->data['google_maps']['embed_url']);
    }

    public function test_settings_form_shows_current_values(): void
    {
        $settings = Setting::first();
        $settings->data = array_merge($settings->data, [
            'company_name' => 'Test Company ABC',
            'company_email' => 'test@abc.com',
        ]);
        $settings->save();
        
        $response = $this->actingAs($this->admin)->get('/admin/settings');
        
        $response->assertStatus(200);
        $response->assertSee('Test Company ABC');
        $response->assertSee('test@abc.com');
    }

    public function test_seo_fields_have_character_limits(): void
    {
        $longTitle = str_repeat('a', 100); // Too long for SEO title
        $longDescription = str_repeat('a', 200); // Too long for SEO description
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'seo_default_title' => $longTitle,
            'seo_default_description' => $longDescription,
        ]);
        
        $response->assertSessionHasErrors(['seo_default_title', 'seo_default_description']);
    }

    public function test_npwp_format_validation(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'npwp' => 'invalid-npwp-format',
        ]);
        
        $response->assertSessionHasErrors(['npwp']);
        
        // Valid format should pass
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'npwp' => '12.345.678.9-123.456',
        ]);
        
        $response->assertSessionDoesntHaveErrors(['npwp']);
    }

    public function test_settings_update_with_all_fields(): void
    {
        $logo = UploadedFile::fake()->image('logo.png', 300, 100);
        $favicon = UploadedFile::fake()->image('favicon.ico', 32, 32);
        
        $response = $this->actingAs($this->admin)->put('/admin/settings', [
            'company_name' => 'PT. Complete Test',
            'company_address' => 'Complete Address',
            'company_phone' => '021-11111111',
            'company_email' => 'complete@test.com',
            'company_website' => 'https://complete.test',
            'established_date' => '2019-02-07',
            'director' => 'Director Name',
            'npwp' => '90.150.964.6-435.000',
            'nib' => '9120105590182',
            'logo' => $logo,
            'favicon' => $favicon,
            'visi' => 'Complete Vision',
            'misi' => 'Complete Mission',
            'home_hero_headline' => 'Hero Headline',
            'home_hero_subheadline' => 'Hero Sub',
            'home_hero_cta_text' => 'Click Here',
            'about_snippet' => 'About text',
            'facebook' => 'https://facebook.com/test',
            'instagram' => 'https://instagram.com/test',
            'linkedin' => 'https://linkedin.com/company/test',
            'twitter' => 'https://twitter.com/test',
            'youtube' => 'https://youtube.com/c/test',
            'google_maps_embed_url' => 'https://maps.google.com/embed?test',
            'latitude' => '-6.2088',
            'longitude' => '106.8456',
            'seo_default_title' => 'SEO Title',
            'seo_default_description' => 'SEO Description',
            'seo_default_keywords' => 'keyword1, keyword2',
        ]);
        
        $response->assertRedirect('/admin/settings');
        $response->assertSessionHas('success');
        
        $settings = Setting::first();
        $this->assertEquals('PT. Complete Test', $settings->getCompanyName());
        $this->assertEquals('Complete Vision', $settings->data['visi']);
        $this->assertNotNull($settings->data['logo']);
        $this->assertNotNull($settings->data['favicon']);
    }
}