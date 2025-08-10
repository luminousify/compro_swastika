<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Machine;
use App\Models\Media;
use App\Models\Product;
use App\Models\Technology;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DivisionController extends Controller
{
    private MediaService $mediaService;
    
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    
    /**
     * Display a listing of divisions
     */
    public function index(Request $request)
    {
        $query = Division::query()
            ->withCount(['products', 'technologies', 'machines'])
            ->orderBy('order');
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $divisions = $query->get();
        
        return view('admin.divisions.index', compact('divisions'));
    }
    
    /**
     * Show the form for creating a new division
     */
    public function create()
    {
        return view('admin.divisions.create');
    }
    
    /**
     * Store a newly created division
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'hero_image' => 'nullable|image|max:10240|dimensions:max_width=4096,max_height=4096',
        ]);
        
        // Generate unique slug from name
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Division::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        // Get the highest order value
        $maxOrder = Division::max('order') ?? 0;
        
        // Create division
        $division = Division::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'],
            'order' => $maxOrder + 1,
        ]);
        
        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            $this->mediaService->uploadImage(
                $request->file('hero_image'),
                $division,
                'hero'
            );
        }
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division created successfully');
    }
    
    /**
     * Display the specified division with nested content
     */
    public function show(Division $division)
    {
        $products = Product::where('division_id', $division->id)
            ->orderBy('order')
            ->get();
            
        $technologies = Technology::where('division_id', $division->id)
            ->orderBy('order')
            ->get();
            
        $machines = Machine::where('division_id', $division->id)
            ->orderBy('order')
            ->get();
        
        return view('admin.divisions.show', compact('division', 'products', 'technologies', 'machines'));
    }
    
    /**
     * Show the form for editing the specified division
     */
    public function edit(Division $division)
    {
        $division->load('media');
        return view('admin.divisions.edit', compact('division'));
    }
    
    /**
     * Update the specified division
     */
    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:divisions,slug,' . $division->id,
            'description' => 'required|string',
            'hero_image' => 'nullable|image|max:10240|dimensions:max_width=4096,max_height=4096',
        ]);
        
        $division->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
        ]);
        
        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            // Delete old hero image if exists
            $oldMedia = Media::where('mediable_type', Division::class)
                ->where('mediable_id', $division->id)
                ->where('type', 'image')
                ->first();
                
            if ($oldMedia) {
                $this->mediaService->deleteMedia($oldMedia);
            }
            
            // Upload new hero image
            $this->mediaService->uploadImage(
                $request->file('hero_image'),
                $division,
                'hero'
            );
        }
        
        // Clear cache
        Cache::forget('divisions:index');
        Cache::forget('division:' . $division->slug);
        
        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division updated successfully');
    }
    
    /**
     * Show delete confirmation page
     */
    public function delete(Division $division)
    {
        $productCount = Product::where('division_id', $division->id)->count();
        $technologyCount = Technology::where('division_id', $division->id)->count();
        $machineCount = Machine::where('division_id', $division->id)->count();
        
        $hasContent = $productCount > 0 || $technologyCount > 0 || $machineCount > 0;
        
        return view('admin.divisions.delete', compact('division', 'productCount', 'technologyCount', 'machineCount', 'hasContent'));
    }
    
    /**
     * Remove the specified division
     */
    public function destroy(Request $request, Division $division)
    {
        // Check if cascade delete is confirmed for divisions with content
        $hasContent = Product::where('division_id', $division->id)->exists() ||
                     Technology::where('division_id', $division->id)->exists() ||
                     Machine::where('division_id', $division->id)->exists();
        
        if ($hasContent && !$request->has('confirm')) {
            return redirect()->route('admin.divisions.delete', $division);
        }
        
        DB::transaction(function () use ($division) {
            // Delete all related content
            Product::where('division_id', $division->id)->delete();
            Technology::where('division_id', $division->id)->delete();
            Machine::where('division_id', $division->id)->delete();
            
            // Delete all media
            $media = Media::where('mediable_type', Division::class)
                ->where('mediable_id', $division->id)
                ->get();
                
            foreach ($media as $item) {
                $this->mediaService->deleteMedia($item);
            }
            
            // Delete the division
            $division->delete();
        });
        
        // Clear cache
        Cache::forget('divisions:index');
        Cache::forget('division:' . $division->slug);
        
        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division deleted successfully');
    }
    
    /**
     * Update division ordering
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'divisions' => 'required|array',
            'divisions.*.id' => 'required|exists:divisions,id',
            'divisions.*.order' => 'required|integer|min:1',
        ]);
        
        DB::transaction(function () use ($validated) {
            foreach ($validated['divisions'] as $item) {
                Division::where('id', $item['id'])
                    ->update(['order' => $item['order']]);
            }
        });
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return response()->json(['success' => true]);
    }
}