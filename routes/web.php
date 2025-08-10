<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MilestoneController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Services\SEOService;
use Illuminate\Support\Facades\Route;

// SEO routes
Route::get('/sitemap.xml', function (SEOService $seoService) {
    $sitemap = $seoService->generateSitemap();
    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function (SEOService $seoService) {
    $robots = $seoService->generateRobotsTxt();
    return response($robots, 200)
        ->header('Content-Type', 'text/plain');
})->name('robots');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Password change routes (for forced password changes)
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');
});

// Admin routes
Route::middleware(['auth', 'force.password.change'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        // Settings management
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        
        // User management
        Route::resource('users', UserController::class);
    });
    
    // Sales and Admin routes
    Route::middleware('role:admin,sales')->group(function () {
        // Division management
        Route::get('/divisions/{division}/delete', [DivisionController::class, 'delete'])->name('divisions.delete');
        Route::post('/divisions/reorder', [DivisionController::class, 'reorder'])->name('divisions.reorder');
        Route::resource('divisions', DivisionController::class);
        
        // Media management
        Route::post('/media/bulk', [MediaController::class, 'bulkUpload'])->name('media.bulk');
        Route::post('/media/reorder', [MediaController::class, 'reorder'])->name('media.reorder');
        Route::resource('media', MediaController::class);
        
        // Milestone management
        Route::post('/milestones/reorder', [MilestoneController::class, 'reorder'])->name('milestones.reorder');
        Route::resource('milestones', MilestoneController::class);
        
        // Client management
        Route::post('/clients/reorder', [ClientController::class, 'reorder'])->name('clients.reorder');
        Route::resource('clients', ClientController::class);
        
        // Contact message management
        Route::get('/messages/export', [ContactMessageController::class, 'export'])->name('messages.export');
        Route::post('/messages/purge', [ContactMessageController::class, 'purge'])->name('messages.purge');
        Route::patch('/messages/{message}/handle', [ContactMessageController::class, 'handle'])->name('messages.handle');
        Route::resource('messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);
    });
});

// Public Routes
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\MilestoneController as PublicMilestoneController;
use App\Http\Controllers\Public\DivisionController as PublicDivisionController;
use App\Http\Controllers\Public\ContactController;

Route::get('/', [PublicHomeController::class, 'index'])->name('home');
Route::get('/visi-misi', [PageController::class, 'visiMisi'])->name('visi-misi');
Route::get('/milestones', [PublicMilestoneController::class, 'index'])->name('milestones');
Route::get('/divisions', [PublicDivisionController::class, 'index'])->name('divisions.index');
Route::get('/line-of-business', [PublicDivisionController::class, 'index'])->name('line-of-business');
Route::get('/divisions/{division:slug}', [PublicDivisionController::class, 'show'])->name('divisions.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Operational endpoints
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toDateTimeString(),
    ]);
})->name('health');

Route::get('/version', function () {
    $versionFile = base_path('VERSION');
    $version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '1.0.0';
    
    return response()->json([
        'version' => $version,
        'build' => env('BUILD_NUMBER', 'dev'),
        'environment' => app()->environment(),
        'php_version' => PHP_VERSION,
    ]);
})->name('version');

// Deployment routes (REMOVE IN PRODUCTION or secure properly)
if (config('deployment.enabled', false)) {
    Route::get('/deploy', [\App\Http\Controllers\DeploymentController::class, 'deploy'])->name('deploy');
    Route::get('/deploy/cleanup', [\App\Http\Controllers\DeploymentController::class, 'cleanup'])->name('deploy.cleanup');
}
