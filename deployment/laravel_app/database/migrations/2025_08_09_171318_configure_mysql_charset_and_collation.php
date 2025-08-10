<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run charset operations on MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            // Set database charset and collation
            DB::statement('ALTER DATABASE ' . DB::getDatabaseName() . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            
            // Update existing tables to use utf8mb4 charset and collation
            $tables = [
                'users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 
                'jobs', 'job_batches', 'failed_jobs', 'settings', 'divisions', 
                'products', 'technologies', 'machines', 'media', 'milestones', 
                'clients', 'contact_messages'
            ];
            
            foreach ($tables as $table) {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    DB::statement("ALTER TABLE {$table} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it only optimizes charset/collation
    }
};