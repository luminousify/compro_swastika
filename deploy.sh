#!/bin/bash

# Deployment script for shared hosting
# Run this script after uploading files to shared hosting

set -e

echo "🚀 Starting deployment process..."

# Step 1: Check PHP version
echo "📋 Checking PHP version..."
php -v

# Step 2: Install/update composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Step 3: Clear and cache configurations
echo "⚙️ Optimizing configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 4: Run database migrations (if needed)
echo "🗃️ Running database migrations..."
php artisan migrate --force

# Step 5: Seed required settings (only if needed)
echo "🌱 Seeding basic settings..."
php artisan db:seed --class=SettingSeeder --force

# Step 6: Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link || true

# Step 7: Optimize for production
echo "🎯 Optimizing for production..."
php artisan optimize

# Step 8: Set proper permissions (if we have the rights)
echo "🔐 Setting file permissions..."
find storage -type f -exec chmod 644 {} \; || true
find storage -type d -exec chmod 755 {} \; || true
find bootstrap/cache -type f -exec chmod 644 {} \; || true
find bootstrap/cache -type d -exec chmod 755 {} \; || true

# Step 9: Clear any existing caches
echo "🧹 Clearing application cache..."
php artisan cache:clear || true
php artisan config:clear
php artisan view:clear

# Step 10: Generate fresh cache
echo "💾 Generating fresh cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment completed successfully!"
echo ""
echo "🔍 System information:"
echo "PHP Version: $(php -r 'echo PHP_VERSION;')"
echo "Laravel Version: $(php artisan --version)"
echo "Environment: $(php artisan env)"
echo ""
echo "🌐 Your application should now be ready!"