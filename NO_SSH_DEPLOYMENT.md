# Deployment Guide Without SSH Access

Since you don't have SSH access to your shared hosting server, here are alternative deployment methods:

## Method 1: FTP Deployment Script (Recommended)

### Setup
1. Run the FTP deployment script I created:
```bash
php deploy-ftp.php
```

2. On first run, it will create `.env.deploy` file. Edit it with your FTP credentials:
```env
FTP_HOST=ftp.yourdomain.com
FTP_USER=your-ftp-username
FTP_PASS=your-ftp-password
FTP_PORT=21
FTP_PASSIVE=true
FTP_SSL=false

REMOTE_PUBLIC_DIR=/public_html
REMOTE_APP_DIR=/laravel_app
```

3. Run the script again to deploy:
```bash
php deploy-ftp.php
```

## Method 2: Web-Based Deployment (After Initial Upload)

### Initial Setup
1. Add to your `.env` file on the server:
```env
DEPLOYMENT_ENABLED=true
DEPLOYMENT_KEY=your-unique-secret-key-here
```

2. After uploading files via FTP, visit these URLs to run deployment tasks:

### Available Deployment URLs

**Check Status:**
```
https://yourdomain.com/deploy?key=your-secret-key&action=status
```

**Run Database Migrations:**
```
https://yourdomain.com/deploy?key=your-secret-key&action=migrate
```

**Seed Database:**
```
https://yourdomain.com/deploy?key=your-secret-key&action=seed
```

**Create Storage Link:**
```
https://yourdomain.com/deploy?key=your-secret-key&action=storage-link
```

**Clear All Caches:**
```
https://yourdomain.com/deploy?key=your-secret-key&action=cache-clear
```

**Optimize Application:**
```
https://yourdomain.com/deploy?key=your-secret-key&action=optimize
```

**Full Deployment (All Steps):**
```
https://yourdomain.com/deploy?key=your-secret-key&action=full-deploy
```

**Health Check:**
```
https://yourdomain.com/deploy?key=your-secret-key&action=health-check
```

**Emergency Cleanup:**
```
https://yourdomain.com/deploy/cleanup?key=your-secret-key
```

## Method 3: cPanel File Manager

### Steps:
1. **Prepare locally:**
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Create deployment archive
tar -czf deploy.tar.gz \
  --exclude=node_modules \
  --exclude=.git \
  --exclude=.env.local \
  --exclude=tests \
  --exclude=storage/logs/* \
  .
```

2. **Upload via cPanel:**
   - Login to cPanel
   - Open File Manager
   - Upload `deploy.tar.gz` to home directory
   - Extract the archive
   - Move `public/*` files to `public_html/`
   - Move other files to `laravel_app/` directory

3. **Update paths:**
   - Edit `public_html/index.php`
   - Change paths to point to `../laravel_app/`

4. **Run deployment tasks:**
   - Use the web-based deployment URLs above

## Method 4: Using cPanel Terminal (if available)

Some cPanel installations have a terminal feature:
1. Login to cPanel
2. Look for "Terminal" in the Advanced section
3. If available, run commands directly:
```bash
cd ~/laravel_app
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

## Important Security Notes

### After Deployment:
1. **Disable deployment routes** by setting in `.env`:
```env
DEPLOYMENT_ENABLED=false
```

2. **Change the deployment key** to something unique:
```env
DEPLOYMENT_KEY=generate-a-very-long-random-string-here
```

3. **Delete deployment files** if no longer needed:
   - Remove `deploy-ftp.php`
   - Remove `.env.deploy`
   - Consider removing `DeploymentController.php` after initial setup

## Troubleshooting

### Storage Link Issues
If symlinks are disabled on your hosting:

1. **Option A:** Copy storage files manually via FTP:
   - Copy `storage/app/public/*` to `public_html/storage/`

2. **Option B:** Use the web deployment route:
   ```
   https://yourdomain.com/deploy?key=your-secret-key&action=storage-link
   ```
   (This will automatically copy files if symlink fails)

### Database Connection Issues
1. Check database credentials in `.env`
2. Ensure database host is `localhost` (not `127.0.0.1`)
3. Verify database user has ALL PRIVILEGES

### Permission Issues
Set via FTP client or cPanel File Manager:
- Directories: 755
- Files: 644
- Storage directories: 775
- Bootstrap/cache: 775

## Deployment Checklist

Before deployment:
- [ ] Build assets locally (`npm run build`)
- [ ] Optimize Composer (`composer install --no-dev`)
- [ ] Update `.env` with production values
- [ ] Generate strong deployment key

After deployment:
- [ ] Run migrations
- [ ] Seed database
- [ ] Create storage link
- [ ] Clear and optimize caches
- [ ] Test all pages
- [ ] Disable debug mode
- [ ] Disable deployment routes
- [ ] Change default passwords

## Quick Commands Reference

### Local preparation:
```bash
# One-time setup
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Deploy via FTP
php deploy-ftp.php
```

### Remote execution (via browser):
```bash
# Replace YOUR_DOMAIN and YOUR_KEY
curl "https://YOUR_DOMAIN/deploy?key=YOUR_KEY&action=full-deploy"
```

## Support

If you encounter issues:
1. Check the Laravel logs in `storage/logs/`
2. Use the health-check URL to diagnose problems
3. Try the emergency cleanup URL if the site is broken
4. Verify PHP version is 8.1 or higher
5. Ensure all required PHP extensions are enabled