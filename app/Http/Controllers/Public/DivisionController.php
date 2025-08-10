<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Setting;

class DivisionController extends Controller
{
    /**
     * Display all divisions
     */
    public function index()
    {
        $divisions = Division::with('media')
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $seo = [
            'title' => 'Our Divisions - ' . Setting::getValue('company_name', 'Company'),
            'description' => 'Explore our business divisions and capabilities',
        ];
        
        return view('divisions.index', compact('divisions', 'seo'));
    }
    
    /**
     * Display division details
     */
    public function show(Division $division)
    {
        $division->load([
            'products' => function ($query) {
                $query->orderBy('order')->orderBy('created_at', 'desc');
            },
            'technologies' => function ($query) {
                $query->orderBy('order')->orderBy('created_at', 'desc');
            },
            'machines' => function ($query) {
                $query->orderBy('order')->orderBy('created_at', 'desc');
            },
            'media' => function ($query) {
                $query->orderBy('order');
            },
        ]);
        
        $seo = [
            'title' => $division->name . ' - ' . Setting::getValue('company_name', 'Company'),
            'description' => $division->description ? substr($division->description, 0, 160) : '',
        ];
        
        return view('divisions.show', compact('division', 'seo'));
    }
}