<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class PageController extends Controller
{
    /**
     * Display the Visi & Misi page
     */
    public function visiMisi()
    {
        $visi = Setting::getValue('visi', '');
        $misi = Setting::getValue('misi', '');
        
        $seo = [
            'title' => 'Visi & Misi - ' . Setting::getValue('company_name', 'Company'),
            'description' => Setting::getValue('meta_description', 'Visi dan Misi perusahaan kami'),
        ];
        
        return view('pages.visi-misi', compact('visi', 'misi', 'seo'));
    }
}