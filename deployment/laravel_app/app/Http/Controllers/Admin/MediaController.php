<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Machine;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Technology;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    private MediaService $mediaService;
    
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    
    /**
     * Display a listing of media
     */
    public function index(Request $request)
    {
        $query = Media::query()->with('mediable');
        
        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by entity type
        if ($request->has('entity_type') && $request->entity_type) {
            $query->where('mediable_type', $request->entity_type);
        }
        
        // Search by caption
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('caption', 'like', "%{$search}%");
        }
        
        // Order by created date
        $query->orderBy('created_at', 'desc');
        
        // Check view mode
        $viewMode = $request->get('view', 'list');
        
        // Paginate with 20 per page
        $media = $query->paginate(20)->withQueryString();
        
        return view('admin.media.index', compact('media', 'viewMode'));
    }
    
    /**
     * Show the form for uploading new media
     */
    public function create()
    {
        // Get all entities that can have media
        $entities = $this->getAvailableEntities();
        
        return view('admin.media.create', compact('entities'));
    }
    
    /**
     * Store newly uploaded media
     */
    public function store(Request $request)
    {
        // Validate based on upload type
        if ($request->hasFile('file')) {
            $validated = $request->validate([
                'file' => 'required|image|max:10240|dimensions:max_width=4096,max_height=4096',
                'entity_type' => 'required|string',
                'entity_id' => 'required|integer',
                'caption' => 'nullable|string|max:255',
                'type' => 'nullable|string|in:general,hero,card',
            ]);
            
            // Get the entity
            $entity = $this->getEntity($validated['entity_type'], $validated['entity_id']);
            
            if (!$entity) {
                return redirect()->back()->withErrors(['entity_id' => 'Invalid entity selected']);
            }
            
            // Upload the image
            $media = $this->mediaService->uploadImage(
                $request->file('file'),
                $entity,
                $validated['type'] ?? 'general'
            );
            
            // Update caption if provided
            if (!empty($validated['caption'])) {
                $media->caption = $validated['caption'];
                $media->save();
            }
            
        } elseif ($request->has('video_url')) {
            $validated = $request->validate([
                'video_url' => ['required', 'url', 'regex:/^https:\/\/(www\.)?(youtube\.com|vimeo\.com)/'],
                'entity_type' => 'required|string',
                'entity_id' => 'required|integer',
                'caption' => 'nullable|string|max:255',
            ]);
            
            // Get the entity
            $entity = $this->getEntity($validated['entity_type'], $validated['entity_id']);
            
            if (!$entity) {
                return redirect()->back()->withErrors(['entity_id' => 'Invalid entity selected']);
            }
            
            // Upload the video URL
            $media = $this->mediaService->uploadVideo(
                $validated['video_url'],
                $entity
            );
            
            // Update caption if provided
            if (!empty($validated['caption'])) {
                $media->caption = $validated['caption'];
                $media->save();
            }
            
        } elseif ($request->hasFile('video_file')) {
            $validated = $request->validate([
                'video_file' => 'required|file|mimetypes:video/mp4,video/mpeg,video/quicktime|max:102400',
                'entity_type' => 'required|string',
                'entity_id' => 'required|integer',
                'caption' => 'nullable|string|max:255',
            ]);
            
            // Get the entity
            $entity = $this->getEntity($validated['entity_type'], $validated['entity_id']);
            
            if (!$entity) {
                return redirect()->back()->withErrors(['entity_id' => 'Invalid entity selected']);
            }
            
            // Upload the video file
            $media = $this->mediaService->uploadVideo(
                $request->file('video_file'),
                $entity
            );
            
            // Update caption if provided
            if (!empty($validated['caption'])) {
                $media->caption = $validated['caption'];
                $media->save();
            }
        } else {
            return redirect()->back()->withErrors(['file' => 'Please select a file or provide a video URL']);
        }
        
        // Clear relevant caches
        $this->clearCaches($entity);
        
        return redirect()->route('admin.media.index')
            ->with('success', 'Media uploaded successfully');
    }
    
    /**
     * Handle bulk media upload
     */
    public function bulkUpload(Request $request)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'image|max:10240|dimensions:max_width=4096,max_height=4096',
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
        ]);
        
        // Get the entity
        $entity = $this->getEntity($validated['entity_type'], $validated['entity_id']);
        
        if (!$entity) {
            return redirect()->back()->withErrors(['entity_id' => 'Invalid entity selected']);
        }
        
        $uploadedCount = 0;
        
        foreach ($request->file('files') as $file) {
            try {
                $this->mediaService->uploadImage($file, $entity, 'general');
                $uploadedCount++;
            } catch (\Exception $e) {
                // Log error but continue with other files
                \Log::error('Bulk upload error: ' . $e->getMessage());
            }
        }
        
        // Clear relevant caches
        $this->clearCaches($entity);
        
        return redirect()->route('admin.media.index')
            ->with('success', "{$uploadedCount} files uploaded successfully");
    }
    
    /**
     * Show the form for editing media
     */
    public function edit(Media $media)
    {
        return view('admin.media.edit', compact('media'));
    }
    
    /**
     * Update media details
     */
    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'caption' => 'nullable|string|max:255',
            'flags' => 'nullable|array',
            'flags.*' => 'string|in:home_slider,featured',
            'order' => 'nullable|integer|min:0',
        ]);
        
        $media->caption = $validated['caption'] ?? null;
        $media->flags = $validated['flags'] ?? [];
        
        if (isset($validated['order'])) {
            $media->order = $validated['order'];
        }
        
        $media->save();
        
        // Clear relevant caches
        $this->clearCaches($media->mediable);
        
        return redirect()->route('admin.media.index')
            ->with('success', 'Media updated successfully');
    }
    
    /**
     * Delete media
     */
    public function destroy(Media $media)
    {
        $entity = $media->mediable;
        
        // Delete the media using service
        $this->mediaService->deleteMedia($media);
        
        // Clear relevant caches
        $this->clearCaches($entity);
        
        return redirect()->route('admin.media.index')
            ->with('success', 'Media deleted successfully');
    }
    
    /**
     * Update media ordering
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'media' => 'required|array',
            'media.*.id' => 'required|exists:media,id',
            'media.*.order' => 'required|integer|min:0',
        ]);
        
        DB::transaction(function () use ($validated) {
            foreach ($validated['media'] as $item) {
                Media::where('id', $item['id'])
                    ->update(['order' => $item['order']]);
            }
        });
        
        // Clear all caches as we don't know which entities were affected
        Cache::forget('home:v1');
        Cache::forget('divisions:index');
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Get entity by type and ID
     */
    private function getEntity(string $type, int $id)
    {
        return match ($type) {
            Division::class => Division::find($id),
            Product::class => Product::find($id),
            Technology::class => Technology::find($id),
            Machine::class => Machine::find($id),
            Milestone::class => Milestone::find($id),
            default => null,
        };
    }
    
    /**
     * Get available entities for media upload
     */
    private function getAvailableEntities()
    {
        return [
            'Divisions' => Division::orderBy('name')->get()->map(function ($item) {
                return [
                    'type' => Division::class,
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }),
            'Products' => Product::orderBy('name')->get()->map(function ($item) {
                return [
                    'type' => Product::class,
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }),
            'Technologies' => Technology::orderBy('name')->get()->map(function ($item) {
                return [
                    'type' => Technology::class,
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }),
            'Machines' => Machine::orderBy('name')->get()->map(function ($item) {
                return [
                    'type' => Machine::class,
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }),
            'Milestones' => Milestone::orderBy('year', 'desc')->get()->map(function ($item) {
                return [
                    'type' => Milestone::class,
                    'id' => $item->id,
                    'name' => "Year {$item->year}",
                ];
            }),
        ];
    }
    
    /**
     * Clear relevant caches based on entity type
     */
    private function clearCaches($entity)
    {
        if (!$entity) {
            return;
        }
        
        // Clear general caches
        Cache::forget('home:v1');
        
        // Clear entity-specific caches
        if ($entity instanceof Division) {
            Cache::forget('divisions:index');
            Cache::forget('division:' . $entity->slug);
        }
    }
}