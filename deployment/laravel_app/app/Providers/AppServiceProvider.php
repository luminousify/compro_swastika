<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Division;
use App\Models\Machine;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Technology;
use App\Models\User;
use App\Observers\CacheInvalidationObserver;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::define('manage-settings', [SettingPolicy::class, 'update']);

        // Register cache invalidation observers
        Setting::observe(CacheInvalidationObserver::class);
        Client::observe(CacheInvalidationObserver::class);
        Division::observe(CacheInvalidationObserver::class);
        Product::observe(CacheInvalidationObserver::class);
        Technology::observe(CacheInvalidationObserver::class);
        Machine::observe(CacheInvalidationObserver::class);
        Media::observe(CacheInvalidationObserver::class);
        Milestone::observe(CacheInvalidationObserver::class);
    }
}
