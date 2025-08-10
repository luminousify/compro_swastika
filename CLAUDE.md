# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 company profile website for PT. Daya Swastika Perkasa (DSP), built with:
- **Backend**: Laravel 11.45.1, PHP 8.4, MariaDB/MySQL
- **Frontend**: Blade templates, TailwindCSS (built via Vite), optional Alpine.js
- **Media**: Intervention/Image for image processing
- **Deployment**: Designed for shared hosting (cPanel)

## Essential Commands

### Development Server
```bash
# Start all services concurrently (server, queue, logs, vite)
composer dev

# Or run individually:
php artisan serve          # Laravel dev server
npm run dev                # Vite dev server with HMR
php artisan queue:listen   # Process queued jobs
php artisan pail          # Real-time log viewer
```

### Build & Deployment
```bash
# Install dependencies
composer install
npm ci

# Build assets
npm run build

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Create storage link
php artisan storage:link
```

### Database
```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Unit/UserModelTest.php

# Run with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Static analysis with PHPStan (level 5)
./vendor/bin/phpstan analyse

# Check performance with Lighthouse
npm run lighthouse
npm run lighthouse:prod https://your-domain.com
```

## Architecture Overview

### Database Schema
The application uses these core tables:
- **users**: Admin and Sales roles (enum: admin|sales)
- **settings**: Single-row JSON configuration for site settings
- **divisions**: Business divisions with slug-based routing
- **products/technologies/machines**: Belong to divisions
- **media**: Polymorphic attachments for images/videos
- **milestones**: Company history timeline
- **clients**: Client showcase with logos
- **contact_messages**: Form submissions (no email sending in v1)

### Key Business Logic

#### User Roles & Permissions
- **Admin**: Full access to all modules including Settings and User management
- **Sales**: CRUD access to content (Divisions, Products, Media, etc.) but no Settings/User access
- No soft deletes - all deletions are permanent
- Direct publishing without approval workflow

#### Media Management
- Images: Max 5MB, auto-resized to multiple sizes (1920/1280/768px)
- WebP generation when supported
- Videos: Either file upload (max 50MB) or YouTube/Vimeo embed URLs
- Stored in `storage/app/public` with public symlink

#### Content Rules
- Contact form: Rate limited (3/min per IP), honeypot protection
- Image validation: Min 1200px width for hero images, aspect ratio guards
- All text content sanitized except whitelisted embed fields

### Public Routes Structure
- `/` - Home with hero, slider, about snippet, client logos
- `/visi-misi` - Vision and mission from Settings
- `/milestones` - Timeline sorted oldest to newest
- `/line-of-business` - Division grid with detail pages
- `/contact` - Contact form and company info

### Admin Panel Structure
- `/admin/dashboard` - Quick stats and recent messages
- `/admin/settings` - Site configuration (Admin only)
- `/admin/divisions` - Manage business divisions
- `/admin/media` - Media library management
- `/admin/users` - User management (Admin only)

## Important Conventions

### File Organization
- Controllers: `app/Http/Controllers/Admin/*` for admin panel
- Models: `app/Models/*` with relationships defined
- Views: `resources/views/admin/*` for admin, `resources/views/*` for public
- Assets: `resources/css/app.css` and `resources/js/app.js` compiled via Vite

### Validation & Security
- CSRF protection on all forms
- Input validation in FormRequests
- Laravel policies for role-based access
- File uploads validated by MIME type and size

### Performance Considerations
- Public queries cached for 10-60 minutes
- Images lazy-loaded with responsive srcset
- TailwindCSS purged in production build
- No runtime Node.js required on server

## Deployment Notes

For shared hosting deployment:
1. Build locally with `npm run build`
2. Include `vendor/` and `public/build/` in deployment package
3. Set document root to `/public` directory
4. Configure `.env` with production values
5. Run `php artisan storage:link` or manually copy storage files if symlinks disabled
6. Ensure `storage/` and `bootstrap/cache/` are writable (755/775)

## Environment Variables

Key environment variables to configure:
- `DB_*` - Database credentials
- `APP_URL` - Production URL
- `CACHE_TTL_*` - Cache durations
- `MAX_IMAGE_SIZE` / `MAX_VIDEO_SIZE` - Upload limits in KB
- `CONTACT_FORM_RATE_LIMIT` - Rate limit per minute
- Social media and SEO configuration variables