# Product Requirements Document (PRD)
**Project:** Company Profile Website — PT. Daya Swastika Perkasa (DSP)  
**Version:** v1.0  
**Date:** 09 Aug 2025  
**Owner:** Product (you)  
**Tech Stack:** Laravel 11.45.1 (PHP 8.4), MariaDB 11.4, Blade, TailwindCSS (built locally via Vite), Alpine.js (optional), no CDN.  
**Hosting:** Shared hosting (cPanel, PHP 8.4). Deployed as a zipped build (includes `vendor/` and `public/build`).  
**Language:** Indonesian (ID) only for v1.

---

## 1) Summary & Goals
**Goal:** Deliver a fast, maintainable company profile website that showcases DSP’s divisions (line of business), milestones, clients, and contact info with an easy back‑office for Admin and Sales.

**Success Metrics (v1):**
- Publishable site within agreed timeline on shared hosting.
- TTFB < 600ms on host; Largest page payload < 1.5MB on first paint.
- Admin/Sales can create/update content without code changes.

**Non‑Goals (v1):** SMTP/email delivery, CRM/leads, content versioning, soft deletes, multilingual, blog.

---

## 2) Scope
**In Scope:**
- Public pages: Home, Visi & Misi, Milestones, Line of Business (Divisions), Contact.
- Admin panel modules: Settings, Divisions, Products, Technologies, Machines, Media (Images/Videos), Milestones, Clients, Contact Messages, Users & Roles.
- Media storage on local disk; image optimization on upload; WebP generation when possible.
- Basic SEO (titles, descriptions, OG tags), sitemap.xml, robots.txt.
- Minimal analytics: none for v1 (can add later). Cookie banner skipped.

**Out of Scope (v1):** Email notifications, certificates/downloads, dealer locator, blog/news, backups automation (manual backup only), external search, approval workflow.

---

## 3) Users & Roles
- **Admin**: Full access (site settings, users & roles, all content, menus, SEO).
- **Sales**: Can view all items. Can **CRUD**: Divisions, Products, Technologies, Machines, Media, Milestones, Clients, Contact Messages. Cannot access Settings, Users/Roles, Menus/SEO.

> Sales publishes directly (no approval). No soft delete.

---

## 4) Information Architecture (Public)
- **/** Home — Hero, image slider (horizontal), About snippet, client list.
- **/visi-misi** — Visi & Misi (static copy from Settings).
- **/milestones** — Timeline sorted **oldest → newest**.
- **/line-of-business** — Grid of divisions; each division detail shows description, images, **Products**, **Technologies**, **Machines**, **Videos**.
- **/contact** — Address, Google Map embed, contact form (stores to DB; readable by Admin/Sales in back‑office).

Footer: company info, NPWP/NIB (optional), socials.  
Header: logo, primary nav, CTA to Contact.

---

## 5) Functional Requirements
### 5.1 Home
- Hero: headline, subheadline, CTA to Contact.
- Image slider: up to 10 images from Media (flagged as "home_slider"), swipe/keyboard accessible.
- About snippet: short text + link to About/Visi & Misi.
- Client list: 12 logos max, ordered by sort_order.

### 5.2 Visi & Misi
- Rich text fields managed in Settings. Display as static page.

### 5.3 Milestones
- List grouped by year ascending. Each item: **year (SMALLINT)** and **text**.

### 5.4 Line of Business
- Divisions index: card grid (image, name, short description).
- Division detail: hero image, description, tabs/sections for **Products**, **Technologies**, **Machines**, **Videos**; gallery of images.

### 5.5 Contact
- Company address (from Settings) + Google Maps embed (lat/long or iframe stored in Settings).
- Contact form fields: name, company (optional), phone/WhatsApp, email (optional), message. On submit: store in DB, show success toast.
- No email sending for v1.

### 5.6 Admin Panel
- **Dashboard:** quick counts (divisions, milestones, media, messages) and latest 5 contact messages.
- **Settings:** company profile, logo(s), address, phone, WhatsApp link, emails (display only), socials, map embed/coords, Visi & Misi text, Home hero copy.
- **Divisions:** CRUD. Fields: slug, name, description, hero_image, order.
- **Products/Technologies/Machines:** CRUD under a division (1‑many). Fields: name, description, order.
- **Media:** Polymorphic to any entity; image/video; caption; order; flags (home_slider, featured). Upload with automatic resizing and WebP.
- **Milestones:** CRUD with year (unique per row not required) + text; order.
- **Clients:** CRUD with name, logo, url (optional), order.
- **Contact Messages:** list/detail, mark as handled (boolean), internal note.
- **Users & Roles:** CRUD users; assign Admin/Sales.

---

## 6) Data Model (tables & key fields)
**users**: id, name, email (unique), password (hash), role (enum: admin|sales), remember_token, timestamps.

**settings**: id=1, data (JSON) — keys: company_name, logo_path(s), address, phone, whatsapp_link, emails[], socials{}, map_embed, visi, misi, home_headline, home_subheadline.

**divisions**: id, slug (unique), name, description (text), hero_image_path, order, timestamps.

**products**: id, division_id (FK), name, description (text), order, timestamps.

**technologies**: id, division_id (FK), name, description (text), order, timestamps.

**machines**: id, division_id (FK), name, description (text), order, timestamps.

**media**: id, mediable_type, mediable_id, type (enum: image|video), path_or_embed, caption, is_home_slider (bool), is_featured (bool), width, height, bytes, order, uploaded_by (FK users.id), timestamps.

**milestones**: id, year (SMALLINT, >= 1900), text, order, timestamps.

**clients**: id, name, logo_path, url (nullable), order, timestamps.

**contact_messages**: id, name, company (nullable), phone, email (nullable), message, handled (bool), note (nullable), created_by_ip, user_agent, created_at.

Indexes: slugs, FKs, (mediable_type, mediable_id), (year, id), (order).

---

## 7) Permissions Matrix (v1)
| Module | Admin | Sales |
|---|---|---|
| Settings | C/R/U | – |
| Users & Roles | C/R/U/D | – |
| Divisions | C/R/U/D | C/R/U/D |
| Products/Technologies/Machines | C/R/U/D | C/R/U/D |
| Media | C/R/U/D | C/R/U/D |
| Milestones | C/R/U/D | C/R/U/D |
| Clients | C/R/U/D | C/R/U/D |
| Contact Messages | C/R/U/D | R/U (mark handled, add note) |

> Sales can see all items. Direct publish; deletes are hard deletes (no recycle bin).

---

## 8) Content Rules & Validation
- **Images**: max 5MB; allowed: jpg/jpeg/png/webp; min width 1200px for hero/slider; aspect guards (hero 16:9 ±10%). Auto‑resize to 1920/1280/768; generate WebP if Imagick/GD supports. Store in `storage/app/public` with `public/storage` link.
- **Videos**: store as file (mp4) **or** YouTube/Vimeo embed URL (validate). Max 50MB if uploaded.
- **Text**: basic HTML sanitization; strip scripts/iframes except whitelisted map/video fields.
- **Contact form**: honeypot field + simple rate limit (e.g., 3/min per IP). Required: name, phone or email, message. No SMTP.

---

## 9) UX & Accessibility
- Keyboard navigable menu & slider; visible focus states.
- Alt text required on images with UI prompts.
- Color contrast AA.
- Error messages inline & clear.

---

## 10) SEO
- Per page `<title>` and `<meta description>`; Open Graph (title, description, image).
- Canonical URLs; clean slugs.
- `sitemap.xml` autogen; `robots.txt` allow all.

---

## 11) Performance
- Build Tailwind with purge; bundle JS via Vite. No runtime Node on server.
- Cache public queries (e.g., divisions, milestones, clients) for 10–60 minutes.
- Lazy‑load images; use `srcset` for responsive sizes.

---

## 12) Security
- CSRF on forms, input validation, Laravel policies for role checks.
- File uploads: validate mime & size; store outside webroot then serve via `public/storage`.
- Contact form rate limiting + honeypot.

---

## 13) Deployment & Hosting
**Local:** `composer install`, `npm ci`, `npm run build`, optimize caches, zip entire project including `vendor/` and `public/build/`.

**Server (cPanel):** point document root to `/public`; upload & extract; create DB; set `.env` (APP_KEY, APP_URL, DB creds); ensure `storage/` & `bootstrap/cache` writable; run `php artisan storage:link` (or copy `storage/app/public` to `public/storage` if symlink denied). No Node needed on server.

---

## 14) Acceptance Criteria (high‑level)
**Home**
- [ ] Hero renders texts from Settings and displays slider (≥1 image).
- [ ] About snippet and clients load from DB; clients max 12 visible.

**Visi & Misi**
- [ ] Page displays Visi and Misi from Settings; SEO tags present.

**Milestones**
- [ ] Items sorted ascending by year. Creating an item shows immediately on page.

**Line of Business**
- [ ] Index shows all divisions in grid; each detail has description + tabs (Products, Technologies, Machines, Videos) and gallery.

**Contact**
- [ ] Form stores messages to DB; success message displayed; no email sent; spam blocked by honeypot.

**Admin Panel**
- [ ] Sales can CRUD Divisions, Products, Technologies, Machines, Media, Milestones, Clients; cannot access Settings or Users.
- [ ] Admin can modify Settings and Users; logo and map are visible on public pages after save.

**General**
- [ ] All images meet size/type validation; WebP generated when possible.
- [ ] Public pages have titles/descriptions; sitemap.xml accessible.
- [ ] Site loads without Node on server and under PHP 8.4.

---

## 15) Risks & Mitigations
- **Hard deletes** may remove content permanently → confirm dialogs + periodic manual backup.
- **Direct publish** may break layout with wrong image ratio → strict upload validation + preview.
- **Shared hosting limits** (symlink, memory) → fallback to copying `public/storage`; reduce image dimensions.

---

## 16) Handover Deliverables
- Source code repo with README (deploy steps), `.env.example`.
- SQL migration(s) and minimal seeders (Settings, one Division, dummy images, 2 Clients).
- Brand‑neutral Tailwind theme; favicon & social images.
- Admin guide (1–2 pages): how to add divisions, images, milestones, and read contact messages.

