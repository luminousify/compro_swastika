<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(Request $request)
    {
        $query = Client::query();
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Order by order field
        $query->orderBy('order')->orderBy('name');
        
        // Paginate with 20 per page
        $clients = $query->paginate(20)->withQueryString();
        
        return view('admin.clients.index', compact('clients'));
    }
    
    /**
     * Show the form for creating a new client
     */
    public function create()
    {
        return view('admin.clients.create');
    }
    
    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url',
            'logo' => 'nullable|image|max:2048', // 2MB max
        ]);
        
        // Get the highest order value
        $maxOrder = Client::max('order') ?? 0;
        
        $client = Client::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'order' => $maxOrder + 1,
        ]);
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $filename = Str::random(32) . '.' . $logo->getClientOriginalExtension();
            $path = $logo->storeAs('logos', $filename, 'public');
            
            $client->logo_path = $path;
            $client->save();
        }
        
        // Clear cache
        Cache::forget('home:v1');
        
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully');
    }
    
    /**
     * Show the form for editing a client
     */
    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }
    
    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url',
            'logo' => 'nullable|image|max:2048', // 2MB max
        ]);
        
        $client->name = $validated['name'];
        $client->url = $validated['url'];
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($client->logo_path) {
                Storage::disk('public')->delete($client->logo_path);
            }
            
            $logo = $request->file('logo');
            $filename = Str::random(32) . '.' . $logo->getClientOriginalExtension();
            $path = $logo->storeAs('logos', $filename, 'public');
            
            $client->logo_path = $path;
        }
        
        $client->save();
        
        // Clear cache
        Cache::forget('home:v1');
        
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully');
    }
    
    /**
     * Remove the specified client
     */
    public function destroy(Client $client)
    {
        // Delete logo if exists
        if ($client->logo_path) {
            Storage::disk('public')->delete($client->logo_path);
        }
        
        $client->delete();
        
        // Clear cache
        Cache::forget('home:v1');
        
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully');
    }
    
    /**
     * Update client ordering
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'clients' => 'required|array',
            'clients.*.id' => 'required|exists:clients,id',
            'clients.*.order' => 'required|integer|min:0',
        ]);
        
        DB::transaction(function () use ($validated) {
            foreach ($validated['clients'] as $item) {
                Client::where('id', $item['id'])
                    ->update(['order' => $item['order']]);
            }
        });
        
        // Clear cache
        Cache::forget('home:v1');
        
        return response()->json(['success' => true]);
    }
}