# Shared Hosting Deployment Guide for PT. Daya Swastika Perkasa Website

## Prerequisites
- Shared hosting account with cPanel
- PHP 8.1 or higher
- MySQL/MariaDB database
- SSH access (optional but recommended)
- FTP client (like FileZilla) or cPanel File Manager

## Step 1: Prepare Project Locally

### 1.1 Install Dependencies
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm ci
```

### 1.2 Build Frontend Assets
```bash
# Build production assets
npm run build
```

### 1.3 Configure Environment File
Create `.env` file with production settings:
```env
APP_NAME="PT. Daya Swastika Perkasa"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Optional: Mail settings if needed
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 1.4 Generate Application Key
```bash
php artisan key:generate
```

### 1.5 Optimize for Production
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Step 2: Prepare Database

### 2.1 Create Database in cPanel
1. Login to cPanel
2. Go to "MySQL Databases"
3. Create a new database (e.g., `youruser_dsp`)
4. Create a new user (e.g., `youruser_dspuser`)
5. Add user to database with ALL PRIVILEGES
6. Note down the credentials

### 2.2 Export Local Database (Optional)
If you have existing data:
```bash
# Export local database
mysqldump -u root -p your_local_db > database_backup.sql
```

## Step 3: Prepare Files for Upload

### 3.1 Create Deployment Package
Create a folder structure for deployment:

```
deployment/
├── public_html/           # Files for public_html directory
│   ├── .htaccess
│   ├── index.php
│   ├── robots.txt
│   ├── favicon.ico
│   ├── build/             # From npm run build
│   └── storage/           # Symlink or folder (see note)
│
└── laravel_app/           # Files for above public_html
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── .env
    └── artisan
```

### 3.2 Modify index.php
Edit `public/index.php` to point to correct paths:
```php
// Change these lines:
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// To:
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
```

### 3.3 Update .htaccess
Ensure `public/.htaccess` has these rules:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
    
    # Force HTTPS (optional)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# Disable directory browsing
Options -Indexes

# Protect .env file
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

## Step 4: Upload Files

### 4.1 Using FTP/SFTP
1. Connect to your hosting using FTP client
2. Upload contents of `deployment/public_html/` to `public_html/`
3. Upload `deployment/laravel_app/` to directory above `public_html/` (usually home directory)

### 4.2 Using cPanel File Manager
1. Login to cPanel
2. Open File Manager
3. Upload and extract a zip file of your project
4. Move files to appropriate directories

### 4.3 File Permissions
Set proper permissions:
```bash
# Directories
find /path/to/laravel_app -type d -exec chmod 755 {} \;

# Files
find /path/to/laravel_app -type f -exec chmod 644 {} \;

# Storage and cache need to be writable
chmod -R 775 /path/to/laravel_app/storage
chmod -R 775 /path/to/laravel_app/bootstrap/cache
```

## Step 5: Configure Application

### 5.1 Via SSH (If Available)
```bash
cd /home/yourusername/laravel_app

# Run migrations
php artisan migrate --force

# Seed the database
php artisan db:seed --force

# Create storage link
php artisan storage:link
```

### 5.2 Via Scheduled HTTP Request (If No SSH)
Create a temporary setup route in `routes/web.php`:
```php
// TEMPORARY - REMOVE AFTER SETUP
Route::get('/setup-deployment-temp-2024', function () {
    if (request()->get('key') !== 'your-secret-key-here') {
        abort(403);
    }
    
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('db:seed', ['--force' => true]);
    Artisan::call('storage:link');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    
    return 'Setup completed!';
});
```
Then visit: `https://yourdomain.com/setup-deployment-temp-2024?key=your-secret-key-here`
**IMPORTANT: Remove this route after setup!**

### 5.3 Handle Storage Link
If symlinks are disabled on your hosting:

Option A: Copy storage files manually
```bash
# Copy storage/app/public to public/storage
cp -r /home/yourusername/laravel_app/storage/app/public /home/yourusername/public_html/storage
```

Option B: Create a PHP script to serve files
Create `public/storage-files.php`:
```php
<?php
$file = '../laravel_app/storage/app/public/' . $_GET['path'];
if (file_exists($file)) {
    header('Content-Type: ' . mime_content_type($file));
    readfile($file);
} else {
    header("HTTP/1.0 404 Not Found");
}
```

Then add to `.htaccess`:
```apache
RewriteRule ^storage/(.*)$ storage-files.php?path=$1 [L]
```

## Step 6: Configure Cron Jobs

### 6.1 Laravel Scheduler
Add this cron job in cPanel:
```bash
* * * * * cd /home/yourusername/laravel_app && php artisan schedule:run >> /dev/null 2>&1
```

## Step 7: Security Hardening

### 7.1 Protect Sensitive Files
Add to root `.htaccess`:
```apache
# Protect sensitive files
<FilesMatch "^\.env|composer\.(json|lock)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 7.2 Disable Debug Mode
Ensure in `.env`:
```env
APP_DEBUG=false
APP_ENV=production
```

### 7.3 Configure CORS (if needed)
Update `config/cors.php` for your domain.

## Step 8: Testing

### 8.1 Check Homepage
Visit `https://yourdomain.com`

### 8.2 Test Admin Panel
1. Visit `https://yourdomain.com/login`
2. Login with default credentials:
   - Email: `admin@dsp.com`
   - Password: `admin123`
3. Change password immediately

### 8.3 Test File Uploads
1. Go to Admin > Media
2. Try uploading an image
3. Check if it displays correctly

### 8.4 Test Contact Form
1. Submit a test message
2. Check in Admin > Contact Messages

## Step 9: Maintenance

### 9.1 Clearing Cache
Create `public/clear-cache.php`:
```php
<?php
if ($_GET['key'] !== 'your-secret-key') die('Unauthorized');

require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('cache:clear');
$kernel->call('config:clear');
$kernel->call('route:clear');
$kernel->call('view:clear');

echo "Cache cleared!";
```
Visit: `https://yourdomain.com/clear-cache.php?key=your-secret-key`

### 9.2 Database Backup
Set up automatic backups in cPanel or use a backup script.

## Troubleshooting

### Common Issues:

1. **500 Internal Server Error**
   - Check `.htaccess` syntax
   - Verify PHP version (needs 8.1+)
   - Check file permissions
   - Review error logs in cPanel

2. **Storage not working**
   - Ensure storage folder is writable (775)
   - Check if symlink is created
   - Use fallback method if symlinks disabled

3. **Database connection error**
   - Verify credentials in `.env`
   - Check database host (usually localhost)
   - Ensure database user has all privileges

4. **Styles/JS not loading**
   - Run `npm run build` locally
   - Upload `public/build` folder
   - Clear browser cache

5. **Session/Cache errors**
   - Ensure `storage/framework/sessions` exists
   - Ensure `storage/framework/cache` exists
   - Set permissions to 775

## Important Notes

1. **Always backup** before deployment
2. **Test on staging** if possible
3. **Monitor error logs** after deployment
4. **Update passwords** immediately after deployment
5. **Set up SSL certificate** in cPanel
6. **Configure email settings** if using contact forms
7. **Set up monitoring** for uptime and performance

## Support Files Needed

Make sure these files are included:
- `.env` (production configuration)
- `vendor/` folder (PHP dependencies)
- `public/build/` folder (compiled assets)
- `storage/` folder structure
- Database with initial seed data

## Post-Deployment Checklist

- [ ] Website loads correctly
- [ ] Admin panel accessible
- [ ] File uploads working
- [ ] Contact form working
- [ ] All pages load without errors
- [ ] SSL certificate active
- [ ] Debug mode disabled
- [ ] Default passwords changed
- [ ] Backup system configured
- [ ] Error monitoring setup
- [ ] Cron jobs running
- [ ] Email configuration tested

---

## Quick Deployment Script

Save this as `deploy.sh` for quick deployment:

```bash
#!/bin/bash

# Configuration
LOCAL_DIR="/path/to/local/project"
REMOTE_USER="your-cpanel-username"
REMOTE_HOST="your-domain.com"
REMOTE_DIR="/home/$REMOTE_USER"

# Build assets
cd $LOCAL_DIR
npm run build
composer install --optimize-autoloader --no-dev

# Create deployment package
tar -czf deployment.tar.gz \
  --exclude=node_modules \
  --exclude=.git \
  --exclude=.env.local \
  --exclude=tests \
  --exclude=storage/app/public/* \
  --exclude=storage/logs/* \
  .

# Upload to server
scp deployment.tar.gz $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/

# Extract on server
ssh $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_DIR && tar -xzf deployment.tar.gz -C laravel_app/"

# Run migrations and optimizations
ssh $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_DIR/laravel_app && php artisan migrate --force && php artisan optimize"

echo "Deployment complete!"
```

---

For questions or issues, consult your hosting provider's documentation or Laravel deployment guides.