<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'data' => [
                'company_name' => 'PT. Daya Swastika Perkasa',
                'company_address' => 'Ruko Arana IX 2, Harapan Indah No. 25, Setia Asih, Tarumajaya, Kabupaten Bekasi, Jawa Barat 17612',
                'company_phone' => '021-89444891',
                'company_email' => 'dswastikap@gmail.com',
                'company_website' => 'https://dsp.co.id',
                'established_date' => '2019-02-07',
                'director' => 'Purwanta Adi Laksana, ST, MBA',
                'npwp' => '90.150.964.6-435.000',
                'nib' => '9120105590182',
                'logo' => null,
                'favicon' => null,
                'visi' => 'Menjadi perusahaan terdepan dalam menyediakan solusi terpadu untuk kebutuhan industri dan otomotif dengan standar kualitas internasional.',
                'misi' => 'Memberikan produk dan layanan berkualitas tinggi yang mendukung pertumbuhan bisnis klien melalui inovasi, keandalan, dan pelayanan prima.',
                'home_hero' => [
                    'headline' => 'Engineering Excellence, Delivered',
                    'subheadline' => 'Your Trusted Partner for Integrated Industrial and Automotive Solutions',
                    'cta_text' => 'Hubungi Kami',
                ],
                'about_snippet' => 'PT. Daya Swastika Perkasa didirikan pada 7 Februari 2019 sebagai perusahaan yang bergerak di berbagai bidang industri dan otomotif. Kami menyediakan solusi terpadu mulai dari pompa Grundfos, suku cadang otomotif, fabrikasi, hingga konsultasi bisnis.',
                'social_media' => [
                    'facebook' => '',
                    'instagram' => '',
                    'linkedin' => '',
                    'twitter' => '',
                    'youtube' => '',
                ],
                'google_maps' => [
                    'embed_url' => '',
                    'latitude' => -6.2088,
                    'longitude' => 106.8456,
                ],
                'seo' => [
                    'default_title' => 'PT. Daya Swastika Perkasa - Solusi Terpadu Industri dan Otomotif',
                    'default_description' => 'PT. Daya Swastika Perkasa menyediakan solusi terpadu untuk kebutuhan industri dan otomotif termasuk pompa Grundfos, suku cadang, fabrikasi, dan konsultasi bisnis.',
                    'default_keywords' => 'DSP, Daya Swastika Perkasa, Grundfos, otomotif, fabrikasi, konsultasi bisnis, industri',
                ],
            ],
        ]);
    }
}
