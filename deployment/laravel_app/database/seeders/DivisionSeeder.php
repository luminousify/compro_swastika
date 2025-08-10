<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Division::create([
            'slug' => 'grundfos-pump-solutions',
            'name' => 'Grundfos Pump Solutions',
            'description' => 'Kami menyediakan solusi pompa Grundfos lengkap mulai dari supply, instalasi, testing & commissioning, hingga layanan after-sales. Grundfos pump adalah pilihan terbaik untuk menjaga sirkulasi cairan pendingin dengan tekanan dan kontinuitas yang tepat untuk melindungi mesin Anda.',
            'hero_image_path' => null,
            'order' => 1,
        ]);
    }
}
