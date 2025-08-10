<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'PT. Paramount Bed Indonesia',
                'logo_path' => null,
                'url' => null,
                'order' => 1,
            ],
            [
                'name' => 'PT. Andara Prima Utama',
                'logo_path' => null,
                'url' => null,
                'order' => 2,
            ],
            [
                'name' => 'PT. Tridaya Interior',
                'logo_path' => null,
                'url' => null,
                'order' => 3,
            ],
            [
                'name' => 'PT. NRM Indonesia',
                'logo_path' => null,
                'url' => null,
                'order' => 4,
            ],
            [
                'name' => 'PT. Grundfos Indonesia',
                'logo_path' => null,
                'url' => null,
                'order' => 5,
            ],
            [
                'name' => 'PT. NGS Battery',
                'logo_path' => null,
                'url' => null,
                'order' => 6,
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
