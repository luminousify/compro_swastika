# Requirements Document

## Introduction

This document outlines the requirements for developing a company profile website for PT. Daya Swastika Perkasa (DSP). The website will serve as a digital showcase of the company's divisions, milestones, clients, and contact information, with a comprehensive back-office system for content management. The solution will be built using Laravel 11.45.1 with PHP 8.4, designed for deployment on shared hosting environments, and will support Indonesian language content exclusively for version 1.

## Requirements

### Requirement 1

**User Story:** As a website visitor, I want to view the company's home page with hero content, image slider, about section, and client showcase, so that I can quickly understand what DSP offers and see their credibility through client logos.

#### Acceptance Criteria

1. WHEN a visitor accesses the home page THEN the system SHALL display a hero section with headline, subheadline, and CTA button to contact page
2. WHEN the home page loads AND slider images exist THEN the system SHALL display an image slider with up to 10 images from media flagged as "home_slider" with autoplay, pause on hover, swipe on touch, and left/right keyboard navigation support
3. WHEN the home page loads AND no slider images exist THEN the system SHALL hide the slider section gracefully without empty space
4. WHEN the home page renders THEN the system SHALL show an about snippet with short text and link to Visi & Misi page
5. WHEN the home page loads THEN the system SHALL display a maximum of 12 client logos ordered by sort_order field from available clients

### Requirement 2

**User Story:** As a website visitor, I want to view the company's vision and mission, so that I can understand DSP's core values and business philosophy.

#### Acceptance Criteria

1. WHEN a visitor accesses the /visi-misi page THEN the system SHALL display the vision text from settings
2. WHEN a visitor accesses the /visi-misi page THEN the system SHALL display the mission text from settings
3. WHEN the visi-misi page loads THEN the system SHALL include proper SEO meta tags (title, description, OG tags)

### Requirement 3

**User Story:** As a website visitor, I want to view the company's milestones in chronological order, so that I can understand DSP's history and growth over time.

#### Acceptance Criteria

1. WHEN a visitor accesses the /milestones page THEN the system SHALL display milestones grouped by year in ascending order (oldest to newest)
2. WHEN milestones are displayed THEN each milestone SHALL show the year and descriptive text
3. WHEN new milestones are added by admin/sales THEN they SHALL appear immediately on the public page

### Requirement 4

**User Story:** As a website visitor, I want to browse DSP's line of business divisions and their details, so that I can understand the company's capabilities and services in each division.

#### Acceptance Criteria

1. WHEN a visitor accesses the /line-of-business page THEN the system SHALL display all divisions in a card grid format showing image, name, and short description truncated to 2 lines with ellipsis
2. WHEN a visitor clicks on a division THEN the system SHALL display the division detail page with hero image, full description, and tabbed sections
3. WHEN viewing a division detail THEN the system SHALL show only tabs/sections that contain data for Products, Technologies, Machines, and Videos related to that division
4. WHEN viewing a division detail THEN the system SHALL display a gallery of images associated with that division

### Requirement 5

**User Story:** As a website visitor, I want to contact DSP through a contact form and view their location information, so that I can reach out for business inquiries or find their office.

#### Acceptance Criteria

1. WHEN a visitor accesses the /contact page THEN the system SHALL display company address information from settings
2. WHEN the contact page loads THEN the system SHALL show an embedded Google Maps with location from settings (lat/long or iframe)
3. WHEN a visitor submits the contact form THEN the system SHALL validate that name, message, and either phone or email are provided with clear validation messages
4. WHEN a visitor submits the contact form THEN the system SHALL store the message in the database with fields: name, company (optional), phone/WhatsApp, email (optional), message, IP address, and user agent
5. WHEN a contact form is submitted successfully THEN the system SHALL display a success toast message
6. WHEN a contact form is submitted THEN the system SHALL apply honeypot spam protection and rate limiting (3 submissions per minute per IP)
7. WHEN form validation fails THEN the system SHALL move focus to the first invalid field and associate error text via aria-describedby

### Requirement 6

**User Story:** As an Admin user, I want full access to all system settings and content management features, so that I can configure the website and manage all content types.

#### Acceptance Criteria

1. WHEN an Admin logs in THEN the system SHALL provide access to Settings, Users & Roles, and all content modules
2. WHEN an Admin accesses Settings THEN the system SHALL allow editing of company profile, logos, address, contact info, social links, map embed, Visi & Misi text, and home hero content
3. WHEN an Admin manages Users & Roles THEN the system SHALL allow creating, reading, updating, and deleting users with role assignment (Admin/Sales)
4. WHEN an Admin performs any CRUD operation THEN the system SHALL apply the changes immediately without approval workflow

### Requirement 7

**User Story:** As a Sales user, I want to manage content related to divisions, products, technologies, machines, media, milestones, clients, and contact messages, so that I can keep the website content current and respond to inquiries.

#### Acceptance Criteria

1. WHEN a Sales user logs in THEN the system SHALL provide access to Divisions, Products, Technologies, Machines, Media, Milestones, Clients, and Contact Messages modules
2. WHEN a Sales user accesses content modules THEN the system SHALL allow full CRUD operations on Divisions, Products, Technologies, Machines, Media, Milestones, and Clients
3. WHEN a Sales user accesses Contact Messages THEN the system SHALL allow reading messages, marking as handled, and adding internal notes
4. WHEN a Sales user attempts to access Settings or Users & Roles THEN the system SHALL deny access

### Requirement 8

**User Story:** As an Admin or Sales user, I want to upload and manage media files with automatic optimization, so that I can maintain high-quality visuals while ensuring good website performance.

#### Acceptance Criteria

1. WHEN uploading images THEN the system SHALL validate file size (max 5MB), type (jpg/jpeg/png/webp), minimum width (1200px for hero/slider images), and aspect ratio (16:9 ±10% for hero/slider)
2. WHEN image validation fails THEN the system SHALL reject the upload with clear error messages specifying the requirements
3. WHEN an image is uploaded THEN the system SHALL automatically resize to 1920px (hero), 1280px (general), and 768px (cards) variants with srcset support and generate WebP format when possible
4. WHEN images are uploaded THEN the system SHALL strip EXIF data, normalize orientation, enforce max dimensions (4096×4096), and store with hashed filenames while keeping original filename for display
5. WHEN uploading videos THEN the system SHALL accept mp4 files (max 50MB) or YouTube/Vimeo embed URLs only with domain whitelist validation and generate video poster thumbnails
6. WHEN media is uploaded THEN the system SHALL store files in storage/app/public with proper organization and serve via public/storage link with fallback file copy if symlink fails
7. WHEN managing media THEN the system SHALL support polymorphic relationships to any entity with caption, order, and flags (home_slider, featured)

### Requirement 9

**User Story:** As a website visitor using assistive technology, I want the website to be accessible and keyboard navigable, so that I can access all content and functionality regardless of my abilities.

#### Acceptance Criteria

1. WHEN any page loads THEN the system SHALL include a "Skip to content" link and set lang="id" on the HTML element
2. WHEN navigating with keyboard THEN the system SHALL provide visible focus states on all interactive elements including slider controls (Left/Right, Pause/Play)
3. WHEN images are displayed THEN the system SHALL include alt text with UI prompts for content managers to add descriptions
4. WHEN viewing any page THEN the system SHALL maintain color contrast ratios meeting AA standards
5. WHEN form errors occur THEN the system SHALL display clear, inline error messages with focus moved to first invalid field and aria-describedby associations

### Requirement 10

**User Story:** As a search engine crawler, I want properly structured SEO metadata and sitemaps, so that I can effectively index and rank the website content.

#### Acceptance Criteria

1. WHEN any page loads THEN the system SHALL include unique title (50-60 characters) and meta description (120-160 characters) tags
2. WHEN pages are accessed THEN the system SHALL provide Open Graph tags (title, description, image) with fallback to company logo if page lacks specific image
3. WHEN division pages are accessed THEN the system SHALL use division hero image for Open Graph image when available
4. WHEN pages are accessed THEN the system SHALL inject JSON-LD Organization schema on Contact page and LocalBusiness schema when address is present
5. WHEN the website is crawled THEN the system SHALL serve an automatically generated sitemap.xml including /, /visi-misi, /milestones, /line-of-business, each division detail, and /contact
6. WHEN robots.txt is requested THEN the system SHALL serve a file allowing all crawlers with 200 status
7. WHEN pages are accessed THEN the system SHALL use canonical URLs and clean slugs with unique, lowercase, hyphen-separated format
8. WHEN APP_ENV is not production THEN the system SHALL add X-Robots-Tag: noindex to prevent staging indexing

### Requirement 11

**User Story:** As a website visitor on any device, I want fast page loading and responsive design, so that I can access the website efficiently regardless of my device or connection speed.

#### Acceptance Criteria

1. WHEN any page loads THEN the system SHALL achieve TTFB (Time To First Byte) under 600ms on the hosting environment
2. WHEN the largest page loads THEN the system SHALL keep payload under 1.5MB on first paint
3. WHEN images are displayed THEN the system SHALL implement lazy loading and responsive srcset attributes
4. WHEN hero and slider images load THEN the system SHALL use aspect-ratio boxes to prevent Cumulative Layout Shift (CLS)
5. WHEN public content is requested THEN the system SHALL cache database queries for 10-60 minutes
6. WHEN CSS and JS assets load THEN the system SHALL serve optimized, purged Tailwind CSS and bundled JavaScript via Vite
7. WHEN performance is measured THEN the system SHALL target LCP < 2.5s and CLS < 0.1

### Requirement 12

**User Story:** As a system administrator, I want the website to be secure against common web vulnerabilities, so that I can protect user data and maintain system integrity.

#### Acceptance Criteria

1. WHEN forms are submitted THEN the system SHALL validate CSRF tokens
2. WHEN user input is processed THEN the system SHALL sanitize and validate all input data
3. WHEN file uploads occur THEN the system SHALL validate MIME types and file sizes, storing files outside webroot
4. WHEN role-based actions are performed THEN the system SHALL enforce Laravel policies for permission checks
5. WHEN users log in THEN the system SHALL apply login throttling (5 attempts per minute) and enforce password policy (minimum 8 characters, deny known weak passwords)
6. WHEN sessions are created THEN the system SHALL set HttpOnly, Secure, SameSite=Lax flags with 60-minute idle timeout
7. WHEN contact forms are submitted THEN the system SHALL apply rate limiting and honeypot protection against spam

### Requirement 13

**User Story:** As an Admin or Sales user, I want to manage contact messages with proper organization and tracking, so that I can efficiently handle customer inquiries and maintain communication records.

#### Acceptance Criteria

1. WHEN viewing contact messages THEN the system SHALL provide list, detail view, and ability to mark as handled with boolean status
2. WHEN managing contact messages THEN the system SHALL allow adding internal notes and filtering by handled status
3. WHEN contact messages are stored THEN the system SHALL capture and make searchable IP address and user agent information
4. WHEN Sales users access contact messages THEN the system SHALL allow reading, updating handled status, and adding notes but not deleting

### Requirement 14

**User Story:** As an Admin or Sales user, I want all content lists to have consistent ordering and pagination, so that I can efficiently navigate and manage large amounts of content.

#### Acceptance Criteria

1. WHEN viewing any content list THEN the system SHALL apply default sorting: order ASC for most content, year ASC then order ASC for milestones
2. WHEN content lists exceed 12 items THEN the system SHALL paginate with 12 items per page
3. WHEN using pagination THEN the system SHALL retain any applied filters or search terms
4. WHEN managing content order THEN the system SHALL provide drag-and-drop reordering for sliders, clients, and milestones
5. WHEN viewing admin lists THEN the system SHALL provide search and filter functionality by name and division where applicable

### Requirement 15

**User Story:** As an Admin or Sales user, I want content changes to be tracked and cached appropriately, so that I can see who made changes and ensure optimal website performance.

#### Acceptance Criteria

1. WHEN any content is created, updated, or deleted THEN the system SHALL track updated_by field linking to the user
2. WHEN public content is requested THEN the system SHALL cache queries for 10-60 minutes based on content type
3. WHEN content is created, updated, or deleted THEN the system SHALL automatically clear related caches and regenerate sitemap
4. WHEN deleting any content THEN the system SHALL show confirmation dialog before permanent deletion with cascade delete rules (division → products/technologies/machines/media, client → media, media → file removal)
5. WHEN database tables are created THEN the system SHALL use utf8mb4 charset with utf8mb4_unicode_ci collation and proper indexes on (mediable_type, mediable_id), is_home_slider, order, (year, order)

### Requirement 16

**User Story:** As a website visitor encountering errors, I want helpful error pages and proper asset delivery, so that I can understand what happened and potentially find what I'm looking for.

#### Acceptance Criteria

1. WHEN a 404 error occurs THEN the system SHALL display a custom error page with navigation back to main sections
2. WHEN a 500 error occurs THEN the system SHALL display a generic error page with contact information
3. WHEN robots.txt is requested THEN the system SHALL serve the file with 200 status
4. WHEN the site is deployed THEN the system SHALL run without Node.js on server with public/build/manifest.json present
5. WHEN storage symlink fails THEN the system SHALL use file copy fallback ensuring images are visible on public pages
6. WHEN Google Maps iframe is blocked THEN the system SHALL show static map image with "Open in Google Maps" link fallback

### Requirement 17

**User Story:** As a website visitor using various devices and browsers, I want consistent functionality and responsive design, so that I can access the website regardless of my device or browser choice.

#### Acceptance Criteria

1. WHEN accessing the website THEN the system SHALL support last 2 versions of Chrome, Edge, Safari, iOS Safari, and Android Chrome
2. WHEN viewing on different screen sizes THEN the system SHALL provide responsive breakpoints: ≥320px (mobile), ≥768px (tablet), ≥1024px (desktop)
3. WHEN the website loads THEN the system SHALL maintain functionality across all supported browsers and devices

### Requirement 18

**User Story:** As a system administrator, I want proper database configuration and operational features, so that I can maintain data integrity and provide administrative capabilities.

#### Acceptance Criteria

1. WHEN the application starts THEN the system SHALL use APP_TIMEZONE=Asia/Jakarta and display dates in DD MMM YYYY format
2. WHEN database tables are created THEN the system SHALL enforce unique constraints on division.slug and optionally clients.name
3. WHEN contact messages are stored THEN the system SHALL provide retention policy (24 months) and CSV export capability from admin
4. WHEN errors occur THEN the system SHALL log to daily files with LOG_LEVEL=warning in production and optional Sentry DSN integration
5. WHEN admin users need password recovery THEN the system SHALL provide "forgot password" flow via email