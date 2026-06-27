# CMS Admin Plugin - Architecture Guide

> **Purpose of this file:** Understand the plugin end-to-end so you can enhance it confidently.
> For deep reference see `FULL.md`. For code recipes see `learn.md`.

---

## What Is This Plugin?

A custom WordPress CMS that **replaces** the WP editor with its own:
- Database tables (`wp_ah_*`)
- Admin dashboard (CMS ADMIN sidebar)
- Model classes (PHP OOP, one per content type)
- AJAX-powered UI

The plugin is paired with a front-end theme that reads data from `wp_ah_*` tables.

---

## Folder Structure at a Glance

```
cms-plugin/
│
├── ah-cms.php              ← Entry point. WordPress loads this first.
│
├── inc/
│   └── class-autoloader.php   ← Maps class names → file paths (no Composer)
│
├── database/
│   ├── class-db-schema.php    ← CREATE TABLE definitions
│   ├── class-db-migrations.php← ALTER TABLE / add columns over time
│   ├── class-db-seed.php      ← Default data on fresh install
│   ├── class-db-installer.php ← Orchestrates all of the above
│   └── class-db-helper.php    ← Raw SQL helpers (insert/update/delete/paginate)
│
├── models/
│   ├── class-model-base.php   ← Abstract base: CRUD for free
│   └── class-*-model.php      ← One file per content type (reviews, faqs, posts…)
│
├── admin/
│   ├── class-admin-bootstrap.php  ← Registers hooks, loads assets
│   ├── menus/
│   │   └── class-admin-menus.php  ← Sidebar menu + routing
│   ├── pages/
│   │   └── *.php                  ← One file per admin screen
│   └── ajax/
│       └── class-ajax-handlers.php← All wp_ajax_* endpoints
│
├── helper/
│   ├── class-slug-helper.php      ← Auto-generates unique URL slugs
│   ├── class-validator.php        ← Validates + sanitizes user input
│   ├── class-uploader.php         ← Handles file uploads
│   └── class-pagination-helper.php
│
└── inc/
    ├── class-form-builder.php     ← [ah_form] shortcode system
    ├── class-rules-engine.php     ← Triggers / automation engine
    └── builder-block-renderer.php ← Renders Page Builder blocks on frontend
```

---

## The 6 Layers

Think of the plugin as 6 stacked layers. Each layer has one job.

```
┌─────────────────────────────────────────────┐
│  6. FRONTEND (theme reads plugin data)       │
├─────────────────────────────────────────────┤
│  5. SPECIAL SYSTEMS                          │
│     Form Builder · Rules Engine · Builder    │
├─────────────────────────────────────────────┤
│  4. HELPERS (stateless utilities)            │
│     Slug · Validator · Uploader · Pagination │
├─────────────────────────────────────────────┤
│  3. ADMIN UI                                 │
│     Menu → Page File → Model → AJAX → JSON  │
├─────────────────────────────────────────────┤
│  2. MODELS (one per content type)            │
│     All extend Model Base → CRUD for free    │
├─────────────────────────────────────────────┤
│  1. DATABASE (the foundation)                │
│     Schema · Migrations · Seed · DB Helper  │
└─────────────────────────────────────────────┘
```

---

## How WordPress Loads the Plugin

```
WordPress boots
    └── ah-cms.php
            ├── Define constants  (AH_PLUGIN_DIR, AH_PLUGIN_URL, version)
            ├── Register Autoloader  ← classes load on demand
            ├── if (is_admin)  →  AH_Admin_Bootstrap::init()
            │       ├── Register sidebar menu
            │       ├── Enqueue admin CSS + JS
            │       └── Register all AJAX hooks
            ├── AH_Ajax_Handlers::init_public()  ← form submit (works for guests too)
            ├── Register shortcodes  [ah_form] [ah_related_links] [ah_static_page]
            ├── wp_loaded  →  AH_DB_Installer::maybe_upgrade()
            │       └── If version changed → run new migrations
            └── Schedule cron  →  Rules Engine runs every 60 seconds
```

---

## Layer 1 - Database

| File | Job |
|---|---|
| `class-db-schema.php` | All `CREATE TABLE` statements live here |
| `class-db-migrations.php` | `ALTER TABLE` / add columns - runs on version bump |
| `class-db-seed.php` | Inserts default rows on fresh install |
| `class-db-installer.php` | Runs schema → FK → seed → migrations in order |
| `class-db-helper.php` | `insert()` `update()` `delete()` `paginate()` etc. |

**Adding a new table:**
1. Add `CREATE TABLE IF NOT EXISTS` to `class-db-schema.php`
2. Add seed rows to `class-db-seed.php` (optional)
3. Bump `AH_PLUGIN_VERSION` constant to trigger `maybe_upgrade()`

---

## Layer 2 - Models

Every content type has its own model class that extends `AH_Model_Base`.

```php
class AH_Reviews_Model extends AH_Model_Base {
    protected string $table_suffix = 'reviews'; // → wp_ah_reviews
}
```

**You get these methods for free** (no extra code needed):

| Method | What it does |
|---|---|
| `find($id)` | Get one record by ID |
| `find_by($col, $value)` | Get one record by any column |
| `all()` | Get all records |
| `paginate($page)` | Get paginated records |
| `create($data)` | Insert + auto-log to Audit Log |
| `update($id, $data)` | Update + auto-log to Audit Log |
| `delete($id)` | Delete + auto-log to Audit Log |
| `count()` | Count records |

**Adding a new model:**
1. Create `models/class-yourfeature-model.php`
2. Extend `AH_Model_Base`, set `$table_suffix`
3. Register in `inc/class-autoloader.php`:
   ```php
   'AH_YourFeature_Model' => 'models/class-yourfeature-model.php',
   ```

---

## Layer 3 - Admin UI

### How a page works (full flow)

```
1. User clicks "Reviews" in sidebar
        ↓
2. class-admin-menus.php routes → page_reviews()
        ↓
3. admin/pages/reviews.php loads
        ↓
4. reviews.php creates (new AH_Reviews_Model())->paginate()
        ↓
5. Renders HTML table with data
        ↓
6. User clicks Save → JavaScript POSTs to admin-ajax.php
        ↓
7. class-ajax-handlers.php::handle_* verifies nonce + capability
        ↓
8. Calls model->update() or model->create()
        ↓
9. Returns JSON → JS shows success toast
```

### Adding a new admin page

1. Create `admin/pages/my-feature.php`
2. Add to `admin/menus/class-admin-menus.php`:
   ```php
   // In $submenus array:
   ['title' => 'My Feature', 'menu' => 'My Feature',
    'slug' => 'ah-my-feature', 'callback' => 'page_my_feature'],

   // Add callback method:
   public static function page_my_feature() { self::load('my-feature'); }
   ```

### AJAX endpoints (class-ajax-handlers.php)

Every save/delete/sort goes through AJAX. Pattern:
```php
public static function handle_my_action(): void {
    self::verify();   // ← checks nonce + manage_options capability
    $id = (int) ($_POST['id'] ?? 0);
    // ... do work ...
    wp_send_json_success(['message' => 'Done.']);
}
```
Add the action name to the `$actions` array in `init()` and the handler auto-wires.

---

## Layer 4 - Helpers

Stateless utilities - no database, no hooks. Use from anywhere.

| Class | Use it for |
|---|---|
| `AH_Slug_Helper::generate($title, $table, $col)` | Unique URL slug from a title |
| `AH_Validator` | Validate required/email/url/length; sanitize input |
| `AH_Uploader::upload('file_field')` | Upload file → save to `ah_media` → return media ID |
| `AH_Pagination::current_page()` | Read `?paged=N` from URL |
| `AH_Pagination::render($meta)` | Output prev/next page links |

---

## Layer 5 - Special Systems

### Form Builder
- Admin creates a form with fields
- Embed anywhere with `[ah_form id="N"]`
- Submissions saved to `wp_ah_form_submissions`
- Rules Engine fires on each submission

### Rules Engine (Triggers Maker)
```
Admin creates a Rule:
  Trigger: form_submit
  Condition: field "service" equals "renovation"
  Action: send_email to admin@site.com
        ↓
Form submitted by visitor
        ↓
AH_Rules_Engine::evaluate('form_submit', $data)
        ↓
Matching rules queued as "pending" in wp_ah_trigger_logs
        ↓
WP-Cron fires every 60 seconds
        ↓
Pending actions executed (email sent, webhook called)
```

### Page Builder
- Admin creates a page with JSON blocks
- Page served at a slug (e.g. `/about-us/`) without creating a WP post
- On 404 → plugin checks `wp_ah_builder_pages` → renders if found

---

## Layer 6 - Frontend (Theme Reads Plugin Data)

The front-end theme calls model methods to get data:

```php
$reviews  = (new AH_Reviews_Model())->all();
$nav      = (new AH_Nav_Model())->get_tree();
$settings = (new AH_Settings_Model())->get_value('site_name');
```

Shortcodes usable anywhere in WordPress:
- `[ah_form id="1"]` - renders a form
- `[ah_static_page slug="terms"]` - renders saved HTML
- `[ah_related_links]` - renders related content links

---

## Adding a Complete New Feature (Step-by-Step Recipe)

Example: adding **"Testimonial Videos"**

| Step | File | What to do |
|---|---|---|
| 1. Table | `database/class-db-schema.php` | Add `CREATE TABLE IF NOT EXISTS wp_ah_testimonial_videos` |
| 2. Model | `models/class-testimonial-videos-model.php` | Extend `AH_Model_Base`, set `$table_suffix = 'testimonial_videos'` |
| 3. Autoloader | `inc/class-autoloader.php` | Add `'AH_Testimonial_Videos_Model' => 'models/class-testimonial-videos-model.php'` |
| 4. Admin page | `admin/pages/testimonial-videos.php` | Build the list + form UI |
| 5. Menu | `admin/menus/class-admin-menus.php` | Add entry in `$submenus` array + callback method |
| 6. AJAX | `admin/ajax/class-ajax-handlers.php` | Add table name to `$allowed_tables` for toggle/delete/sort |
| 7. Version bump | `ah-cms.php` | Increment `AH_PLUGIN_VERSION` to trigger DB migration |

---

## Key Rules (Never Break These)

| Rule | Why |
|---|---|
| Always use `$wpdb->prepare()` for SQL | Prevents SQL injection |
| Always `sanitize_*` before saving, `esc_*` before echoing | Input/output security |
| Always verify nonce + `manage_options` in AJAX handlers | CSRF + auth protection |
| Store `image_id` (int), not image URLs | URLs break when files move |
| Add every new class to the autoloader map | Missing entry = fatal error |
| Use `AH_DB_Helper::table('suffix')` not hardcoded table names | Breaks on non-standard WP prefixes |

---

## Admin Menu Groups (Current)

| Group | Menu Items |
|---|---|
| Dashboard | Dashboard |
| Content | Blog Posts · Reviews · Client Stories · FAQs · Taxonomy Manager |
| Site Layout | Navigation Editor · Home Banners · Spotlights · Site Notices · News Bar |
| Pages & Builders | Page Builder · Static Pages · Pages Manager |
| Forms & Communication | Form Builder · Notifications |
| Assets | Media Library · File Links |
| System | Analytics Reports · Rules Engine · Data Import · Site Settings · Audit Log · Admin Tools · Reference Notes · Help & Guide |

---

## Quick File Reference

| Need to... | Go to |
|---|---|
| Change what loads on boot | `ah-cms.php` |
| Add a database table | `database/class-db-schema.php` |
| Add a data migration | `database/class-db-migrations.php` |
| Add a new content type | `models/` + autoloader |
| Add a sidebar menu item | `admin/menus/class-admin-menus.php` |
| Add/edit an admin screen | `admin/pages/*.php` |
| Add an AJAX endpoint | `admin/ajax/class-ajax-handlers.php` |
| Create an automation trigger | `AH_Rules_Engine::evaluate('event', $data)` |
| Add a shortcode | `ah-cms.php` → `add_action('init', ...)` |
