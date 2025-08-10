<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_has_fillable_attributes(): void
    {
        $fillable = ['data'];
        $setting = new Setting();
        
        $this->assertEquals($fillable, $setting->getFillable());
    }

    public function test_setting_casts_data_to_array(): void
    {
        $data = ['company_name' => 'Test Company', 'logo' => 'logo.png'];
        $setting = Setting::create(['data' => $data]);
        
        $this->assertIsArray($setting->data);
        $this->assertEquals($data, $setting->data);
    }

    public function test_get_company_name_returns_value_from_data(): void
    {
        $setting = new Setting();
        $setting->data = ['company_name' => 'Test Company'];
        
        $this->assertEquals('Test Company', $setting->getCompanyName());
    }

    public function test_get_company_name_returns_default_when_not_set(): void
    {
        $setting = new Setting();
        $setting->data = [];
        
        $this->assertEquals('PT. Daya Swastika Perkasa', $setting->getCompanyName());
    }

    public function test_get_logo_returns_value_from_data(): void
    {
        $setting = new Setting();
        $setting->data = ['logo' => 'logo.png'];
        
        $this->assertEquals('logo.png', $setting->getLogo());
    }

    public function test_get_logo_returns_null_when_not_set(): void
    {
        $setting = new Setting();
        $setting->data = [];
        
        $this->assertNull($setting->getLogo());
    }

    public function test_get_visi_returns_value_from_data(): void
    {
        $setting = new Setting();
        $setting->data = ['visi' => 'Our vision statement'];
        
        $this->assertEquals('Our vision statement', $setting->getVisi());
    }

    public function test_get_visi_returns_empty_string_when_not_set(): void
    {
        $setting = new Setting();
        $setting->data = [];
        
        $this->assertEquals('', $setting->getVisi());
    }

    public function test_get_misi_returns_value_from_data(): void
    {
        $setting = new Setting();
        $setting->data = ['misi' => 'Our mission statement'];
        
        $this->assertEquals('Our mission statement', $setting->getMisi());
    }

    public function test_get_misi_returns_empty_string_when_not_set(): void
    {
        $setting = new Setting();
        $setting->data = [];
        
        $this->assertEquals('', $setting->getMisi());
    }

    public function test_get_home_hero_returns_value_from_data(): void
    {
        $heroData = [
            'headline' => 'Custom Headline',
            'subheadline' => 'Custom Subheadline'
        ];
        $setting = new Setting();
        $setting->data = ['home_hero' => $heroData];
        
        $this->assertEquals($heroData, $setting->getHomeHero());
    }

    public function test_get_home_hero_returns_default_when_not_set(): void
    {
        $setting = new Setting();
        $setting->data = [];
        
        $expected = [
            'headline' => 'Welcome to DSP',
            'subheadline' => 'Your trusted business partner',
        ];
        
        $this->assertEquals($expected, $setting->getHomeHero());
    }

    public function test_setting_can_be_created_with_complex_data(): void
    {
        $complexData = [
            'company_name' => 'PT. Daya Swastika Perkasa',
            'logo' => 'logo.png',
            'visi' => 'To be the leading company',
            'misi' => 'To provide excellent service',
            'home_hero' => [
                'headline' => 'Welcome to DSP',
                'subheadline' => 'Your trusted partner'
            ],
            'contact_info' => [
                'address' => '123 Main St',
                'phone' => '+62123456789',
                'email' => 'info@dsp.com'
            ],
            'social_links' => [
                'facebook' => 'https://facebook.com/dsp',
                'instagram' => 'https://instagram.com/dsp'
            ]
        ];
        
        $setting = Setting::create(['data' => $complexData]);
        
        $this->assertEquals($complexData, $setting->data);
        $this->assertEquals('PT. Daya Swastika Perkasa', $setting->getCompanyName());
        $this->assertEquals('logo.png', $setting->getLogo());
        $this->assertEquals('To be the leading company', $setting->getVisi());
        $this->assertEquals('To provide excellent service', $setting->getMisi());
    }
}