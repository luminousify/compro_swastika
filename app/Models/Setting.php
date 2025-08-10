<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['data'];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get company name from settings
     */
    public function getCompanyName(): string
    {
        return $this->data['company_name'] ?? 'PT. Daya Swastika Perkasa';
    }

    /**
     * Get company logo path
     */
    public function getLogo(): ?string
    {
        return $this->data['logo'] ?? null;
    }

    /**
     * Get vision text
     */
    public function getVisi(): string
    {
        return $this->data['visi'] ?? '';
    }

    /**
     * Get mission text
     */
    public function getMisi(): string
    {
        return $this->data['misi'] ?? '';
    }

    /**
     * Get home hero content
     */
    public function getHomeHero(): array
    {
        return $this->data['home_hero'] ?? [
            'headline' => 'Welcome to DSP',
            'subheadline' => 'Your trusted business partner',
        ];
    }

    /**
     * Get company address
     */
    public function getCompanyAddress(): ?string
    {
        return $this->data['company_address'] ?? null;
    }

    /**
     * Get company phone
     */
    public function getCompanyPhone(): ?string
    {
        return $this->data['company_phone'] ?? null;
    }

    /**
     * Get company email
     */
    public function getCompanyEmail(): ?string
    {
        return $this->data['company_email'] ?? null;
    }
    
    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $settings = static::first();
        if (!$settings) {
            return $default;
        }
        
        return $settings->data[$key] ?? $default;
    }
    
    /**
     * Set a setting value by key
     */
    public static function setValue(string $key, $value): void
    {
        $settings = static::firstOrCreate([], ['data' => []]);
        $data = $settings->data;
        $data[$key] = $value;
        $settings->data = $data;
        $settings->save();
    }
}
