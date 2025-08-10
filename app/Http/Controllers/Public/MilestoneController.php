<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Setting;

class MilestoneController extends Controller
{
    /**
     * Display all milestones
     */
    public function index()
    {
        $milestones = Milestone::orderBy('year', 'desc')
            ->orderBy('order')
            ->get();
        
        // Group by decade
        $groupedMilestones = $milestones->groupBy(function ($milestone) {
            return floor($milestone->year / 10) * 10 . 's';
        });
        
        $seo = [
            'title' => 'Our Journey - ' . Setting::getValue('company_name', 'Company'),
            'description' => 'Explore our company milestones and achievements over the years',
        ];
        
        return view('milestones.index', compact('milestones', 'groupedMilestones', 'seo'));
    }
}