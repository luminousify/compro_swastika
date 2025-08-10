<?php

namespace Database\Seeders;

use App\Models\Milestone;
use Illuminate\Database\Seeder;

class MilestoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $milestones = [
            [
                'year' => 2019,
                'text' => 'PT. Daya Swastika Perkasa didirikan pada 7 Februari 2019 sebagai agen pompa Grundfos dan dealer utama baterai NGS untuk wilayah Jawa Barat.',
                'order' => 1,
            ],
            [
                'year' => 2020,
                'text' => 'Ekspansi produk dengan menambahkan ban Bridgestone, GT Radial, dan berbagai merek baterai otomotif seperti Aspira, GS Yuasa, MS, dan Amaron.',
                'order' => 1,
            ],
        ];

        foreach ($milestones as $milestone) {
            Milestone::create($milestone);
        }
    }
}
