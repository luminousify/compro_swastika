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
                'name' => 'JMM',
                'logo_path' => 'logos/BzzKE1YWv0uP1WxU6Ne7VxOwq2HxRPIg.png',
                'url' => null,
                'order' => 1,
            ],
            [
                'name' => 'PT. Paramount Bed Indonesia',
                'logo_path' => 'logos/paramount-bed.png',
                'url' => null,
                'order' => 2,
            ],
            [
                'name' => 'PT. Andara Prima Utama',
                'logo_path' => 'logos/andara-prima.png',
                'url' => null,
                'order' => 3,
            ],
            [
                'name' => 'PT. Tridaya Interior',
                'logo_path' => 'logos/tridaya-interior.png',
                'url' => null,
                'order' => 4,
            ],
            [
                'name' => 'PT. NRM Indonesia',
                'logo_path' => 'logos/nrm-indonesia.png',
                'url' => null,
                'order' => 5,
            ],
            [
                'name' => 'PT. Grundfos Indonesia',
                'logo_path' => 'logos/grundfos-indonesia.png',
                'url' => null,
                'order' => 6,
            ],
            [
                'name' => 'PT. NGS Battery',
                'logo_path' => 'logos/ngs-battery.png',
                'url' => null,
                'order' => 7,
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
