<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class DeploymentController extends Controller
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = config('deployment.secret_key', 'your-secret-deployment-key-2024');
    }

    /**
     * Web-based deployment endpoint for shared hosting
     */
    public function deploy(Request $request)
    {
        // Verify secret key
        if ($request->get('key') !== $this->secretKey) {
            abort(403, 'Unauthorized');
        }

        $action = $request->get('action', 'status');
        $output = [];

        try {
            switch ($action) {
                case 'migrate':
                    Artisan::call('migrate', ['--force' => true]);
                    $output['migrate'] = Artisan::output();
                    break;

                case 'seed':
                    Artisan::call('db:seed', ['--force' => true]);
                    $output['seed'] = Artisan::output();
                    break;

                case 'storage-link':
                    // Try to create symlink
                    try {
                        Artisan::call('storage:link');
                        $output['storage'] = 'Storage link created successfully';
                    } catch (\Exception $e) {
                        // Fallback: copy files if symlink fails
                        $this->copyStorageFiles();
                        $output['storage'] = 'Storage files copied (symlink not available)';
                    }
                    break;

                case 'cache-clear':
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    $output['cache'] = 'All caches cleared';
                    break;

                case 'optimize':
                    Artisan::call('config:cache');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                    Artisan::call('optimize');
                    $output['optimize'] = 'Application optimized';
                    break;

                case 'full-deploy':
                    // Run all deployment steps
                    Artisan::call('migrate', ['--force' => true]);
                    $output['migrate'] = Artisan::output();
                    
                    Artisan::call('db:seed', ['--force' => true]);
                    $output['seed'] = Artisan::output();
                    
                    try {
                        Artisan::call('storage:link');
                        $output['storage'] = 'Storage link created';
                    } catch (\Exception $e) {
                        $this->copyStorageFiles();
                        $output['storage'] = 'Storage files copied';
                    }
                    
                    Artisan::call('cache:clear');
                    Artisan::call('config:cache');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                    Artisan::call('optimize');
                    $output['optimize'] = 'Application optimized';
                    break;

                case 'health-check':
                    $output = $this->healthCheck();
                    break;

                case 'status':
                default:
                    $output = $this->getStatus();
                    break;
            }

            return response()->json([
                'success' => true,
                'action' => $action,
                'output' => $output,
                'timestamp' => now()->toIso8601String()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get deployment status
     */
    private function getStatus()
    {
        return [
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'url' => config('app.url'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
            'available_actions' => [
                'migrate' => 'Run database migrations',
                'seed' => 'Seed the database',
                'storage-link' => 'Create storage symlink',
                'cache-clear' => 'Clear all caches',
                'optimize' => 'Optimize application',
                'full-deploy' => 'Run full deployment',
                'health-check' => 'Run health check',
                'status' => 'Get current status'
            ]
        ];
    }

    /**
     * Perform health check
     */
    private function healthCheck()
    {
        $checks = [];

        // Database connection
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // Storage writable
        $storagePath = storage_path('app');
        if (is_writable($storagePath)) {
            $checks['storage'] = ['status' => 'ok', 'message' => 'Writable'];
        } else {
            $checks['storage'] = ['status' => 'error', 'message' => 'Not writable'];
        }

        // Cache writable
        $cachePath = storage_path('framework/cache');
        if (is_writable($cachePath)) {
            $checks['cache'] = ['status' => 'ok', 'message' => 'Writable'];
        } else {
            $checks['cache'] = ['status' => 'error', 'message' => 'Not writable'];
        }

        // Public storage accessible
        $publicStorage = public_path('storage');
        if (file_exists($publicStorage)) {
            $checks['public_storage'] = ['status' => 'ok', 'message' => 'Exists'];
        } else {
            $checks['public_storage'] = ['status' => 'warning', 'message' => 'Not found'];
        }

        // Tables exist
        try {
            $tables = DB::select('SHOW TABLES');
            $tableCount = count($tables);
            $checks['tables'] = ['status' => 'ok', 'message' => "$tableCount tables found"];
        } catch (\Exception $e) {
            $checks['tables'] = ['status' => 'error', 'message' => 'Could not check tables'];
        }

        // PHP extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'xml', 'ctype', 'json', 'bcmath', 'openssl'];
        $missingExtensions = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }
        
        if (empty($missingExtensions)) {
            $checks['php_extensions'] = ['status' => 'ok', 'message' => 'All required extensions loaded'];
        } else {
            $checks['php_extensions'] = ['status' => 'error', 'message' => 'Missing: ' . implode(', ', $missingExtensions)];
        }

        return $checks;
    }

    /**
     * Check database connection
     */
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return 'Connected';
        } catch (\Exception $e) {
            return 'Not connected: ' . $e->getMessage();
        }
    }

    /**
     * Check storage status
     */
    private function checkStorage()
    {
        $publicStorage = public_path('storage');
        if (is_link($publicStorage)) {
            return 'Symlink exists';
        } elseif (is_dir($publicStorage)) {
            return 'Directory exists (no symlink)';
        } else {
            return 'Not configured';
        }
    }

    /**
     * Check cache status
     */
    private function checkCache()
    {
        try {
            Cache::put('deployment_test', 'test', 1);
            Cache::forget('deployment_test');
            return 'Working';
        } catch (\Exception $e) {
            return 'Not working: ' . $e->getMessage();
        }
    }

    /**
     * Copy storage files as fallback when symlink not available
     */
    private function copyStorageFiles()
    {
        $source = storage_path('app/public');
        $destination = public_path('storage');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        File::copyDirectory($source, $destination);
    }

    /**
     * Emergency cleanup for deployment issues
     */
    public function cleanup(Request $request)
    {
        if ($request->get('key') !== $this->secretKey) {
            abort(403, 'Unauthorized');
        }

        // Clear all caches
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        // Clear compiled files
        $compiledPath = storage_path('framework/bootstrap.php');
        if (file_exists($compiledPath)) {
            unlink($compiledPath);
        }

        // Clear sessions
        $sessionPath = storage_path('framework/sessions');
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cleanup completed'
        ]);
    }
}