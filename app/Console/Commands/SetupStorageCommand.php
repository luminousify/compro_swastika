<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupStorageCommand extends Command
{
    protected $signature = 'storage:setup';
    protected $description = 'Set up storage symlink with fallback mechanism';

    public function handle()
    {
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        // Check if symlink already exists and is valid
        if (is_link($linkPath) && readlink($linkPath) === $targetPath) {
            $this->info('Storage symlink already exists and is valid.');
            return 0;
        }

        // Remove existing link/directory if it exists
        if (file_exists($linkPath)) {
            if (is_link($linkPath)) {
                unlink($linkPath);
                $this->info('Removed existing invalid symlink.');
            } else {
                $this->error('A file or directory already exists at public/storage. Please remove it manually.');
                return 1;
            }
        }

        // Try to create symlink
        try {
            symlink($targetPath, $linkPath);
            $this->info('Storage symlink created successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->warn('Could not create symlink: ' . $e->getMessage());
            $this->info('Symlink creation failed. Files will be copied directly when needed.');
            
            // Create the directory structure for fallback
            if (!File::exists($linkPath)) {
                File::makeDirectory($linkPath, 0755, true);
                $this->info('Created public/storage directory for fallback file copying.');
            }
            
            return 0;
        }
    }
}