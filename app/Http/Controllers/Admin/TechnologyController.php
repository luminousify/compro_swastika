<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Technology;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TechnologyController extends Controller
{
    private MediaService $mediaService;
    
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    
    /**
     * Display a listing of technologies for a specific division
     */
    public function index(Division $division)
    {
        $technologies = Technology::where('division_id', $division->id)
            ->orderBy('order')
            ->get();
        
        return view('admin.technologies.index', compact('division', 'technologies'));
    }
    
    /**
     * Show the form for creating a new technology
     */
    public function create(Division $division)
    {
        return view('admin.technologies.create', compact('division'));
    }
    
    /**
     * Store a newly created technology
     */
    public function store(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        
        // Get the highest order value for this division
        $maxOrder = Technology::where('division_id', $division->id)->max('order') ?? 0;
        
        // Create technology
        $technology = Technology::create([
            'division_id' => $division->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'order' => $maxOrder + 1,
        ]);
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Technology created successfully');
    }
    
    /**
     * Show the form for editing the specified technology
     */
    public function edit(Division $division, Technology $technology)
    {
        // Ensure technology belongs to division
        if ($technology->division_id !== $division->id) {
            abort(404);
        }
        
        return view('admin.technologies.edit', compact('division', 'technology'));
    }
    
    /**
     * Update the specified technology
     */
    public function update(Request $request, Division $division, Technology $technology)
    {
        // Ensure technology belongs to division
        if ($technology->division_id !== $division->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        
        // Update technology
        $technology->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Technology updated successfully');
    }
    
    /**
     * Remove the specified technology
     */
    public function destroy(Division $division, Technology $technology)
    {
        // Ensure technology belongs to division
        if ($technology->division_id !== $division->id) {
            abort(404);
        }
        
        $technology->delete();
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Technology deleted successfully');
    }
    
    /**
     * Reorder technologies within a division
     */
    public function reorder(Request $request, Division $division)
    {
        $request->validate([
            'technologies' => 'required|array',
            'technologies.*.id' => 'required|exists:technologies,id',
            'technologies.*.order' => 'required|integer|min:1',
        ]);
        
        foreach ($request->technologies as $technologyData) {
            $technology = Technology::find($technologyData['id']);
            if ($technology && $technology->division_id === $division->id) {
                $technology->update(['order' => $technologyData['order']]);
            }
        }
        
        return response()->json(['success' => true]);
    }
}