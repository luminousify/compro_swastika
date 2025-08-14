<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Product;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    private MediaService $mediaService;
    
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    
    /**
     * Display a listing of products for a specific division
     */
    public function index(Division $division)
    {
        $products = Product::where('division_id', $division->id)
            ->orderBy('order')
            ->get();
        
        return view('admin.products.index', compact('division', 'products'));
    }
    
    /**
     * Show the form for creating a new product
     */
    public function create(Division $division)
    {
        return view('admin.products.create', compact('division'));
    }
    
    /**
     * Store a newly created product
     */
    public function store(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        
        // Get the highest order value for this division
        $maxOrder = Product::where('division_id', $division->id)->max('order') ?? 0;
        
        // Create product
        $product = Product::create([
            'division_id' => $division->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'order' => $maxOrder + 1,
        ]);
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Product created successfully');
    }
    
    /**
     * Show the form for editing the specified product
     */
    public function edit(Division $division, Product $product)
    {
        // Ensure product belongs to division
        if ($product->division_id !== $division->id) {
            abort(404);
        }
        
        return view('admin.products.edit', compact('division', 'product'));
    }
    
    /**
     * Update the specified product
     */
    public function update(Request $request, Division $division, Product $product)
    {
        // Ensure product belongs to division
        if ($product->division_id !== $division->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        
        // Update product
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Product updated successfully');
    }
    
    /**
     * Remove the specified product
     */
    public function destroy(Division $division, Product $product)
    {
        // Ensure product belongs to division
        if ($product->division_id !== $division->id) {
            abort(404);
        }
        
        $product->delete();
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Product deleted successfully');
    }
    
    /**
     * Reorder products within a division
     */
    public function reorder(Request $request, Division $division)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.order' => 'required|integer|min:1',
        ]);
        
        foreach ($request->products as $productData) {
            $product = Product::find($productData['id']);
            if ($product && $product->division_id === $division->id) {
                $product->update(['order' => $productData['order']]);
            }
        }
        
        return response()->json(['success' => true]);
    }
}