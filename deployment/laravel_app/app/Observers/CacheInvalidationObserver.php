<?php

namespace App\Observers;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;

class CacheInvalidationObserver
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Invalidate relevant caches based on model type
     */
    private function invalidateCache(Model $model): void
    {
        $modelClass = class_basename($model);
        $identifier = $this->getModelIdentifier($model);

        match ($modelClass) {
            'Setting' => $this->cacheService->invalidateContentCache('settings'),
            'Client' => $this->cacheService->invalidateContentCache('clients'),
            'Division' => $this->handleDivisionInvalidation($model),
            'Product' => $this->cacheService->invalidateContentCache('products', $identifier),
            'Technology' => $this->cacheService->invalidateContentCache('technologies', $identifier),
            'Machine' => $this->cacheService->invalidateContentCache('machines', $identifier),
            'Media' => $this->handleMediaInvalidation($model),
            'Milestone' => $this->cacheService->invalidateContentCache('milestones'),
            default => null
        };
    }

    /**
     * Handle division-specific cache invalidation
     */
    private function handleDivisionInvalidation(Model $division): void
    {
        $this->cacheService->invalidateContentCache('divisions');
        if ($division->slug) {
            $this->cacheService->invalidateContentCache('division', $division->slug);
        }
    }

    /**
     * Handle media-specific cache invalidation
     */
    private function handleMediaInvalidation(Model $media): void
    {
        // Check if it's a home slider image
        if ($media->is_home_slider) {
            $this->cacheService->invalidateContentCache('media', 'home_slider');
        }

        // Check if it's related to a division
        if ($media->mediable_type === 'App\\Models\\Division') {
            $division = $media->mediable;
            if ($division) {
                $this->cacheService->invalidateContentCache('media', 'division');
                $this->cacheService->invalidateContentCache('division', $division->slug);
            }
        }
    }

    /**
     * Get model identifier for cache invalidation
     */
    private function getModelIdentifier(Model $model): ?string
    {
        return match (class_basename($model)) {
            'Division' => $model->slug ?? $model->id,
            'Product', 'Technology', 'Machine' => $model->division?->slug ?? $model->division_id,
            default => $model->id ?? null
        };
    }
}