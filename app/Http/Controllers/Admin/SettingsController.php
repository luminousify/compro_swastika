<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Constructor to ensure only admin can access
     */
    public function __construct()
    {
        // Authorization is handled by the route middleware
    }

    /**
     * Show the settings form
     */
    public function edit()
    {
        $settings = Setting::first();
        
        if (!$settings) {
            $settings = Setting::create(['data' => []]);
        }
        
        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            // Company Information
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'established_date' => 'nullable|date',
            'director' => 'nullable|string|max:255',
            'npwp' => ['nullable', 'string', 'regex:/^\d{2}\.\d{3}\.\d{3}\.\d{1}-\d{3}\.\d{3}$/'],
            'nib' => 'nullable|string|max:50',
            
            // Files
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'favicon' => 'nullable|mimes:ico,png|max:512',
            
            // Vision & Mission
            'visi' => 'nullable|string|max:1000',
            'misi' => 'nullable|string|max:1000',
            
            // Home Hero
            'home_hero_headline' => 'nullable|string|max:255',
            'home_hero_subheadline' => 'nullable|string|max:255',
            'home_hero_cta_text' => 'nullable|string|max:50',
            
            // About
            'about_snippet' => 'nullable|string|max:1000',
            
            // Social Media
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            
            // Google Maps
            'google_maps_embed_url' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // SEO
            'seo_default_title' => 'nullable|string|max:60',
            'seo_default_description' => 'nullable|string|max:160',
            'seo_default_keywords' => 'nullable|string|max:255',
        ]);
        
        $settings = Setting::first();
        if (!$settings) {
            $settings = Setting::create(['data' => []]);
        }
        
        $data = $settings->data;
        
        // Update company information
        if ($request->filled('company_name')) $data['company_name'] = $request->company_name;
        if ($request->filled('company_address')) $data['company_address'] = $request->company_address;
        if ($request->filled('company_phone')) $data['company_phone'] = $request->company_phone;
        if ($request->filled('company_email')) $data['company_email'] = $request->company_email;
        if ($request->filled('company_website')) $data['company_website'] = $request->company_website;
        if ($request->filled('established_date')) $data['established_date'] = $request->established_date;
        if ($request->filled('director')) $data['director'] = $request->director;
        if ($request->filled('npwp')) $data['npwp'] = $request->npwp;
        if ($request->filled('nib')) $data['nib'] = $request->nib;
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if (!empty($data['logo'])) {
                Storage::disk('public')->delete($data['logo']);
            }
            
            $logo = $request->file('logo');
            $filename = 'logo-' . Str::random(10) . '.' . $logo->getClientOriginalExtension();
            $path = $logo->storeAs('settings', $filename, 'public');
            $data['logo'] = $path;
        }
        
        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if (!empty($data['favicon'])) {
                Storage::disk('public')->delete($data['favicon']);
            }
            
            $favicon = $request->file('favicon');
            $filename = 'favicon-' . Str::random(10) . '.' . $favicon->getClientOriginalExtension();
            $path = $favicon->storeAs('settings', $filename, 'public');
            $data['favicon'] = $path;
        }
        
        // Update vision & mission
        if ($request->filled('visi')) $data['visi'] = $request->visi;
        if ($request->filled('misi')) $data['misi'] = $request->misi;
        
        // Update home hero
        if (!isset($data['home_hero'])) $data['home_hero'] = [];
        if ($request->filled('home_hero_headline')) $data['home_hero']['headline'] = $request->home_hero_headline;
        if ($request->filled('home_hero_subheadline')) $data['home_hero']['subheadline'] = $request->home_hero_subheadline;
        if ($request->filled('home_hero_cta_text')) $data['home_hero']['cta_text'] = $request->home_hero_cta_text;
        
        // Update about snippet
        if ($request->filled('about_snippet')) $data['about_snippet'] = $request->about_snippet;
        
        // Update social media
        if (!isset($data['social_media'])) $data['social_media'] = [];
        if ($request->filled('facebook')) $data['social_media']['facebook'] = $request->facebook;
        if ($request->filled('instagram')) $data['social_media']['instagram'] = $request->instagram;
        if ($request->filled('linkedin')) $data['social_media']['linkedin'] = $request->linkedin;
        if ($request->filled('twitter')) $data['social_media']['twitter'] = $request->twitter;
        if ($request->filled('youtube')) $data['social_media']['youtube'] = $request->youtube;
        
        // Update Google Maps
        if (!isset($data['google_maps'])) $data['google_maps'] = [];
        if ($request->filled('google_maps_embed_url')) {
            // Extract URL from iframe if provided
            $embedUrl = $request->google_maps_embed_url;
            if (strpos($embedUrl, '<iframe') !== false) {
                preg_match('/src="([^"]+)"/', $embedUrl, $matches);
                $embedUrl = $matches[1] ?? $embedUrl;
            }
            $data['google_maps']['embed_url'] = $embedUrl;
        }
        if ($request->filled('latitude')) $data['google_maps']['latitude'] = $request->latitude;
        if ($request->filled('longitude')) $data['google_maps']['longitude'] = $request->longitude;
        
        // Update SEO
        if (!isset($data['seo'])) $data['seo'] = [];
        if ($request->filled('seo_default_title')) $data['seo']['default_title'] = $request->seo_default_title;
        if ($request->filled('seo_default_description')) $data['seo']['default_description'] = $request->seo_default_description;
        if ($request->filled('seo_default_keywords')) $data['seo']['default_keywords'] = $request->seo_default_keywords;
        
        // Save settings
        $settings->data = $data;
        $settings->save();
        
        // Clear cache
        Cache::forget('settings:all');
        Cache::forget('home:v1');
        
        return redirect()->route('admin.settings.edit')->with('success', 'Settings updated successfully');
    }
}