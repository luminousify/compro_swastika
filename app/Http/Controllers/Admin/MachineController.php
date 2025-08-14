<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Machine;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MachineController extends Controller
{
    private MediaService $mediaService;
    
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    
    /**
     * Display a listing of machines for a specific division
     */
    public function index(Division $division)
    {
        $machines = Machine::where('division_id', $division->id)
            ->orderBy('order')
            ->get();
        
        return view('admin.machines.index', compact('division', 'machines'));
    }
    
    /**
     * Show the form for creating a new machine
     */
    public function create(Division $division)
    {
        return view('admin.machines.create', compact('division'));
    }
    
    /**
     * Store a newly created machine
     */
    public function store(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        
        // Get the highest order value for this division
        $maxOrder = Machine::where('division_id', $division->id)->max('order') ?? 0;
        
        // Create machine
        $machine = Machine::create([
            'division_id' => $division->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'order' => $maxOrder + 1,
        ]);
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Machine created successfully');
    }
    
    /**
     * Show the form for editing the specified machine
     */
    public function edit(Division $division, Machine $machine)
    {
        // Ensure machine belongs to division
        if ($machine->division_id !== $division->id) {
            abort(404);
        }
        
        return view('admin.machines.edit', compact('division', 'machine'));
    }
    
    /**
     * Update the specified machine
     */
    public function update(Request $request, Division $division, Machine $machine)
    {
        // Ensure machine belongs to division
        if ($machine->division_id !== $division->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        
        // Update machine
        $machine->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Machine updated successfully');
    }
    
    /**
     * Remove the specified machine
     */
    public function destroy(Division $division, Machine $machine)
    {
        // Ensure machine belongs to division
        if ($machine->division_id !== $division->id) {
            abort(404);
        }
        
        $machine->delete();
        
        // Clear cache
        Cache::forget('divisions:index');
        
        return redirect()->route('admin.divisions.show', $division)
            ->with('success', 'Machine deleted successfully');
    }
    
    /**
     * Reorder machines within a division
     */
    public function reorder(Request $request, Division $division)
    {
        $request->validate([
            'machines' => 'required|array',
            'machines.*.id' => 'required|exists:machines,id',
            'machines.*.order' => 'required|integer|min:1',
        ]);
        
        foreach ($request->machines as $machineData) {
            $machine = Machine::find($machineData['id']);
            if ($machine && $machine->division_id === $division->id) {
                $machine->update(['order' => $machineData['order']]);
            }
        }
        
        return response()->json(['success' => true]);
    }
}