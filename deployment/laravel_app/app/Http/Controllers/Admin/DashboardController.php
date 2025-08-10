<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Division;
use App\Models\Machine;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Technology;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics with caching for performance
        $stats = Cache::remember('admin:dashboard:stats', 300, function () {
            return [
                'divisions' => Division::count(),
                'products' => Product::count() + Technology::count() + Machine::count(),
                'media' => Media::count(),
                'milestones' => Milestone::count(),
                'clients' => Client::count(),
                'unhandled_messages' => ContactMessage::where('handled', false)->count(),
            ];
        });

        // Get recent contact messages
        $recentMessages = ContactMessage::where('handled', false)
            ->latest()
            ->limit(5)
            ->get();

        // Get system status
        $systemStatus = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => $this->checkDatabaseConnection(),
            'cache_driver' => config('cache.default'),
            'storage_link' => $this->checkStorageLink(),
        ];

        // Get quick actions based on user role
        $quickActions = $this->getQuickActions();

        // Get recent activity
        $recentActivity = $this->getRecentActivity();

        return view('admin.dashboard', compact(
            'stats',
            'recentMessages',
            'systemStatus',
            'quickActions',
            'recentActivity'
        ));
    }

    /**
     * Get quick actions based on user role
     */
    private function getQuickActions(): array
    {
        $actions = [
            [
                'title' => 'Add Division',
                'icon' => 'folder-plus',
                'route' => 'admin.divisions.create',
                'color' => 'blue',
            ],
            [
                'title' => 'Upload Media',
                'icon' => 'image',
                'route' => 'admin.media.create',
                'color' => 'green',
            ],
            [
                'title' => 'Add Client',
                'icon' => 'users',
                'route' => 'admin.clients.create',
                'color' => 'purple',
            ],
            [
                'title' => 'View Messages',
                'icon' => 'mail',
                'route' => 'admin.messages.index',
                'color' => 'yellow',
            ],
        ];

        // Add admin-only actions
        if (auth()->user()->isAdmin()) {
            array_unshift($actions, [
                'title' => 'Settings',
                'icon' => 'cog',
                'route' => 'admin.settings.edit',
                'color' => 'gray',
            ]);
            
            $actions[] = [
                'title' => 'Manage Users',
                'icon' => 'user-group',
                'route' => 'admin.users.index',
                'color' => 'indigo',
            ];
        }

        return $actions;
    }

    /**
     * Get recent activity across all models
     */
    private function getRecentActivity(): array
    {
        $activity = [];

        // Get recent divisions
        $recentDivisions = Division::latest()
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'division',
                    'title' => $item->name,
                    'action' => 'created',
                    'time' => $item->created_at,
                ];
            });

        // Get recent products
        $recentProducts = Product::with('division')
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'product',
                    'title' => $item->name,
                    'action' => 'created',
                    'time' => $item->created_at,
                ];
            });

        // Get recent clients
        $recentClients = Client::latest()
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'client',
                    'title' => $item->name,
                    'action' => 'created',
                    'time' => $item->created_at,
                ];
            });

        // Merge and sort by time
        $activity = collect()
            ->merge($recentDivisions)
            ->merge($recentProducts)
            ->merge($recentClients)
            ->sortByDesc('time')
            ->take(10)
            ->values()
            ->toArray();

        return $activity;
    }

    /**
     * Check database connection status
     */
    private function checkDatabaseConnection(): string
    {
        try {
            DB::connection()->getPdo();
            return 'Connected';
        } catch (\Exception $e) {
            return 'Disconnected';
        }
    }

    /**
     * Check if storage link exists
     */
    private function checkStorageLink(): string
    {
        $publicStoragePath = public_path('storage');
        
        if (is_link($publicStoragePath)) {
            return 'Symlink Active';
        } elseif (is_dir($publicStoragePath)) {
            return 'Directory Copy';
        } else {
            return 'Not Configured';
        }
    }
}