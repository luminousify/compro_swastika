# Implementation Plan

- [x] 0. Bootstrap initial setup and seed data
  - Create database seeders for initial admin user (with forced password change), 1 division, 2 milestones, 6 clients, and 4 slider images
  - Set up placeholder images for missing hero/client logos with proper aspect ratios
  - Create basic .env.example with all required configuration variables
  - Implement Laravel Pint (formatting) and Larastan level 5 (static analysis) for code quality
  - Write acceptance test ensuring UAT instance renders Home page without manual database setup
  - _Requirements: 6.1, 1.1, 1.4, 3.1_

- [x] 1. Set up database foundation and core models
  - Create database migrations for all tables with proper charset (utf8mb4), collation (utf8mb4_unicode_ci), indexes, and constraints
  - Implement User model with role enum, authentication, and authorization methods
  - Create Setting model with JSON data field and accessor methods for common settings
  - Write unit tests for model relationships, validation, and scopes
  - _Requirements: 18.1, 18.2, 15.5, 6.3, 7.1_

- [x] 2. Implement core content models with relationships
  - Create Division model with slug, polymorphic media relationships, and route model binding
  - Implement Product, Technology, Machine models with division relationships and ordering
  - Create Media model with polymorphic relationships, type casting, and accessor methods
  - Implement Milestone model with year validation and ordering scopes
  - Create Client and ContactMessage models with appropriate relationships and scopes
  - Write comprehensive unit tests for all model relationships and business logic
  - _Requirements: 4.1, 4.3, 4.4, 3.1, 1.4, 5.3, 13.1_

- [x] 3. Build authentication and authorization system
  - Configure Laravel authentication with custom login throttling (5 attempts/minute)
  - Implement password policy validation (minimum 8 characters, weak password detection)
  - Set up session configuration (HttpOnly, Secure, SameSite=Lax, 60-minute timeout)
  - Create role-based authorization policies for Admin and Sales users
  - Implement forgot password functionality for admin users
  - Write feature tests for authentication flows and authorization checks
  - _Requirements: 6.1, 6.2, 7.1, 7.2, 12.5, 12.6, 18.5_

- [x] 4. Create media processing and upload system
  - Implement MediaService with image upload, validation, and processing methods
  - Add EXIF stripping, orientation normalization, and hashed filename generation
  - Create image resizing pipeline (1920px hero, 1280px general, 768px cards) with WebP generation
  - Implement video upload handling with YouTube/Vimeo URL validation and thumbnail generation (with FFmpeg fallback or static poster)
  - Add aspect ratio validation (16:9 ±10%) for hero/slider images with max dimensions (4096×4096)
  - Create storage symlink with file copy fallback mechanism
  - Write comprehensive tests for media processing, validation, and error handling
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7_

- [x] 5. Build caching and performance optimization system
  - Implement CacheService with specific cache keys and invalidation mapping:
    - home:v1 → invalidated on media(home_slider)/clients/settings change
    - divisions:index → divisions/products/tech/machines/media change
    - division:{slug} → that division or its children/media change
  - Create automatic cache invalidation on content create/update/delete operations
  - Add sitemap regeneration triggers linked to cache invalidation
  - Implement aspect-ratio CSS classes and lazy loading for images with srcset support
  - Add pre-deploy Lighthouse run (manual or scripted) with performance gates: cut ticket if LCP ≥ 2.5s or CLS ≥ 0.1
  - Write tests for caching behavior, invalidation, and performance optimizations
  - _Requirements: 11.3, 11.4, 11.5, 11.6, 11.7, 15.3_

- [x] 6. Implement SEO and structured data system
  - Create SEOService with meta tag generation (title 50-60 chars, description 120-160 chars)
  - Implement Open Graph tag generation with entity-specific images and company logo fallback
  - Add JSON-LD structured data generation (Organization on Contact, LocalBusiness when address present)
  - Create automatic sitemap.xml generation including all public routes and division details
  - Implement staging protection with X-Robots-Tag: noindex when APP_ENV != production
  - Add canonical URL generation and clean slug validation
  - Write tests for SEO tag generation, structured data, and sitemap functionality
  - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7, 10.8_

- [x] 7. Create admin dashboard and navigation
  - Build AdminDashboardController with content counts and recent contact messages
  - Implement admin navigation with role-based menu visibility
  - Create responsive admin layout with TailwindCSS styling
  - Add breadcrumb navigation and page titles
  - Implement "Skip to content" link and proper accessibility attributes (lang="id")
  - Write tests for dashboard functionality and navigation accessibility
  - _Requirements: 6.2, 9.1, 9.2_

- [x] 8. Implement settings management system
  - Create SettingsController with form display and update functionality
  - Build settings form with company profile, logos, contact info, social links, and map embed
  - Add Visi & Misi rich text editing with HTML sanitization
  - Implement home hero content management (headline, subheadline)
  - Create settings validation and error handling
  - Add JSON import/export functionality for quick brand migration with acceptance test
  - Write tests for settings CRUD operations and validation
  - _Requirements: 6.2, 6.3, 2.1, 2.2, 1.1_

- [x] 9. Build user and role management system (Admin only)
  - Create UserController with full CRUD operations restricted to Admin role
  - Implement user creation form with role assignment (Admin/Sales)
  - Add user editing with password change functionality
  - Create user deletion with confirmation dialogs
  - Implement user listing with search and pagination (12 per page)
  - Add test for "Sales cannot access Settings/Users" and login throttle lockout message
  - Write tests for user management operations and Admin-only access restrictions
  - _Requirements: 6.3, 7.2, 14.2, 14.3, 15.4_

- [x] 10. Implement division management with nested content
  - Create DivisionController for admin with CRUD operations and drag-and-drop ordering
  - Build division forms with slug generation, hero image upload, and description editing
  - Implement nested Product, Technology, Machine controllers with division relationships
  - Add search and filter functionality by division and content type
  - Create content ordering with drag-and-drop interface via AJAX that persists after refresh
  - Implement cascade delete confirmation dialogs with clear warnings
  - Write tests for division management, nested content, cascade deletion, and drag-and-drop persistence
  - _Requirements: 6.4, 7.3, 14.1, 14.4, 14.5, 15.4_

- [x] 11. Build media management system
  - Create MediaController with polymorphic relationship handling
  - Implement media upload interface with progress indicators and validation feedback
  - Add media gallery with filtering by type (image/video) and entity
  - Create media editing with caption, flags (home_slider, featured), and ordering
  - Implement drag-and-drop reordering for media items
  - Add media deletion with file cleanup and confirmation dialogs
  - Test memory fail cases (reject >4096×4096), ensure EXIF stripped, and validate hashed path structure /media/{entity}/{YYYY}/{MM}/hash.ext
  - Write tests for media CRUD operations, file handling, and polymorphic relationships
  - _Requirements: 6.4, 7.3, 8.6, 8.7, 14.4, 15.4_

- [x] 12. Create milestone and client management
  - Implement MilestoneController with year validation (1900-2100) and ordering
  - Build milestone forms with year input and rich text description
  - Create ClientController with logo upload and URL management
  - Add client ordering with drag-and-drop interface (max 12 for homepage display)
  - Implement search functionality and pagination for both content types
  - Write tests for milestone/client CRUD operations and ordering functionality
  - _Requirements: 6.4, 7.3, 3.1, 3.2, 1.4, 14.1, 14.4_

- [x] 13. Implement contact message management
  - Create ContactMessageController with list, detail, and status management
  - Build contact message interface with handled status toggle and internal notes
  - Add filtering by handled status and search by name, company, email, IP address
  - Implement contact message retention with admin action "Purge old messages" (24 months) and guarded route for shared hosting without cron
  - Create CSV export functionality (UTF-8 BOM, respects filters) for contact messages
  - Add pagination and sorting for message lists
  - Document cron setup: php artisan schedule:run every minute or daily php artisan app:clean-contact-messages
  - Write tests for message management, filtering, and export functionality
  - _Requirements: 13.1, 13.2, 13.3, 13.4, 7.4, 18.3_

- [x] 14. Create public website controllers and views
  - Implement HomeController with hero content, slider, about snippet, and client showcase (max 12 clients)
  - Add empty state handling for slider (graceful hiding when no images)
  - Create PageController for Visi & Misi with SEO meta tag integration
  - Implement MilestoneController with year-based grouping and chronological ordering
  - Build DivisionController with index grid and detail pages with conditional tabs (hide empty tabs)
  - Add ContactController with company info, map display, and form handling
  - Implement placeholder images and aspect-ratio boxes to prevent CLS
  - Write feature tests for all public pages, content display, and empty state handling
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 3.1, 3.2, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2_

- [x] 15. Build contact form with validation and spam protection
  - Implement contact form validation requiring name + (phone OR email) + message
  - Add honeypot field and rate limiting (3 submissions per minute per IP)
  - Create IP address and user agent capture with database storage
  - Implement form error handling with focus management and aria-describedby associations
  - Add success toast notification and form reset after submission
  - Create Google Maps fallback with static image and "Open in Google Maps" link
  - Write comprehensive tests for form validation, spam protection, and accessibility
  - _Requirements: 5.3, 5.4, 5.5, 5.6, 5.7, 9.5, 16.6_

- [x] 16. Create error pages and operational features
  - Build custom 404 error page with navigation back to main sections
  - Create custom 500 error page with contact information
  - Implement proper robots.txt serving with 200 status
  - Add security headers: X-Frame-Options=DENY, X-Content-Type-Options=nosniff, Referrer-Policy=strict-origin-when-cross-origin
  - Add error logging with daily files and LOG_LEVEL=warning in production
  - Create optional Sentry integration for error tracking
  - Implement health check route returning 200 with build info (commit/date)
  - Write tests for error page functionality and logging behavior
  - _Requirements: 16.1, 16.2, 16.3, 18.4_

- [x] 17. Optimize for shared hosting deployment
  - Configure Vite build process for production with asset optimization
  - Create build and package script (bash or Composer): composer install --no-dev, npm ci && npm run build, php artisan optimize, zip including vendor/ and public/build/
  - Implement storage symlink with automatic fallback to file copying
  - Add database migration and seeding for initial setup
  - Ensure Node.js-free operation on server with public/build/manifest.json verification
  - Acceptance: zip unpacks and runs on cPanel without Node.js
  - Write deployment tests and shared hosting compatibility checks
  - _Requirements: 16.4, 16.5, 11.6_

- [ ] 18. Create responsive frontend with TailwindCSS
  - Build responsive layouts for all public pages with breakpoints (≥320px mobile, ≥768px tablet, ≥1024px desktop)
  - Implement image slider with autoplay, pause on hover, swipe support, and keyboard navigation (Left/Right, Space for pause/play)
  - Create card layouts with content truncation (2 lines with ellipsis) and hover effects
  - Add loading states, skeleton screens, and smooth transitions
  - Implement proper focus management and visible focus states for accessibility
  - Optimize CSS with Tailwind purging and Vite bundling
  - Write tests for responsive behavior and interactive elements
  - _Requirements: 1.2, 1.3, 4.1, 9.2, 9.3, 11.6, 17.2, 17.3_

- [ ] 19. Add accessibility and keyboard navigation
  - Implement comprehensive keyboard navigation for all interactive elements
  - Add ARIA labels, descriptions, and proper semantic HTML structure
  - Create skip links and focus management for form validation errors
  - Ensure color contrast meets AA standards across all components
  - Add alt text prompts and validation for all uploaded images
  - Implement screen reader friendly content and navigation
  - Perform accessibility manual check (NVDA/VoiceOver quick pass)
  - Write accessibility tests and manual testing with screen readers
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 20. Add final polish and browser compatibility
  - Test and fix compatibility across last 2 versions of Chrome, Edge, Safari, iOS Safari, Android Chrome
  - Implement progressive enhancement for JavaScript functionality
  - Add performance monitoring and optimization for TTFB < 600ms and payload < 1.5MB
  - Create comprehensive browser testing suite
  - Add final UI polish, animations, and micro-interactions
  - Perform final accessibility audit and fixes
  - Create optional documentation: route table (route → controller → middleware), ERD (Mermaid export), and tiny admin user guide (1-2 pages)
  - Write end-to-end tests covering complete user workflows
  - _Requirements: 11.1, 11.2, 17.1, 17.3_