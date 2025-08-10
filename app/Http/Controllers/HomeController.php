<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Media;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        // Get settings or create default if not exists
        $settings = Setting::first();
        
        if (!$settings) {
            // Create default settings if none exist
            $settings = Setting::create([
                'data' => [
                    'company_name' => 'PT. Daya Swastika Perkasa',
                    'home_hero' => [
                        'headline' => 'PT. Daya Swastika Perkasa',
                        'subheadline' => 'Solusi Terpadu untuk Industri dan Otomotif',
                    ],
                ],
            ]);
        }

        // Get slider images
        $sliderImages = Media::where('is_home_slider', true)
            ->orderBy('order')
            ->get();

        // Get clients for homepage (max 12)
        $clients = Client::forHomepage()->get();

        return view('home', compact('settings', 'sliderImages', 'clients'));
    }
}
