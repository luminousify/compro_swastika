# Shared Hosting Deployment Guide

This guide explains how to deploy the PT Swastika Investama Prima company website to shared hosting.

## Prerequisites

- Shared hosting with PHP 8.1+ support
- MySQL/MariaDB database
- SSH/FTP access to your hosting account
- Composer installed (or available via SSH)

## Step 1: Prepare Your Environment

### 1.1 Create Database
1. Login to your hosting control panel (cPanel, Plesk, etc.)
2. Create a new MySQL database
3. Create a database user with full privileges
4. Note down the database credentials

### 1.2 Configure Domain
1. Point your domain to the `public` directory of this project
2. Or move contents of `public` to your domain's root directory

## Step 2: Upload Files

### Option A: Using Git (Recommended)
```bash
# Clone the repository
git clone <repository-url> /path/to/your/domain

# Or if files are already uploaded, pull latest changes
cd /path/to/your/domain
git pull origin main
```

### Option B: Using FTP/File Manager
1. Upload all files to your hosting directory
2. Ensure the `public` directory is your document root

## Step 3: Configure Environment

### 3.1 Create .env File
```bash
cp .env.example .env
```

### 3.2 Edit .env File
Update the following variables:

```env
APP_NAME="PT Swastika Investama Prima"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Mail Configuration (for contact form)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Cache and Session (optimized for shared hosting)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=single
LOG_LEVEL=warning
```

## Step 4: Run Deployment Script

Make the deployment script executable and run it:

```bash
chmod +x deploy.sh
./deploy.sh
```

If you don't have SSH access, manually run these commands:

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed basic settings
php artisan db:seed --class=SettingSeeder --force

# Create storage symlink
php artisan storage:link

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Step 5: Configure File Permissions

Set appropriate file permissions (if you have SSH access):

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make specific directories writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Step 6: Configure Web Server

### Apache (.htaccess)
The optimized `.htaccess` file is already included in the `public` directory with:
- Security headers
- Gzip compression
- Browser caching
- File protection

### Nginx
If using Nginx, add this configuration to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}

# Security headers
add_header X-Frame-Options "DENY" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Cache static assets
location ~* \.(css|js|jpg|jpeg|png|gif|svg|webp|woff|woff2|ttf|eot)$ {
    expires 1M;
    add_header Cache-Control "public, immutable";
}

# Deny access to sensitive files
location ~ /\. {
    deny all;
}

location ~ /(storage|bootstrap|app|config|database|resources|routes|tests|vendor)/ {
    deny all;
}
```

## Step 7: Setup Administration

### 7.1 Create Admin User
```bash
php artisan db:seed --class=UserSeeder --force
```

Default admin credentials:
- Username: `admin@swastika.co.id`
- Password: Check the seeder or create manually

### 7.2 Access Admin Panel
Visit: `https://yourdomain.com/admin/login`

## Step 8: Configure Basic Settings

1. Login to admin panel
2. Go to Settings
3. Update company information:
   - Company Name
   - Address
   - Phone
   - Email
   - Social Media Links
   - Meta Description

## Step 9: Performance Optimization

### 9.1 Enable OPcache
Add to your `.htaccess` or ask hosting provider to enable:
```apache
php_value opcache.enable 1
php_value opcache.memory_consumption 256
php_value opcache.max_accelerated_files 4000
```

### 9.2 Database Optimization
- Enable MySQL query cache if available
- Regular database optimization via cPanel

### 9.3 CDN Setup (Optional)
- CloudFlare (recommended for free plan)
- Configure caching rules
- Enable minification

## Step 10: Security Checklist

- [ ] `.env` file is not publicly accessible
- [ ] Admin panel uses strong passwords
- [ ] Regular backups are configured
- [ ] SSL certificate is installed
- [ ] Security headers are working
- [ ] File permissions are correct

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check file permissions
- Verify `.env` configuration
- Check error logs

**Database Connection Error**
- Verify database credentials
- Check if database exists
- Ensure user has proper privileges

**Missing Assets/Styles**
- Run `php artisan storage:link`
- Check if public directory is correctly configured
- Verify file permissions

**Contact Form Not Working**
- Check mail configuration in `.env`
- Verify SMTP settings with hosting provider
- Check if port 587 is open

### Log Files
Check these files for errors:
- `storage/logs/laravel.log`
- Server error logs (usually in cPanel)

### Getting Help
- Check the application logs first
- Verify hosting requirements
- Contact hosting support for server-specific issues

## Maintenance

### Regular Tasks
- Update Laravel and dependencies monthly
- Backup database weekly
- Monitor disk space usage
- Review application logs

### Updates
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Performance Monitoring

Monitor these metrics:
- Page load times
- Database query performance
- Memory usage
- Storage space
- Error rates

The application includes health check endpoints:
- `/health` - Basic application status
- `/version` - Application version info

---

For technical support, check the application logs and hosting documentation first.