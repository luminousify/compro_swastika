#!/usr/bin/env php
<?php

/**
 * FTP Deployment Script for Shared Hosting
 * Run: php deploy-ftp.php
 */

class FTPDeployer
{
    private $config;
    private $ftp;
    private $excludePatterns = [
        'node_modules',
        '.git',
        '.env.local',
        'tests',
        'storage/logs/*',
        'storage/app/public/*',
        '.DS_Store',
        'deploy-ftp.php',
        'deployment',
        '.kiro'
    ];

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $configFile = __DIR__ . '/.env.deploy';
        
        if (!file_exists($configFile)) {
            $this->createConfigFile($configFile);
            die("Please edit .env.deploy with your FTP credentials and run again.\n");
        }

        $this->config = parse_ini_file($configFile);
        
        if (empty($this->config['FTP_HOST']) || empty($this->config['FTP_USER']) || empty($this->config['FTP_PASS'])) {
            die("Error: FTP credentials not configured in .env.deploy\n");
        }
    }

    private function createConfigFile($file)
    {
        $template = <<<EOT
# FTP Deployment Configuration
FTP_HOST=ftp.yourdomain.com
FTP_USER=your-ftp-username
FTP_PASS=your-ftp-password
FTP_PORT=21
FTP_PASSIVE=true
FTP_SSL=false

# Remote paths
REMOTE_PUBLIC_DIR=/public_html
REMOTE_APP_DIR=/laravel_app

# Local build settings
BUILD_ASSETS=true
OPTIMIZE_COMPOSER=true
EOT;
        
        file_put_contents($file, $template);
        echo "Created .env.deploy configuration file.\n";
    }

    public function deploy()
    {
        echo "🚀 Starting deployment...\n\n";

        // Step 1: Build assets locally
        if ($this->config['BUILD_ASSETS'] === 'true') {
            $this->buildAssets();
        }

        // Step 2: Optimize composer
        if ($this->config['OPTIMIZE_COMPOSER'] === 'true') {
            $this->optimizeComposer();
        }

        // Step 3: Connect to FTP
        $this->connectFTP();

        // Step 4: Create remote directories
        $this->createRemoteDirectories();

        // Step 5: Upload application files
        $this->uploadDirectory(__DIR__, $this->config['REMOTE_APP_DIR'], [
            'public',
            'node_modules',
            '.git',
            'tests',
            'storage/logs',
            'storage/app/public'
        ]);

        // Step 6: Upload public files
        $this->uploadDirectory(__DIR__ . '/public', $this->config['REMOTE_PUBLIC_DIR'], []);

        // Step 7: Update index.php paths
        $this->updateIndexPHP();

        // Step 8: Close FTP connection
        ftp_close($this->ftp);

        echo "\n✅ Deployment complete!\n";
        echo "\n⚠️  Don't forget to:\n";
        echo "1. Run database migrations via web route\n";
        echo "2. Create storage symlink or copy files\n";
        echo "3. Clear caches\n";
        echo "4. Update .env file on server\n";
    }

    private function buildAssets()
    {
        echo "📦 Building frontend assets...\n";
        exec('npm run build 2>&1', $output, $return);
        
        if ($return !== 0) {
            echo "Warning: Asset build failed. Continue anyway? (y/n): ";
            $answer = trim(fgets(STDIN));
            if ($answer !== 'y') {
                die("Deployment cancelled.\n");
            }
        } else {
            echo "✓ Assets built successfully\n\n";
        }
    }

    private function optimizeComposer()
    {
        echo "🔧 Optimizing Composer dependencies...\n";
        exec('composer install --optimize-autoloader --no-dev 2>&1', $output, $return);
        
        if ($return !== 0) {
            echo "Warning: Composer optimization failed. Continue anyway? (y/n): ";
            $answer = trim(fgets(STDIN));
            if ($answer !== 'y') {
                die("Deployment cancelled.\n");
            }
        } else {
            echo "✓ Composer optimized\n\n";
        }
    }

    private function connectFTP()
    {
        echo "🔌 Connecting to FTP server...\n";
        
        $port = isset($this->config['FTP_PORT']) ? $this->config['FTP_PORT'] : 21;
        $ssl = isset($this->config['FTP_SSL']) && $this->config['FTP_SSL'] === 'true';
        
        if ($ssl) {
            $this->ftp = ftp_ssl_connect($this->config['FTP_HOST'], $port);
        } else {
            $this->ftp = ftp_connect($this->config['FTP_HOST'], $port);
        }
        
        if (!$this->ftp) {
            die("Error: Could not connect to FTP server\n");
        }
        
        if (!ftp_login($this->ftp, $this->config['FTP_USER'], $this->config['FTP_PASS'])) {
            die("Error: FTP login failed\n");
        }
        
        // Force passive mode and set options to handle NAT/firewall issues
        ftp_set_option($this->ftp, FTP_USEPASVADDRESS, false);
        ftp_pasv($this->ftp, true);
        
        echo "✓ Connected to FTP\n\n";
    }

    private function createRemoteDirectories()
    {
        echo "📁 Creating remote directories...\n";
        
        $dirs = [
            $this->config['REMOTE_APP_DIR'],
            $this->config['REMOTE_APP_DIR'] . '/storage/framework/cache',
            $this->config['REMOTE_APP_DIR'] . '/storage/framework/sessions',
            $this->config['REMOTE_APP_DIR'] . '/storage/framework/views',
            $this->config['REMOTE_APP_DIR'] . '/storage/logs',
            $this->config['REMOTE_APP_DIR'] . '/storage/app/public',
            $this->config['REMOTE_APP_DIR'] . '/bootstrap/cache',
        ];
        
        foreach ($dirs as $dir) {
            @ftp_mkdir($this->ftp, $dir);
        }
        
        echo "✓ Directories ready\n\n";
    }

    private function uploadDirectory($localDir, $remoteDir, $exclude = [])
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($localDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        $totalFiles = iterator_count($files);
        $current = 0;
        
        echo "📤 Uploading to $remoteDir...\n";
        
        foreach ($files as $file) {
            $current++;
            $localPath = $file->getRealPath();
            $relativePath = str_replace($localDir . DIRECTORY_SEPARATOR, '', $localPath);
            $remotePath = $remoteDir . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            
            // Check if should exclude
            $shouldExclude = false;
            foreach ($this->excludePatterns as $pattern) {
                if (fnmatch($pattern, $relativePath) || strpos($relativePath, $pattern) === 0) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            foreach ($exclude as $pattern) {
                if (fnmatch($pattern, $relativePath) || strpos($relativePath, $pattern) === 0) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if ($shouldExclude) {
                continue;
            }
            
            if ($file->isDir()) {
                @ftp_mkdir($this->ftp, $remotePath);
            } else {
                $this->uploadFile($localPath, $remotePath);
                
                // Show progress
                if ($current % 10 == 0) {
                    $percent = round(($current / $totalFiles) * 100);
                    echo "  Progress: $percent% ($current/$totalFiles)\r";
                }
            }
        }
        
        echo "\n✓ Upload complete\n\n";
    }

    private function uploadFile($localFile, $remoteFile)
    {
        $maxRetries = 3;
        $retry = 0;
        
        while ($retry < $maxRetries) {
            if (ftp_put($this->ftp, $remoteFile, $localFile, FTP_BINARY)) {
                return true;
            }
            $retry++;
            if ($retry < $maxRetries) {
                sleep(1); // Wait before retry
            }
        }
        
        echo "\nWarning: Failed to upload $localFile\n";
        return false;
    }

    private function updateIndexPHP()
    {
        echo "📝 Updating index.php paths...\n";
        
        $tempFile = tempnam(sys_get_temp_dir(), 'index');
        $content = file_get_contents(__DIR__ . '/public/index.php');
        
        // Update paths to point to laravel_app directory
        $content = str_replace(
            ["require __DIR__.'/../vendor/autoload.php';", "require_once __DIR__.'/../bootstrap/app.php';"],
            ["require __DIR__.'/../laravel_app/vendor/autoload.php';", "require_once __DIR__.'/../laravel_app/bootstrap/app.php';"],
            $content
        );
        
        file_put_contents($tempFile, $content);
        
        if (ftp_put($this->ftp, $this->config['REMOTE_PUBLIC_DIR'] . '/index.php', $tempFile, FTP_ASCII)) {
            echo "✓ index.php updated\n\n";
        } else {
            echo "⚠️  Warning: Could not update index.php\n\n";
        }
        
        unlink($tempFile);
    }
}

// Run the deployer
$deployer = new FTPDeployer();
$deployer->deploy();