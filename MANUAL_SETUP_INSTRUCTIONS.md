# Manual Setup Instructions for 500 Error Resolution

## The 500 error is likely due to missing configuration. Follow these steps:

## 1. Upload the New Package
I've created `deployment-package-with-env.tar.gz` which includes:
- Proper .env configuration
- DeploymentController
- All necessary files

Upload and extract this in your cPanel home directory.

## 2. Database Configuration
The .env file is configured with your database credentials:
- Database: `dayaswas_lara785`
- Username: `dayaswas_lara785`
- Password: `30pk#l5GChKC.1`

## 3. Manual Setup via cPanel

### Option A: Using cPanel Terminal (if available)
```bash
cd ~/laravel_app

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed the database
php artisan db:seed --force

# Create storage link (or copy files if symlinks disabled)
php artisan storage:link

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Set permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Option B: Using Web Deployment Routes
After uploading the new package, access these URLs in order:

1. **Generate Application Key** (if needed):
   ```
   https://dayaswastika.com/deploy?key=dsp2024deploy-secure-key-change-this&action=status
   ```

2. **Run Migrations**:
   ```
   https://dayaswastika.com/deploy?key=dsp2024deploy-secure-key-change-this&action=migrate
   ```

3. **Seed Database**:
   ```
   https://dayaswastika.com/deploy?key=dsp2024deploy-secure-key-change-this&action=seed
   ```

4. **Create Storage Link**:
   ```
   https://dayaswastika.com/deploy?key=dsp2024deploy-secure-key-change-this&action=storage-link
   ```

5. **Clear Caches**:
   ```
   https://dayaswastika.com/deploy?key=dsp2024deploy-secure-key-change-this&action=cache-clear
   ```

6. **Full Deploy** (runs all steps):
   ```
   https://dayaswastika.com/deploy?key=dsp2024deploy-secure-key-change-this&action=full-deploy
   ```

### Option C: Manual Database Setup via phpMyAdmin
If deployment routes still give 500 error:

1. Login to cPanel → phpMyAdmin
2. Select database `dayaswas_lara785`
3. Import the SQL file from the migrations

## 4. Common 500 Error Causes & Solutions

### Check these in order:

1. **PHP Version**: Ensure PHP 8.1 or higher
   - Check in cPanel → Select PHP Version

2. **File Permissions**:
   ```
   storage/: 775
   bootstrap/cache/: 775
   All other directories: 755
   All files: 644
   ```

3. **Missing .htaccess** in public_html:
   Create if missing with this content:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   ```

4. **Check Error Logs**:
   - cPanel → Errors
   - Or check `laravel_app/storage/logs/laravel.log`

5. **Missing PHP Extensions**:
   Required extensions (enable in cPanel → Select PHP Version):
   - PDO
   - pdo_mysql
   - mbstring
   - tokenizer
   - xml
   - ctype
   - json
   - bcmath
   - openssl
   - fileinfo

## 5. Testing

After setup, test these URLs:
1. Homepage: `https://dayaswastika.com`
2. Admin login: `https://dayaswastika.com/admin/login`
   - Email: `admin@dayaswastika.com`
   - Password: `Admin@2024DSP`

## 6. Security - IMPORTANT

After successful deployment:

1. **Change deployment key** in .env:
   ```
   DEPLOYMENT_KEY=generate-new-secure-random-key-here
   ```

2. **Disable deployment** in .env:
   ```
   DEPLOYMENT_ENABLED=false
   ```

3. **Change admin password** immediately after first login

## Quick Troubleshooting

If still getting 500 error:

1. **Enable debug mode temporarily** in .env:
   ```
   APP_DEBUG=true
   ```
   Visit the site to see the actual error, then disable debug mode.

2. **Check file paths** in `public_html/index.php`:
   Should point to `../laravel_app/` directories

3. **Verify database connection**:
   Create a test file `public_html/test-db.php`:
   ```php
   <?php
   try {
       $pdo = new PDO('mysql:host=localhost;dbname=dayaswas_lara785', 
                      'dayaswas_lara785', 
                      '30pk#l5GChKC.1');
       echo "Database connected successfully";
   } catch (Exception $e) {
       echo "Error: " . $e->getMessage();
   }
   ```
   Visit `https://dayaswastika.com/test-db.php`
   Delete this file after testing!

## Files to Upload

Upload `deployment-package-with-env.tar.gz` which contains:
- `laravel_app/` - Complete application with .env configured
- `public_html/` - Public files with correct paths

The package is ready and includes all necessary configurations.