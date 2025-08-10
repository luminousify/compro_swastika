<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Division;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Display the home page
     */
    public function index()
    {
        // Cache everything for 1 hour to minimize database queries
        $data = Cache::remember('home:v2', 3600, function () {
            return [
                'hero' => [
                    'headline' => Setting::getValue('home_hero.headline', 'Engineering Excellence, Delivered'),
                    'subheadline' => Setting::getValue('home_hero.subheadline', 'Your Trusted Partner for Integrated Industrial and Automotive Solutions'),
                ],
                'sliders' => Media::where('mediable_type', 'home_slider')
                    ->whereJsonContains('flags->home_slider', true)
                    ->orderBy('order')
                    ->get(),
                'about_snippet' => Setting::getValue('about_snippet', ''),
                'clients' => Client::orderBy('order')
                    ->orderBy('created_at', 'desc')
                    ->limit(12)
                    ->get(),
                'divisions' => Division::orderBy('order')
                    ->orderBy('created_at', 'desc')
                    ->get(),
                'milestones' => Milestone::orderBy('year', 'desc')
                    ->limit(3)
                    ->get(),
                'seo' => [
                    'title' => Setting::getValue('company_name', 'Company') . ' - Home',
                    'description' => Setting::getValue('meta_description', ''),
                    'og_image' => Setting::getValue('og_image', ''),
                ],
            ];
        });
        
        return view('home', $data);
    }
}