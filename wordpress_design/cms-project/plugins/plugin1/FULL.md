# CMS ADMIN - Complete Project Documentation

---

## 1. Project Overview

**CMS ADMIN** is a custom WordPress plugin that provides a full-featured Content Management System (CMS) for building and managing business websites. Instead of relying on WordPress's native post types and the block editor, it creates its own database tables, admin interface, models, and front-end rendering system. The plugin is designed to power a professional services or business website - with sections for home page management, services, about us, team, reviews, blog/news posts, FAQs, events, contact forms, and more - all controlled from a unified custom admin dashboard inside WordPress.

### Who is it for?

The plugin was originally built for a business called "Advith Homes" / "The Cane House" but is designed to be reusable for any service-oriented business. A site administrator manages every piece of content (text, images, sections, navigation, footer) from the CMS ADMIN panel without touching code.

### Key Characteristics

- **Self-contained data layer**: 60+ custom database tables (prefixed `ah_`) hold all content, independent of WordPress's `wp_posts` / `wp_postmeta`.
- **Dual-mode operation**: Works as a standalone WordPress theme or as a plugin paired with any front-end theme.
- **Full admin portal**: Custom admin pages for every content area, accessed under a single "CMS ADMIN" sidebar menu.
- **Automation engine**: A built-in "Triggers Maker" (rules engine) automates actions like sending emails or calling webhooks when events occur (form submissions, bookings, etc.).
- **Dynamic form builder**: Create forms via the admin UI, embed them via shortcodes, and view submissions - no code needed.
- **Page builder**: A block-based page builder for creating custom landing pages stored in a dedicated table and served via slug routing.
- **CSV data import**: Bulk import services, reviews, FAQs, posts, team members, taxonomies, events, and news bar items from CSV files.

---

## 2. Architecture

### High-Level Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        WordPress Core                           │
├──────────────────────────┬──────────────────────────────────────┤
│   CMS ADMIN Plugin       │     Front-End Theme (optional)       │
│   (ah-cms.php)           │     (functions.php)                  │
│                          │                                      │
│  ┌────────────────────┐  │  ┌────────────────────────────────┐  │
│  │  Admin Bootstrap   │  │  │  Asset Loader                  │  │
│  │  ┌──────────────┐  │  │  │  (CSS + JS for public pages)   │  │
│  │  │ Admin Menus  │  │  │  └────────────────────────────────┘  │
│  │  │ Admin Pages  │  │  │                                      │
│  │  │ AJAX Handler │  │  │  ┌────────────────────────────────┐  │
│  │  └──────────────┘  │  │  │  Template Builder Renderer     │  │
│  └────────────────────┘  │  │  (Page Builder front-end)       │  │
│                          │  └────────────────────────────────┘  │
│  ┌────────────────────┐  │                                      │
│  │  Models Layer      │  │                                      │
│  │  (Model Base +     │  │                                      │
│  │   15 domain models)│  │                                      │
│  └─────────┬──────────┘  │                                      │
│            │             │                                      │
│  ┌─────────▼──────────┐  │                                      │
│  │  Database Layer     │  │                                      │
│  │  DB Helper          │  │                                      │
│  │  DB Installer       │  │                                      │
│  │  (60+ ah_ tables)   │  │                                      │
│  └────────────────────┘  │                                      │
│                          │                                      │
│  ┌────────────────────┐  │                                      │
│  │  Form Builder      │  │                                      │
│  │  Rules Engine       │  │                                      │
│  │  CSV Importer       │  │                                      │
│  │  Helpers            │  │                                      │
│  └────────────────────┘  │                                      │
├──────────────────────────┴──────────────────────────────────────┤
│               MySQL Database (wp_ + ah_ tables)                 │
└─────────────────────────────────────────────────────────────────┘
```

### Design Pattern: MVC-ish

The plugin loosely follows a Model–View–Controller pattern:

- **Models** (`models/` directory): Each domain entity (Services, Reviews, Posts, FAQs, Team, etc.) has a model class extending `AH_Model_Base`. Models encapsulate CRUD operations, pagination, search, and audit logging.
- **Views** (`admin/pages/` directory): PHP template files that render the admin UI for each section. Each admin page has its own file (e.g., `services.php`, `reviews.php`, `posts.php`).
- **Controllers** (`admin/class-admin-bootstrap.php` + `admin/ajax/class-ajax-handlers.php`): The bootstrap class wires admin menus, handles form POST submissions (navigation, banners, notices), and the AJAX handler class processes all asynchronous requests (toggle status, delete, sort, upload, etc.).

---

## 3. File and Folder Structure

```
plugin1/
├── ah-cms.php                    ← Main plugin bootstrap file
├── cms-project-plugin.php        ← SEO helper + sitemap generator (secondary plugin)
├── functions.php                 ← Theme functions (dual-mode: plugin or standalone)
├── style.css                     ← Theme stylesheet header
├── constants.php                 ← Constants (currently empty, reserved)
├── index.php                     ← Security: prevents directory listing
│
├── inc/                          ← Core infrastructure classes
│   ├── class-autoloader.php      ← PSR-4-like class autoloader
│   ├── class-asset-loader.php    ← Front-end CSS/JS enqueue
│   ├── class-theme-setup.php     ← WordPress theme support setup
│   ├── class-form-builder.php    ← Dynamic form builder (CRUD + shortcode renderer)
│   └── class-rules-engine.php    ← Triggers Maker automation engine
│
├── database/
│   ├── class-db-installer.php    ← Table creation, migrations, seeds (60+ tables)
│   ├── class-db-helper.php       ← Generic CRUD, pagination, search, audit log
│   ├── schema.sql                ← Reference SQL schema
│   └── schema/                   ← Schema fragments directory
│
├── models/                       ← Domain model classes
│   ├── class-model-base.php      ← Abstract base with CRUD, pagination, search
│   ├── class-home-model.php      ← Home page sections (hero, highlights, etc.)
│   ├── class-services-model.php  ← Services management
│   ├── class-about-model.php     ← About page sections
│   ├── class-reviews-model.php   ← Customer reviews
│   ├── class-posts-model.php     ← Blog/news/articles/newsletters/guides
│   ├── class-faqs-model.php      ← Frequently asked questions
│   ├── class-team-model.php      ← Team members
│   ├── class-events-model.php    ← Events / hire packages
│   ├── class-pages-model.php     ← CMS pages registry
│   ├── class-nav-model.php       ← Navigation menu data
│   ├── class-newsbar-model.php   ← News bar ticker items
│   ├── class-footer-model.php    ← Footer configuration
│   ├── class-media-model.php     ← Custom media library
│   ├── class-settings-model.php  ← Site settings (key-value store)
│   ├── class-taxonomy-model.php         ← Taxonomy terms
│   ├── class-taxonomy-parent-model.php  ← Taxonomy types
│   ├── class-content-taxonomy-model.php ← Universal object↔taxonomy pivot
│   └── class-audit-model.php     ← Audit log queries
│
├── admin/
│   ├── class-admin-bootstrap.php ← Admin initialization, asset loading, POST handlers
│   ├── menus/
│   │   └── class-admin-menus.php ← WordPress admin menu/submenu registration
│   ├── ajax/
│   │   └── class-ajax-handlers.php ← All AJAX endpoint handlers
│   ├── import/
│   │   └── class-csv-importer.php  ← CSV bulk import logic
│   ├── assets/
│   │   ├── css/admin-style.css   ← Admin panel stylesheet
│   │   └── js/admin-script.js    ← Admin panel JavaScript
│   └── pages/                    ← Admin page templates (27 files)
│       ├── dashboard.php
│       ├── settings.php
│       ├── home-sections.php
│       ├── services.php
│       ├── about.php
│       ├── reviews.php
│       ├── posts.php
│       ├── faqs.php
│       ├── team.php
│       ├── taxonomy.php
│       ├── navigation.php
│       ├── news-bar.php
│       ├── media.php
│       ├── pages.php
│       ├── client-stories.php
│       ├── banners.php
│       ├── notices.php
│       ├── page-builder.php
│       ├── form-builder.php
│       ├── rules-engine.php
│       ├── static-pages.php
│       ├── file-links.php
│       ├── events.php
│       ├── import.php
│       ├── audit-log.php
│       ├── admin-actions.php
│       └── help.php
│
├── helper/
│   ├── class-slug-helper.php     ← Unique slug generation
│   ├── class-pagination-helper.php ← Pagination metadata
│   ├── class-validator.php       ← Input validation + sanitization
│   ├── class-uploader.php        ← File upload to custom media library
│   ├── class-banners-helper.php  ← Home banner management helpers
│   ├── class-notice-helper.php   ← Admin notice flash messages
│   └── common.php                ← Shared utility functions (currently empty)
│
├── components/
│   ├── toaster/index.php         ← Flash notification helper function
│   ├── buttons/                  ← Reusable button components
│   ├── cards/                    ← Reusable card components
│   ├── footers/                  ← Footer components
│   ├── form_fields/              ← Form field components
│   └── headers/                  ← Header components
│
├── templates/
│   └── template-builder-page.php ← Front-end renderer for Page Builder pages
│
├── assets/
│   ├── css/
│   │   ├── variables.css         ← CSS custom properties (design tokens)
│   │   ├── animations.css        ← CSS animations library
│   │   └── main.css              ← Main front-end stylesheet
│   ├── js/
│   │   └── main.js               ← Main front-end JavaScript
│   ├── images/                   ← Static image assets
│   └── mock_data/                ← Sample/test data
│
├── static/                       ← Static HTML pages directory
└── template-static-page.php      ← Static page template
```

---

## 4. End-to-End Execution Flow

### 4.1 Plugin Activation

When the plugin is activated in WordPress:

1. WordPress calls the activation hook registered in `ah-cms.php`.
2. `AH_DB_Installer::install()` runs, which creates all 60+ custom database tables using `CREATE TABLE IF NOT EXISTS` statements.
3. Foreign key constraints are added between related tables.
4. Default seed data is inserted: a `super_admin` role, default taxonomy types (Category, Tag, Subtag), default site settings (site name, colors, social links), default CMS pages (Home, About, Services, Contact, Blog, News, Client Stories), and default navigation menus.
5. The database version is stored in `wp_options` as `ah_cms_db_version`.

### 4.2 Every Page Load

On every WordPress load:

1. The autoloader (`AH_Autoloader`) registers an SPL autoload function that maps 35+ class names to their file paths.
2. If the request is for the admin area (`is_admin()`), `AH_Admin_Bootstrap::init()` fires, which registers all admin menus, enqueues admin CSS/JS, and initializes AJAX handlers.
3. Public AJAX handlers are initialized (for form submissions from the front-end).
4. The `[ah_form]` shortcode is registered.
5. The `ahTheme` JavaScript object is localized (provides `ajaxUrl` and `nonce` to front-end scripts).
6. `AH_DB_Installer::maybe_upgrade()` checks if the stored DB version matches the current plugin version. If not, it re-runs the installation and executes all migration methods (adding columns, creating new tables, seeding new taxonomy types).
7. A WP-Cron job (`ah_rules_cron_process`) is scheduled to run every minute, processing pending trigger actions from the Rules Engine.

### 4.3 Admin Panel Usage Flow

```
Admin logs into WordPress
        │
        ▼
Sees "CMS ADMIN" menu in sidebar
        │
        ▼
Clicks a submenu item (e.g., "Services")
        │
        ▼
AH_Admin_Menus routes to the correct page callback
        │
        ▼
The page template file (admin/pages/services.php) loads
        │
        ▼
Template instantiates the model (AH_Services_Model)
        │
        ▼
Model calls AH_DB_Helper to query the database
        │
        ▼
Data is rendered in the admin template (tables, forms)
        │
        ▼
Admin makes changes (create/edit/delete)
        │
        ▼
Changes go through either:
  • Form POST → Admin Bootstrap handler → Model → DB
  • AJAX call → AH_Ajax_Handlers → Model → DB
        │
        ▼
Audit log entry is created in ah_audit_logs
```

### 4.4 Front-End Page Request Flow

```
Visitor requests a URL (e.g., /some-landing-page)
        │
        ▼
WordPress processes the request normally
        │
        ▼
If it results in a 404:
   └─→ template_redirect hook fires
        │
        ▼
   Plugin checks ah_builder_pages table for a matching slug
        │
        ▼
   If found and status is 'active':
      └─→ Loads template-builder-page.php
           └─→ Decodes the JSON `blocks` column
           └─→ Renders each block in order
           └─→ exit() - WordPress's 404 is suppressed
        │
   If not found:
      └─→ Normal WordPress 404 page is shown
```

### 4.5 Form Submission Flow

```
Visitor sees [ah_form id="3"] on a page
        │
        ▼
AH_Form_Builder::render() outputs HTML form
(includes AJAX URL, nonce, honeypot field)
        │
        ▼
Visitor fills in fields and clicks "Send Message"
        │
        ▼
JavaScript sends POST to admin-ajax.php
   action = "ah_form_submit"
        │
        ▼
AH_Ajax_Handlers::handle_form_submit()
  ├─ Verifies nonce
  ├─ Checks honeypot (spam filter)
  ├─ Validates required fields
  ├─ Saves to ah_form_submissions table
  ├─ Fires AH_Rules_Engine::evaluate('form_submit', $context)
  └─ Returns JSON success/error
        │
        ▼
Rules Engine matches active rules for trigger 'form_submit'
  ├─ Evaluates conditions (field matches, operator checks)
  ├─ Queues matching actions to ah_trigger_logs (status: pending)
  └─ WP-Cron picks them up within 60 seconds
        │
        ▼
Cron processor executes actions:
  ├─ send_email → wp_mail() with optional SMTP
  ├─ whatsapp   → HTTP POST to WhatsApp API
  └─ http_request → generic webhook call
```

---

## 5. Database Structure

The plugin creates 60+ custom tables, all prefixed with `{wp_prefix}ah_`. Below are the major table groups.

### 5.1 Core Infrastructure Tables

| Table | Purpose |
|-------|---------|
| `ah_admin_roles` | Role definitions with JSON permissions (e.g., `super_admin` with `["*"]`) |
| `ah_admin_users` | CMS-specific admin user accounts (separate from WordPress users) |
| `ah_site_settings` | Key-value store for site configuration - grouped by `group_name` (general, contact, social, design, notifications). Supports types: text, textarea, image, color, url, email, phone, toggle, json |
| `ah_media` | Custom media library records (file name, path, URL, MIME type, dimensions) |
| `ah_audit_logs` | Complete audit trail - every create, update, delete is logged with old/new values, user ID, IP address, and user agent |
| `ah_pages` | CMS page registry. Each page has a `page_type` (home, about, services, contact, client_stories, blog_listing, news_listing, custom) plus SEO fields |
| `ah_page_sections` | Controls which sections are visible on each page, with sort ordering |

### 5.2 Taxonomy System

| Table | Purpose |
|-------|---------|
| `ah_taxonomy_types` | Categories of taxonomies (e.g., Category, Tag, Subtag, Review Type, Review Categories, FAQ Tags, DataProtected) |
| `ah_taxonomies` | Individual taxonomy terms, each belonging to a type. Supports hierarchy via `parent_id`, images via `image_id`, emoji icons, protection flags, and SEO meta |
| `ah_taxonomy_parent_terms` | Extended parent term metadata |
| `ah_content_taxonomies` | Universal many-to-many pivot table. Links any object type (service, review, FAQ, post) to any taxonomy via `object_type` + `object_id` + `taxonomy_id` |
| `ah_service_taxonomies` | Legacy pivot: service ↔ taxonomy |
| `ah_post_taxonomies` | Legacy pivot: post ↔ taxonomy |

### 5.3 Home Page Tables

| Table | Purpose |
|-------|---------|
| `ah_home_banners` | Hero banner slides (image, mobile image, title, subtitle, description, CTA button, text alignment/position, overlay color) |
| `ah_section_hero` | Hero section content (badge, heading, subheading, CTAs, background image) |
| `ah_section_highlights` | USP highlight items with icons |
| `ah_section_why_us` + `ah_section_why_us_cards` | "Why Us" section header + feature cards |
| `ah_section_guide_through` + `ah_section_guide_through_points` | Step-by-step guide section |
| `ah_section_stack_items` | Technology/partner stack with icons |
| `ah_section_difference` + `ah_section_difference_table` | Comparison table (us vs. others) |
| `ah_section_featured_properties` + `ah_section_featured_properties_items` | Featured properties/projects |
| `ah_section_experience` + `ah_section_experience_cards` | Experience showcase cards |
| `ah_section_why_required` + `ah_section_why_required_cards` | "Why This Is Needed" section with YouTube video cards |

### 5.4 Content Tables

| Table | Purpose |
|-------|---------|
| `ah_services` | Services with title, slug, image, descriptions, SEO meta. Linked to bullet points and taxonomies |
| `ah_service_bullet_points` | Key points for each service |
| `ah_reviews` | Customer/partner/event reviews with reviewer info, rating (1–5), source (manual/google/facebook), featured flag, optional short description |
| `ah_review_images` | Gallery images per review |
| `ah_section_reviews_header` | Reviews section heading per page |
| `ah_faqs` | FAQ items with question, answer, optional link, page assignment |
| `ah_section_faq_header` | FAQ section heading per page |
| `ah_posts` | Multi-type content: blog, article, news, newsletter, guide. Includes slug, excerpt, full content, featured/banner images, author, status (active/inactive/draft/scheduled), SEO meta, view count |
| `ah_post_table_blocks` | Embedded data tables within posts (JSON) |
| `ah_post_links` | Reference/CTA links per post |
| `ah_post_stack_items` | Technology icons per post |
| `ah_related_posts` | Manual post-to-post relationships |
| `ah_news_detail_big_cards` + `ah_news_detail_card_links` | Rich cards with links for news posts |
| `ah_team_members` | Team with photo, name, designation, bio, email, LinkedIn, featured flag |
| `ah_events` | Events/hire packages with icon, title, description, items (JSON), color, featured flag, notification settings |

### 5.5 Navigation and Layout

| Table | Purpose |
|-------|---------|
| `ah_news_bar_items` | Scrolling announcement bar items with optional rich content, image, link, date range |
| `ah_footer_config` | Footer global settings (logo, site name, tagline, copyright) |
| `ah_footer_contact_links` | Footer contact info links |
| `ah_footer_social_links` | Footer social media links |
| `ah_floating_widgets` | Floating action buttons (WhatsApp, contact, chat, custom) with position and page exclusions |
| `ah_random_blog_card_configs` | Configures random blog post cards on pages |

### 5.6 Contact and Submissions

| Table | Purpose |
|-------|---------|
| `ah_contact_page_config` | Contact page configuration (heading, info, email, phone, WhatsApp, Google Maps embed) |
| `ah_contact_form_submissions` | Legacy contact form submissions (name, email, phone, subject, message, metadata) |

### 5.7 Client Stories

| Table | Purpose |
|-------|---------|
| `ah_client_stories_header` | Client stories page header |
| `ah_client_story_images` | Before/after or testimonial images |
| `ah_client_users_journey` | Customer journey entries with photos and narratives |
| `ah_client_gallery` | Image gallery with size classes |
| `ah_client_video_links` | Video testimonial links with thumbnails |

### 5.8 About Page

| Table | Purpose |
|-------|---------|
| `ah_about_page_header` | About page heading and info |
| `ah_about_story` + `ah_about_story_points` | Company story section with bullet points |
| `ah_about_values` | Core values with images |
| `ah_services_page_header` | Services listing page header |
| `ah_post_listing_page_header` | Blog/news listing page header |

### 5.9 Form Builder Tables

| Table | Purpose |
|-------|---------|
| `ah_forms` | Form definitions (name, notification email, success message, status) |
| `ah_form_fields` | Field definitions per form (label, key, type, placeholder, options for dropdowns, required flag, sort order). Types: text, email, tel, textarea, select, number, date, url |
| `ah_form_submissions` | Submission records with JSON data blob, IP address, timestamps |

### 5.10 Rules Engine / Triggers Maker

| Table | Purpose |
|-------|---------|
| `ah_rules` | Automation rules. Each rule has a trigger name, condition groups (JSON with nested AND/OR logic), actions (JSON array), dedup/cooldown settings, run count, last run timestamp |
| `ah_trigger_logs` | Execution log for every action attempt. Tracks rule ID, trigger name, context data, action config, status (pending/sent/failed/unsent), retry attempts, error messages, timestamps |

### 5.11 Page Builder

| Table | Purpose |
|-------|---------|
| `ah_builder_pages` | Dynamic pages with title, slug, JSON blocks, status (active/draft), SEO meta |

### Key Relationships

- Most content tables reference `ah_pages` via `page_id` (which page this content belongs to).
- Taxonomy terms reference `ah_taxonomy_types` via `type_id`.
- Taxonomies support hierarchy via self-referencing `parent_id`.
- Content-to-taxonomy links use `ah_content_taxonomies` (polymorphic pivot with `object_type`).
- Posts support multiple taxonomy assignments, related posts, table blocks, links, and stack items.
- Reviews link to gallery images via `ah_review_images`.
- All foreign keys use `ON DELETE CASCADE` (child removed when parent deleted) or `ON DELETE SET NULL` (reference cleared but record kept).

---

## 6. Features and Functionality Breakdown

### 6.1 Dashboard

The CMS Dashboard is the landing page of the admin portal. It provides an overview of the site's content and quick access to all management areas.

### 6.2 Site Settings

A key-value configuration store organized into groups:

- **General**: Site name, logo, footer tagline.
- **Contact**: Phone, WhatsApp, email, address, consultation URL.
- **Social**: Facebook, Twitter, LinkedIn, Instagram, YouTube URLs.
- **Design**: Primary color.
- **Notifications**: SMTP configuration (host, port, username, password, encryption), notification sender name and email.

Settings are stored in `ah_site_settings` with typed fields (text, textarea, image, color, url, email, phone, toggle, json).

### 6.3 Navigation Editor

A visual drag-and-drop navigation editor that manages:

- **Primary navigation**: Menu items with labels, URLs, icons, descriptions. Supports dropdowns with sub-items (each with label, URL, description, icon, highlight flag).
- **CTA button**: A call-to-action button in the header (label + URL).
- **Footer columns**: Multiple columns of link groups.
- **Legal links**: Privacy policy, terms of service, etc.

Navigation data is stored as JSON in WordPress options (`ah_cms_navigation`, `ah_cms_nav_cta`, `ah_cms_footer_columns`, `ah_cms_footer_legal`).

### 6.4 Home Page Management

Manages all sections of the home page through a tabbed admin interface:

- **Hero banners**: Carousel slides with desktop/mobile images, text overlays, CTA buttons, text positioning, and color overlays.
- **Hero section**: Badge text, heading, subheading, dual CTAs, background image.
- **Highlights**: Icon + text USP items.
- **Why Us**: Section header + feature cards with images and descriptions.
- **Guide Through**: Step-by-step walkthrough with an image and numbered points.
- **Stack Items**: Technology/partner logo grid.
- **Difference Table**: Feature comparison (us vs. competitors).
- **Featured Properties**: Showcase cards.
- **Experience Section**: Experience/portfolio cards.
- **Why Required**: Educational content with YouTube video cards.
- **Reviews Header**: Testimonials section configuration.
- **FAQ Header**: FAQ section configuration.

### 6.5 Services Management

Full CRUD for business services:

- Title, auto-generated slug, featured image.
- Short description and rich-text full description.
- Bullet points (add/remove dynamically).
- Taxonomy assignment (categories, tags).
- SEO meta fields (title, description).
- Status toggle (active/inactive), sort ordering.

### 6.6 Blog / Posts System

A multi-type publishing system supporting five content types:

- **Blog**: Standard blog posts.
- **Article**: Long-form articles.
- **News**: Company/industry news.
- **Newsletter**: Email newsletter archives.
- **Guide**: How-to guides.

Each post has: title, auto-generated slug (unique per type), excerpt, rich-text content, featured and banner images, author assignment, status management (active/inactive/draft/scheduled), scheduling support, SEO meta, view count tracking, taxonomy assignment, related posts, reference links, stack items (technology icons), and table data blocks.

### 6.7 Reviews Management

- Reviewer name, title, short description, profile image.
- Review text, star rating (1–5).
- Source tracking: manual, Google, Facebook, other.
- Featured flag for homepage display.
- Gallery images per review (for occasion photos).
- Taxonomy-based categorization (Review Type: Customer/Partner/Event; Review Categories: customer/partner/event/client-story).
- Paginated listing with search and source/status filters.

### 6.8 FAQ Management

- Question and answer pairs with rich text.
- Optional link (text + URL) for each FAQ.
- Page assignment (which page the FAQ appears on).
- Tag assignment via FAQ Tags taxonomy type.
- Sort ordering and status toggle.

### 6.9 Team Members

- Photo, name, designation/role, bio.
- Contact info: email, LinkedIn URL.
- Featured flag, sort ordering, status toggle.

### 6.10 Events / Hire Packages

- Emoji icon, title, rich description.
- Items list (stored as JSON - line items within the package).
- Color theme, featured flag, sort ordering.
- Booking notification integration: `notify_on_booking` flag and `booking_trigger_name` for Rules Engine integration.

### 6.11 Taxonomy System

A flexible, hierarchical taxonomy system independent of WordPress taxonomies:

- **Taxonomy Types**: Define categories of classification (Category, Tag, Subtag, Review Type, Review Categories, FAQ Tags, DataProtected).
- **Taxonomy Terms**: Individual terms within each type. Support hierarchy (parent/child), images, emoji icons, descriptions, SEO meta.
- **Protected terms**: System terms marked as `is_protected` that cannot be deleted (e.g., "Unchangeable", "Undeletable").
- **Universal pivot**: `ah_content_taxonomies` allows any content type (service, review, FAQ, post, event) to be tagged with any taxonomy term via a polymorphic `object_type` field.

### 6.12 News Bar

A scrolling announcement ticker at the top of the website:

- Multiple items with text, optional rich content, image, link URL, link target.
- Date range support (start/end dates for time-limited announcements).
- Status toggle and sort ordering.

### 6.13 Media Library

A custom media library (separate from WordPress's media library):

- Upload files (images: JPEG, PNG, GIF, WebP, SVG; documents: PDF; video: MP4).
- Records file metadata: name, path, URL, MIME type, file size, dimensions.
- Files stored in `wp-content/uploads/ah-media/YYYY/MM/`.
- Used throughout the CMS for image selection in services, reviews, team, banners, etc.
- AJAX-based upload, listing, and deletion.

### 6.14 Form Builder

A no-code form creation system:

- **Admin UI**: Create forms with a name, notification email, and custom success message.
- **Field types**: Text, Email, Phone, Textarea, Dropdown (select), Number, Date, URL.
- **Field settings**: Label, placeholder, required flag, sort order. Dropdown fields support custom option lists.
- **Shortcode embedding**: `[ah_form id="N"]` renders the form on any page.
- **Front-end rendering**: Self-contained HTML with vanilla JavaScript (no framework dependency). Includes a honeypot field for spam prevention, AJAX submission, loading spinner, success/error feedback, and form disabling after successful submission.
- **Submission storage**: All submissions stored in `ah_form_submissions` as JSON data with IP tracking.
- **Rules Engine integration**: On submission, `AH_Rules_Engine::evaluate('form_submit', $context)` is called, allowing automated email notifications, webhooks, etc.

### 6.15 Triggers Maker (Rules Engine)

A general-purpose automation platform:

**Triggers** (events that start the automation):
- `form_submit` - fired automatically when any AH form is submitted.
- Custom trigger names (e.g., `sugarcane_contact_form`, `order_placed`, `user_signup`) - fired programmatically via `AH_Rules_Engine::evaluate('trigger_name', $context)`.

**Conditions** (when should actions run):
- Grouped conditions with AND/OR logic at both the group and inter-group level.
- Operators: equals, not_equals, contains, not_contains, starts_with, ends_with, gt, lt, gte, lte, is_empty, is_not_empty, regex.
- Fields reference context data keys (form field values, metadata).

**Actions** (what to do):
- `send_email`: Send email with template tokens (e.g., `{name}`, `{email}`). Supports HTML/plain text, CC, custom SMTP per email channel, From name/email override. Config tokens like `{config_email_from_email}` pull from site settings.
- `whatsapp`: Send WhatsApp messages via API integration.
- `http_request`: Make HTTP POST/GET requests to external URLs (webhooks).
- `update_option`: Update a WordPress option value.

**Execution Model**:
- By default, actions are queued as `pending` in `ah_trigger_logs`.
- A WP-Cron job runs every minute (`cron_process`), picking up pending entries and executing them.
- Failed actions are retried up to 3 times.
- Deduplication: configurable dedup key and time window to prevent duplicate notifications.
- Cooldown: configurable minimum interval between rule firings.
- Full execution log with status tracking, error messages, timestamps.
- Admin UI shows logs with filtering by status/trigger/rule, and allows retry/cancel/delete.

**Email Channels**: Named SMTP configurations stored as JSON in WordPress options. Each channel has its own host, port, username, password, encryption, from name, and from email. Rules can reference a channel by ID so different forms use different outbound servers.

### 6.16 Page Builder

A block-based page builder for creating custom landing pages:

- Admin UI for adding/editing pages with title, slug, and SEO meta.
- Content is stored as a JSON array of blocks in the `blocks` column.
- Block types include: heading, text, image, HTML, tabs, alerts, timelines, accordion, call-to-action, forms, and more.
- Pages are served via slug routing: when WordPress returns a 404, the plugin checks `ah_builder_pages` for a matching active slug and renders the page using `template-builder-page.php`.
- Supports "bare" mode (`?bare=1`) for embedding pages without theme header/footer.
- Front-end rendering includes tab switching, dismissible alerts, scroll-reveal animations, and accordion toggling.

### 6.17 Static Pages

Management of static HTML files stored in the `static/` directory.

### 6.18 CSV Data Import

Bulk import from CSV files for:

- Services, Reviews, FAQs, Posts, Team Members, Taxonomies, Events, News Bar Items.

Each import type has defined column mappings. The importer parses CSV files, validates data, creates records using the appropriate models, auto-generates slugs, and returns success/error counts.

### 6.19 Client Stories

A showcase section for customer case studies:

- Page header configuration.
- Story images (before/after photos with review text).
- User journey cards (heading, info, image, user details).
- Image gallery with configurable size classes (small/medium/large/full).
- Video links with thumbnails.

### 6.20 Audit Log

Complete audit trail of all administrative actions:

- Every create, update, and delete is logged.
- Captures: user ID, action type, table name, record ID, old values (JSON), new values (JSON), IP address, user agent, timestamp.
- Admin UI with pagination and the ability to clear logs.

### 6.21 Admin Actions

A utility panel for site administrators:

- **Flush rewrite rules**: Clears WordPress permalink cache.
- **Clear transients**: Removes all WordPress transient cache entries.
- **Clear audit log**: Purges the audit log table.
- **DB health check**: Verifies all expected custom tables exist and reports any missing ones.
- **Clear form submissions**: Removes all contact form submissions.
- **Rebuild schema**: Re-runs the full database installer to repair/upgrade tables.

### 6.22 SEO and Sitemap (cms-project-plugin.php)

A companion plugin that provides:

- **SEO meta tags**: Automatically outputs `<title>`, meta description, canonical URL, Open Graph tags, Twitter Card tags, and JSON-LD structured data (Organization + WebSite schemas) on every page.
- **Sitemap generation**: Admin page to generate `sitemap.xml` at the site root.
- **Auto-regeneration**: Sitemap is automatically regenerated whenever a post is published.

---

## 7. API Endpoints (AJAX)

All AJAX endpoints are registered under `wp_ajax_` (admin) or `wp_ajax_nopriv_` (public) hooks.

### Admin AJAX Endpoints (require `manage_options` + nonce)

| Action | Handler | Purpose |
|--------|---------|---------|
| `ah_toggle_status` | `handle_toggle_status` | Toggle active/inactive status on any allowed table |
| `ah_delete_item` | `handle_delete_item` | Delete a record from any allowed table |
| `ah_update_sort_order` | `handle_update_sort_order` | Update sort ordering for a batch of records |
| `ah_get_media` | `handle_get_media` | Fetch paginated media library items |
| `ah_upload_media` | `handle_upload_media` | Upload a file to the custom media library |
| `ah_delete_media` | `handle_delete_media` | Delete a media record and its file |
| `ah_mark_submission` | `handle_mark_submission` | Mark a contact submission as read/spam/resolved |
| `ah_save_nav_item` | `handle_save_nav_item` | Save a navigation menu item |
| `ah_delete_nav_item` | `handle_delete_nav_item` | Delete a navigation menu item |
| `ah_get_form_fields` | `handle_get_form_fields` | Fetch form field definitions (for AJAX-loaded form editing) |
| `ah_save_static_page` | `handle_save_static_page` | Create/update static HTML pages |
| `ah_flush_rewrites` | `handle_flush_rewrites` | Flush WordPress rewrite rules |
| `ah_clear_transients` | `handle_clear_transients` | Clear all transient cache |
| `ah_clear_audit_log` | `handle_clear_audit_log` | Truncate the audit log table |
| `ah_db_health_check` | `handle_db_health_check` | Check that all expected tables exist |
| `ah_clear_form_submissions` | `handle_clear_form_submissions` | Delete all contact form submissions |
| `ah_rebuild_schema` | `handle_rebuild_schema` | Re-run database installer |
| `ah_quick_save_post_meta` | `handle_quick_save_post_meta` | Quick-edit post metadata inline |

### Public AJAX Endpoints (logged-in + guest)

| Action | Handler | Purpose |
|--------|---------|---------|
| `ah_form_submit` | `handle_form_submit` | Process front-end form submissions from the Form Builder shortcode |

### Admin POST Handlers (non-AJAX)

| Handler | Purpose |
|---------|---------|
| `handle_navigation` | Save the complete navigation structure (header, footer columns, legal links, CTA) |
| `handle_notice_save` | Save site notice configuration |
| `handle_banners_save` | Save home hero banner slides |

---

## 8. Module-by-Module Explanation

### 8.1 Autoloader (`inc/class-autoloader.php`)

Maps 35+ class names to file paths in a static `$map` array. When PHP encounters an unknown class, `spl_autoload_register` calls `AH_Autoloader::load()`, which checks the map and requires the file. This avoids manual `require_once` statements everywhere.

### 8.2 Database Helper (`database/class-db-helper.php`)

A generic database access layer wrapping WordPress's `$wpdb`:

- `table($name)`: Constructs the full prefixed table name.
- `get_row($table, $id)`: Fetch a single record by primary key.
- `get_by($table, $col, $value)`: Fetch a single record by any column.
- `get_list($table, $args)`: Fetch multiple records with optional WHERE, WHERE IN, ORDER BY, LIMIT, OFFSET.
- `count($table, $where, $where_in)`: Count records with optional filters.
- `insert($table, $data)`: Insert a record, return the new ID.
- `update($table, $data, $id)`: Update a record by ID.
- `delete($table, $id)`: Delete a record by ID.
- `delete_where($table, $where)`: Delete records matching conditions.
- `set_status($table, $id, $status)`: Quick status toggle.
- `update_sort_order($table, $id, $order)`: Update sort position.
- `log_action(...)`: Write to the audit log table.
- `search_where($columns, $term)`: Build a LIKE-based search clause across multiple columns.
- `paginate_meta(...)`: Calculate pagination metadata (total pages, offset, has_prev, has_next).

### 8.3 Model Base (`models/class-model-base.php`)

Abstract class that every domain model extends. Provides:

- `find($id)`, `find_by($col, $value)`: Single record lookups.
- `all($args)`, `paginate($page, $args)`: List and paginated queries.
- `create($data)`, `update($id, $data)`, `delete($id)`: CRUD with automatic audit logging (old and new values captured).
- `set_status($id, $status)`, `set_sort_order($id, $order)`: Quick updates.
- `count($where)`, `search($term, $columns)`: Counting and search.

Each model sets a `$table_suffix` (e.g., `'services'`, `'reviews'`) and optionally overrides methods for domain-specific logic.

### 8.4 Validator (`helper/class-validator.php`)

Fluent validation class:

```php
$v = new AH_Validator($_POST);
$v->required('title', 'Title')
  ->email('contact_email', 'Email')
  ->url('website', 'Website URL')
  ->max_length('title', 255)
  ->in_list('status', ['active', 'inactive']);

if ($v->fails()) {
    echo $v->first_error();
}
```

Also provides static sanitization helpers: `sanitize_text`, `sanitize_textarea`, `sanitize_html` (wp_kses_post), `sanitize_url`, `sanitize_int`, `sanitize_slug`, `sanitize_color`.

### 8.5 Slug Helper (`helper/class-slug-helper.php`)

Generates URL-safe unique slugs by appending `-2`, `-3`, etc., until no collision exists in the target table/column. Has a specialized `generate_post()` method that scopes uniqueness by `post_type`.

### 8.6 Uploader (`helper/class-uploader.php`)

Handles file uploads to the custom media library:

- Validates MIME type against an allowlist (JPEG, PNG, GIF, WebP, SVG, PDF, MP4).
- Saves files to `wp-content/uploads/ah-media/YYYY/MM/` with a randomized filename suffix.
- Creates a record in `ah_media` with full metadata.
- Returns the media record ID or a `WP_Error`.

### 8.7 CSV Importer (`admin/import/class-csv-importer.php`)

Bulk data import from CSV files:

- `get_config()`: Returns column definitions and templates for each importable type.
- `parse_file($path)`: Reads CSV, detects encoding, parses rows.
- `import($type, $rows)`: Routes to type-specific importers.

Supported types: services, reviews, FAQs, posts, team members, taxonomies, events, news bar items. Each importer handles slug generation, default values, taxonomy lookup/creation, and error reporting.

---

## 9. Configuration and Setup

### 9.1 Requirements

- WordPress 6.0+
- PHP 8.0+ (uses union types, named arguments, arrow functions)
- MySQL 5.7+ / MariaDB 10.3+ (uses JSON columns, InnoDB)

### 9.2 Installation

1. Upload the plugin folder to `wp-content/plugins/`.
2. Activate the plugin from the WordPress Plugins admin page.
3. The database tables are created automatically on activation.
4. Navigate to "CMS ADMIN" in the WordPress sidebar to begin configuring your site.

### 9.3 Dual-Mode Operation

The system supports two operation modes:

**Plugin Mode** (recommended): Install as a WordPress plugin. The `ah-cms.php` file handles all bootstrapping. Pair it with any front-end theme that reads from the `ah_` tables.

**Standalone Theme Mode**: Place all files in a WordPress theme directory. The `functions.php` file detects that the plugin constant `AH_PLUGIN_DIR` is not defined and performs full bootstrap itself - defining constants, registering the autoloader, initializing admin, AJAX, and database, and setting up theme support.

### 9.4 Key Constants

| Constant | Default | Purpose |
|----------|---------|---------|
| `AH_PLUGIN_VERSION` | `'1.0.3'` | Current version - triggers DB migration when changed |
| `AH_DB_VERSION_KEY` | `'ah_cms_db_version'` | WordPress option key storing the installed DB version |
| `TABLE_MID_FIX` | `'_cms_plug_'` | Infix for table names (change only before first install) |
| `AH_PLUGIN_DIR` | Plugin directory path | Absolute filesystem path to the plugin root |
| `AH_PLUGIN_URL` | Plugin directory URL | Web-accessible URL to the plugin root |
| `AH_THEME_DIR` / `AH_THEME_URL` | Aliases of plugin paths | Backward-compatibility aliases |

### 9.5 SMTP Configuration

Email sending can be configured via Site Settings (Notifications group):

- `smtp_host`: SMTP server hostname
- `smtp_port`: Port number (587 for TLS, 465 for SSL)
- `smtp_user`: SMTP username
- `smtp_pass`: SMTP password
- `smtp_secure`: Encryption method (`tls` or `ssl`)
- `notif_from_name`: Sender display name
- `notif_from_email`: Sender email address

Additionally, the Rules Engine supports named "Email Channels" for per-rule SMTP configuration.

---

## 10. Important Business Logic

### 10.1 Database Migration System

The plugin uses a version-check migration pattern:

1. On every page load, `maybe_upgrade()` compares the stored DB version with the current plugin version.
2. If they differ, the full `install()` method runs (using `CREATE TABLE IF NOT EXISTS`, so existing tables are not destroyed).
3. After the full install, individual `ensure_*()` methods run to add new columns, create new tables, seed new data, and fix legacy issues.
4. This design means every migration is idempotent - safe to run multiple times.

### 10.2 Broken Foreign Key Cleanup

Early versions of the schema created foreign keys referencing `ah_admin_users` and `ah_media` for columns that actually store WordPress user IDs and WordPress attachment IDs. The `drop_broken_fks()` method queries `INFORMATION_SCHEMA` to find and drop these invalid constraints.

### 10.3 Builder Page Routing

Builder pages are served without creating WordPress posts or pages:

1. A `template_redirect` hook fires on 404 responses.
2. The plugin extracts the request slug from the URL.
3. It queries `ah_builder_pages` for an active page with that slug.
4. If found, it sets the global `$GLOBALS['ah_builder_page']` and includes the template.
5. The template decodes the JSON blocks and renders each one.
6. `exit()` prevents WordPress from showing its 404 page.

### 10.4 Rules Engine Deduplication

To prevent duplicate notifications (e.g., double-submitting a form):

- Each rule can define a `dedup_key` (a template string like `{email}_{form_id}`) and a `dedup_window_hours`.
- Before queuing actions, the engine checks `ah_trigger_logs` for recent entries with the same resolved dedup key.
- If a match is found within the window, the rule is silently skipped.

### 10.5 Rules Engine Token System

Action templates use `{token}` syntax that gets replaced with context data:

- `{field_key}`: Direct field value from the trigger context.
- `{config_*}`: Values from site settings (e.g., `{config_email_from_email}`).
- `{site_url}`, `{site_name}`: WordPress site info.
- `{submitted_at}`: Timestamp of the event.

---

## 11. Security Considerations

### 11.1 Access Control

- All admin AJAX handlers call `self::verify()`, which checks both `current_user_can('manage_options')` and validates the nonce via `check_ajax_referer()`.
- Admin POST handlers use `check_admin_referer()` for CSRF protection.
- Public form submissions verify the front-end nonce.
- Admin menu pages require the `manage_options` capability.

### 11.2 Input Sanitization

- All user inputs are sanitized using WordPress functions: `sanitize_text_field()`, `sanitize_textarea_field()`, `wp_kses_post()` (for rich HTML), `esc_url_raw()`, `sanitize_title()`.
- The `AH_Validator` class provides fluent validation for required fields, email format, URL format, max length, and enum values.
- AJAX table/model parameters are checked against explicit allowlists.

### 11.3 SQL Injection Prevention

- All database queries use `$wpdb->prepare()` with parameterized placeholders.
- Table names are constructed from constants and sanitized keys, never from raw user input.

### 11.4 File Upload Security

- Uploads are validated against a MIME type allowlist (images, PDF, MP4 only).
- Filenames are sanitized and randomized.
- Files are stored outside the theme directory in `wp-content/uploads/ah-media/`.

### 11.5 Spam Prevention

- Form Builder includes a honeypot field (hidden input `ah_hp`). If it contains a value, the submission is rejected as spam.
- Front-end nonce verification prevents cross-site request forgery.

### 11.6 Direct Access Protection

- Every PHP file begins with `defined('ABSPATH') || exit;` to prevent direct file execution.
- `index.php` files prevent directory listing.

### 11.7 Audit Trail

- Every data modification (create, update, delete) is logged with the acting user's ID, IP address, user agent, and before/after values. This provides accountability and the ability to trace unauthorized changes.

---

## 12. Integrations and Dependencies

### WordPress Dependencies

- **Core APIs**: `$wpdb` (database), `wp_mail()` (email), WP-Cron (scheduling), WordPress Media Uploader (admin image selection), `wp_kses_post()` (HTML sanitization), admin menu API, AJAX API, nonce API.
- **jQuery + jQuery UI Sortable**: Used in admin for drag-and-drop reordering.
- **WP Color Picker**: Used in admin for color fields.
- **WordPress Media Library**: Admin pages use the WordPress media modal for image selection (via `wp_enqueue_media()`).
- **TinyMCE / WordPress Editor**: Loaded on pages that need rich text editing (Page Builder, Pages, Posts).

### External Integrations

- **SMTP Servers**: Optional external SMTP for reliable email delivery (configured via settings or email channels).
- **WhatsApp API**: The Rules Engine can send WhatsApp messages via HTTP POST to a WhatsApp Business API endpoint.
- **Webhooks**: Generic HTTP request actions can integrate with any external service (Zapier, Slack, CRMs, etc.).

### No External PHP Dependencies

The plugin has zero Composer dependencies - it uses only WordPress core functions and native PHP.

---

## 13. User Flow Diagrams

### Content Editor Workflow

```
1. Log in to WordPress admin
2. Click "CMS ADMIN" → Choose section (e.g., "Services")
3. View list of existing items (paginated, searchable)
4. Click "Add New" → Fill in form → Click "Save"
   → Record created in ah_services
   → Slug auto-generated
   → Audit log entry created
5. Or: Click "Edit" on existing item → Modify fields → Save
6. Or: Click status toggle → Active/Inactive switches via AJAX
7. Or: Drag to reorder → Sort order updated via AJAX
8. Or: Click "Delete" → Confirmation → Record removed
```

### Visitor Form Submission Workflow

```
1. Visitor lands on page with [ah_form id="5"]
2. Form renders with configured fields
3. Visitor fills out form and submits
4. JavaScript sends AJAX POST
5. Server validates (nonce, honeypot, required fields)
6. Submission saved to ah_form_submissions
7. Rules Engine evaluates matching rules
8. Actions queued in ah_trigger_logs
9. Visitor sees success message, form is disabled
10. Within 60 seconds, WP-Cron fires
11. Pending actions are executed (emails sent, webhooks called)
12. Trigger log status updated to 'sent' or 'failed'
```

---

## 14. Future Enhancement Possibilities

Based on the current architecture, natural extensions include:

- **REST API layer**: Expose content via WordPress REST API endpoints for headless front-end frameworks (React, Next.js, etc.).
- **Role-based access control**: The `ah_admin_roles` and `ah_admin_users` tables exist but are largely unused. Implementing granular permissions per admin page would add multi-user support.
- **Media image optimization**: Auto-generate thumbnails and WebP variants on upload.
- **Scheduled content publishing**: The `scheduled_at` column exists in `ah_posts` - implementing a cron job to auto-publish at the scheduled time would complete the feature.
- **Search indexing**: Add full-text search indexes to content tables for faster search.
- **Revision history**: Store content revisions to allow rollback.
- **Drag-and-drop Page Builder UI**: The JSON block structure supports it - a React-based visual editor would enhance the page builder experience.
- **Multi-language support**: The taxonomy system could support language variants.
- **Analytics dashboard**: Track page views, form submissions, and engagement metrics over time.
- **Export functionality**: CSV/JSON export of content and submissions.
- **Webhook incoming**: Accept incoming webhooks to create/update content programmatically.
- **A/B testing**: Use the banner system to test different hero variants.

---

## 15. Quick Reference

### Adding a New Content Type

1. Create a new table definition in `AH_DB_Installer::get_table_sqls()`.
2. Create a model class extending `AH_Model_Base` with the appropriate `$table_suffix`.
3. Register the class in `AH_Autoloader::$map`.
4. Create an admin page template in `admin/pages/`.
5. Register the submenu in `AH_Admin_Menus::register()` and add the page callback.
6. Add the table name to the allowlists in `AH_Ajax_Handlers` (toggle, delete, sort).
7. Optionally add CSV import support in `AH_CSV_Importer`.

### Key Entry Points

| What | Where |
|------|-------|
| Plugin bootstrap | `ah-cms.php` |
| Database creation | `database/class-db-installer.php` → `install()` |
| Admin menu setup | `admin/menus/class-admin-menus.php` → `register()` |
| AJAX endpoints | `admin/ajax/class-ajax-handlers.php` |
| Form shortcode | `inc/class-form-builder.php` → `render()` |
| Automation trigger | `AH_Rules_Engine::evaluate($trigger, $context)` |
| Builder page render | `templates/template-builder-page.php` |
| Front-end assets | `inc/class-asset-loader.php` |

---

*This documentation covers the complete CMS ADMIN plugin as of version 1.0.3. For deployment instructions, see `deploy.md` in the project root.*
