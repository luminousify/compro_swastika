<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MilestoneController extends Controller
{
    /**
     * Display a listing of milestones
     */
    public function index(Request $request)
    {
        $query = Milestone::query();
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('text', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%");
        }
        
        // Order by year descending (most recent first)
        $query->orderBy('year', 'desc')->orderBy('order');
        
        // Paginate with 15 per page
        $milestones = $query->paginate(15)->withQueryString();
        
        return view('admin.milestones.index', compact('milestones'));
    }
    
    /**
     * Show the form for creating a new milestone
     */
    public function create()
    {
        return view('admin.milestones.create');
    }
    
    /**
     * Store a newly created milestone
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:2100|unique:milestones,year',
            'text' => 'required|string',
        ]);
        
        // Get the highest order value
        $maxOrder = Milestone::max('order') ?? 0;
        
        Milestone::create([
            'year' => $validated['year'],
            'text' => $validated['text'],
            'order' => $maxOrder + 1,
        ]);
        
        // Clear cache
        Cache::forget('home:v1');
        
        return redirect()->route('admin.milestones.index')
            ->with('success', 'Milestone created successfully');
    }
    
    /**
     * Show the form for editing a milestone
     */
    public function edit(Milestone $milestone)
    {
        return view('admin.milestones.edit', compact('milestone'));
    }
    
    /**
     * Update the specified milestone
     */
    public function update(Request $request, Milestone $milestone)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:2100|unique:milestones,year,' . $milestone->id,
            'text' => 'required|string',
        ]);
        
        $milestone->update([
            'year' => $validated['year'],
            'text' => $validated['text'],
        ]);
        
        // Clear cache
        Cache::forget('home:v1');
        
        return redirect()->route('admin.milestones.index')
            ->with('success', 'Milestone updated successfully');
    }
    
    /**
     * Remove the specified milestone
     */
    public function destroy(Milestone $milestone)
    {
        $milestone->delete();
        
        // Clear cache
        Cache::forget('home:v1');
        
        return redirect()->route('admin.milestones.index')
            ->with('success', 'Milestone deleted successfully');
    }
    
    /**
     * Update milestone ordering
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'milestones' => 'required|array',
            'milestones.*.id' => 'required|exists:milestones,id',
            'milestones.*.order' => 'required|integer|min:0',
        ]);
        
        DB::transaction(function () use ($validated) {
            foreach ($validated['milestones'] as $item) {
                Milestone::where('id', $item['id'])
                    ->update(['order' => $item['order']]);
            }
        });
        
        // Clear cache
        Cache::forget('home:v1');
        
        return response()->json(['success' => true]);
    }
}