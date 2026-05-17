# learn.md — Advith Homes CMS: Complete Developer Reference
<!-- Living document — append new sections as the project grows. -->
<!-- Last updated: 2026-05-15 — Session 4 -->

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Setup & Installation](#2-setup--installation)
3. [Repository Structure](#3-repository-structure)
4. [Bootstrap & Autoloading](#4-bootstrap--autoloading)
5. [Database Layer](#5-database-layer)
6. [Model System](#6-model-system)
7. [Helper Classes](#7-helper-classes)
8. [Admin Portal](#8-admin-portal)
9. [AJAX System](#9-ajax-system)
10. [Import System](#10-import-system)
11. [Admin CSS Component Library](#11-admin-css-component-library)
12. [Admin JavaScript Patterns](#12-admin-javascript-patterns)
13. [Security Rules](#13-security-rules)
14. [Naming Conventions](#14-naming-conventions)
15. [Recipes — How to Add New Features](#15-recipes--how-to-add-new-features)
16. [Common Gotchas](#16-common-gotchas)
17. [Full DB Table List](#17-full-db-table-list)
18. [File Links System](#18-file-links-system)
19. [Dynamic Form Builder System](#19-dynamic-form-builder-system)
20. [Admin Actions System](#20-admin-actions-system)
21. [Static HTML Pages System](#21-static-html-pages-system)
22. [Plugin Mode & Deployment](#22-plugin-mode--deployment)

---

## 1. Project Overview

Advith Homes (`ah_final_theme`) is a **fully custom WordPress CMS platform** built as a theme.
It replaces WordPress's native content editor with a purpose-built admin portal that manages
every piece of content through structured database tables.

**Two phases:**

| Phase | Status | What it covers |
|---|---|---|
| Phase 1 — Admin Portal | Complete | All content management, settings, media, import |
| Phase 2 — Frontend Theme | Pending | Public-facing templates that read from Phase 1 data |

**What makes it different from a standard WP theme:**
- No `wp_posts` usage for custom content — all data lives in `wp_ah_*` tables
- OOP model classes own all DB access; admin pages are thin view templates
- Static class-map autoloader (no Composer, no PSR-4 directory scan)
- Full audit log for every create / update / delete operation
- CSV bulk import for every content type with per-type sample files
- WordPress is used only as the host runtime (auth, nonces, cron, file handling)

---

## 2. Setup & Installation

### Requirements
- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+ (InnoDB engine, foreign key support required)
- Laragon (or any LAMP / LEMP stack) for local development

### Steps
1. Copy `ah_final_theme/` into `wp-content/themes/`
2. Activate in **Appearance → Themes**
3. Activation fires `after_switch_theme` → `AH_DB_Installer::install()`
   which creates all custom tables and seeds default data
4. Navigate to **CMS Portal** in the WP admin sidebar

### First-time seeds (auto-inserted on install)

| Seed | Detail |
|---|---|
| Admin role | `super_admin` |
| Taxonomy types | category, tag, subtag |
| Site settings | 12 keys: site name, tagline, email, phone, socials, etc. |
| Nav menus | primary, footer |
| Pages | home, about, services, contact, client-stories, blog, news |

### Re-running the installer
`CREATE TABLE IF NOT EXISTS` makes the installer idempotent — re-activating the theme
never destroys data. Seeds use `INSERT IGNORE`.

### Upgrading the schema
Bump the version constant `AH_DB_VERSION_KEY` in `functions.php`. `maybe_upgrade()`
hooks into `wp_loaded` and re-runs the installer when the stored version is stale.

---

## 3. Repository Structure

```
ah_final_theme/
│
├── ah-cms.php                         WordPress plugin main file (Plugin Name: CMS ADMIN)
├── functions.php                      Dual-mode bootstrap (plugin active vs standalone)
├── style.css                          WP theme header (Theme Name, Version, Author)
├── index.php                          WP fallback template (required)
├── template-contact.php               Page template: Contact Page (AJAX form + sidebar)
├── template-static-page.php           Page template: Static HTML Page (iframe isolation)
├── static/                            Raw .html files served by template-static-page.php
├── .brain.md                          AI project memory file (append-only)
├── learn.md                           This file — developer reference
├── deploy.md                          Deployment + companion theme building guide
├── Design.md                          Visual / UX design reference
│
├── database/
│   ├── schema.sql                     Original schema reference
│   ├── class-db-installer.php         Table creation, FK wiring, seeding
│   └── class-db-helper.php            Static query utilities
│
├── inc/
│   ├── class-autoloader.php           Static class-name → file-path map
│   ├── class-theme-setup.php          Theme supports, nav menu registration, shortcodes
│   ├── class-asset-loader.php         Frontend CSS / JS enqueue
│   └── class-form-builder.php         Dynamic Form Builder — DB install, CRUD, shortcode renderer
│
├── models/                            17 domain model classes
│   ├── class-model-base.php           Abstract CRUD base
│   ├── class-settings-model.php
│   ├── class-media-model.php
│   ├── class-pages-model.php
│   ├── class-nav-model.php
│   ├── class-home-model.php           All 9 home-page sections
│   ├── class-services-model.php
│   ├── class-about-model.php
│   ├── class-reviews-model.php
│   ├── class-faqs-model.php
│   ├── class-posts-model.php
│   ├── class-team-model.php
│   ├── class-contact-model.php
│   ├── class-taxonomy-model.php
│   ├── class-newsbar-model.php
│   ├── class-footer-model.php
│   └── class-audit-model.php
│
├── helper/
│   ├── class-slug-helper.php          Unique slug generation
│   ├── class-pagination-helper.php    WP-style paginator
│   ├── class-validator.php            Chainable validation + sanitizers
│   └── class-uploader.php             File upload → wp_ah_media record
│
└── admin/
    ├── class-admin-bootstrap.php      Hooks: admin_menu, assets, AJAX
    ├── menus/
    │   └── class-admin-menus.php      23 submenus + page callbacks
    ├── ajax/
    │   └── class-ajax-handlers.php    16 admin AJAX actions + 2 public
    ├── import/
    │   ├── class-csv-importer.php     CSV parser + 7 typed import methods
    │   └── samples/                   One .csv sample file per import type
    ├── assets/
    │   ├── css/admin-style.css        Admin component CSS library
    │   └── js/admin-script.js         Status toggle, sort, delete, media, repeater
    └── pages/                         23 admin page templates (thin views)
        ├── dashboard.php
        ├── settings.php
        ├── pages.php
        ├── media.php
        ├── news-bar.php
        ├── home-sections.php
        ├── services.php
        ├── about.php
        ├── reviews.php
        ├── faqs.php
        ├── posts.php
        ├── team.php
        ├── client-stories.php
        ├── contact.php
        ├── taxonomy.php
        ├── submissions.php
        ├── audit-log.php
        ├── import.php
        ├── file-links.php
        ├── form-builder.php           Dynamic Form Builder admin UI
        ├── admin-actions.php          Utility action cards (flush, clear, health check)
        └── static-pages.php           Static HTML page editor + file manager
```

---

## 4. Bootstrap & Autoloading

### functions.php — execution order

```php
define('AH_THEME_VERSION', '1.0.0');
define('AH_THEME_DIR', get_template_directory());
define('AH_THEME_URL', get_template_directory_uri());

require_once AH_THEME_DIR . '/inc/class-autoloader.php';
AH_Autoloader::register();          // spl_autoload_register

AH_Theme_Setup::init();             // add_theme_support(), register_nav_menus()
AH_Asset_Loader::init();            // wp_enqueue_scripts on frontend only

if ( is_admin() ) {
    AH_Admin_Bootstrap::init();     // admin_menu + admin_enqueue_scripts + AJAX hooks
}

add_action('after_switch_theme', ['AH_DB_Installer', 'install']);
add_action('wp_loaded',          ['AH_DB_Installer', 'maybe_upgrade']);
```

### Autoloader

`inc/class-autoloader.php` holds a static `$map` array. Every class name maps to a
file path relative to `AH_THEME_DIR`. There is no filesystem scan.

```php
private static array $map = [
    'AH_Home_Model'   => 'models/class-home-model.php',
    'AH_CSV_Importer' => 'admin/import/class-csv-importer.php',
    // ...
];
```

**Rule: add every new class to this map.** Omitting it causes a fatal
"Class not found" on first instantiation.

---

## 5. Database Layer

### Table naming — never hardcode the prefix

```php
// Always:
$table = AH_DB_Helper::table('services');   // → wp_ah_services

// Never:
$table = 'wp_ah_services';   // breaks on non-standard prefix installs
```

`AH_DB_Helper::table()` uses `$wpdb->prefix` internally, making the theme
multisite-compatible.

### AH_DB_Helper static methods

| Method | Returns | Purpose |
|---|---|---|
| `table(string $suffix)` | string | Full prefixed table name |
| `get_row(string $table, int $id)` | object\|null | Fetch single row by primary key |
| `get_by(string $table, string $col, mixed $val)` | object\|null | First row where col = val |
| `get_list(string $table, array $where, string $order)` | array | All matching rows |
| `count(string $table, array $where)` | int | COUNT(*) with optional WHERE |
| `insert(string $table, array $data)` | int\|false | Insert row, return ID |
| `update(string $table, array $data, array $where)` | int\|false | Update rows |
| `delete(string $table, int $id)` | bool | Delete by ID |
| `delete_where(string $table, array $where)` | bool | Delete by conditions |
| `set_status(string $table, int $id, string $status)` | bool | Update status column |
| `update_sort_order(string $table, array $ordered_ids)` | void | Bulk-update sort_order |
| `log_action(string $action, string $table, int\|null $id, array $data)` | void | Write to audit_logs |
| `search_where(string $term, array $cols)` | string | Build LIKE WHERE clause |
| `paginate_meta(int $total, int $per_page, int $page)` | array | Pagination metadata |

### Writing raw queries

Always use `$wpdb->prepare()` — no exceptions:

```php
global $wpdb;
$table = AH_DB_Helper::table('services');

$row = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM `{$table}` WHERE slug = %s AND status = %s",
        $slug, 'active'
    )
);
```

### Adding a new table

Add to `database/class-db-installer.php` inside `install()`, **before** the FK section:

```php
$wpdb->query("
    CREATE TABLE IF NOT EXISTS `{$p}ah_my_things` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `title`      VARCHAR(255) NOT NULL DEFAULT '',
        `sort_order` SMALLINT     NOT NULL DEFAULT 0,
        `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
        `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
```

If you need a foreign key, add it in the **FK section** (after all tables exist):

```php
$wpdb->query("ALTER TABLE `{$p}ah_my_things`
    ADD CONSTRAINT `fk_my_things_page`
    FOREIGN KEY (`page_id`) REFERENCES `{$p}ah_pages`(`id`) ON DELETE SET NULL;
");
```

---

## 6. Model System

### AH_Model_Base — available methods

```php
// Read
$model->find(int $id): object|null
$model->find_by(string $col, mixed $val): object|null
$model->all(array $where = [], string $order = 'id ASC', int $limit = 0): array
$model->paginate(int $page, int $per_page, array $where, string $search, array $search_cols): array
$model->count(array $where = []): int

// Write (all auto-log to audit_logs)
$model->create(array $data): int|false      // returns insert ID
$model->update(int $id, array $data): bool
$model->delete(int $id): bool

// Convenience
$model->set_status(int $id, string $status): bool
$model->set_sort_order(array $ordered_ids): void
```

`paginate()` returns `['items' => [...], 'meta' => ['total', 'per_page', 'current_page', 'total_pages']]`.

### Creating a new model

1. Create `models/class-myfeature-model.php`:

```php
<?php
defined('ABSPATH') || exit;

class AH_MyFeature_Model extends AH_Model_Base {

    protected string $table_suffix = 'my_things';  // → wp_ah_my_things

    public function get_active(): array {
        global $wpdb;
        $t = $this->table();
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `{$t}` WHERE status = %s ORDER BY sort_order ASC",
                'active'
            )
        ) ?: [];
    }
}
```

2. Register in `inc/class-autoloader.php`:
```php
'AH_MyFeature_Model' => 'models/class-myfeature-model.php',
```

### Image URL resolution

Images are stored as integer IDs (FK to `wp_ah_media`). Always resolve at render time:

```php
$media_m = new AH_Media_Model();
$url     = $media_m->get_url((int) $row->image_id);  // '' if ID is 0 or missing
```

Never store raw URLs — they break if files are moved.

---

## 7. Helper Classes

### AH_Slug_Helper

```php
// Auto-generate from a source string, guarantee uniqueness in a column
$slug = AH_Slug_Helper::generate('My Service Title', AH_DB_Helper::table('services'), 'slug');
// → 'my-service-title'  (or 'my-service-title-2' if taken)

// Scoped by post_type
$slug = AH_Slug_Helper::generate_post('My Post Title', 'blog', $exclude_id);
```

### AH_Pagination

```php
$page   = AH_Pagination::current_page();   // reads ?paged=N, defaults to 1
$result = $model->paginate($page, 20, [], $search);
$items  = $result['items'];
$meta   = $result['meta'];

echo AH_Pagination::render($meta);  // outputs WP-style prev/next/page links
```

### AH_Validator

Chainable validation:

```php
$v = new AH_Validator($_POST);
$v->required('title')->max_length('title', 255)
  ->required('email')->email('email')
  ->required('star_rating')->in_list('star_rating', ['1','2','3','4','5']);

if ($v->fails()) {
    $notice = $v->first_error();
}
```

Static sanitizers (always call before inserting into DB):

```php
AH_Validator::sanitize_text($val)       // sanitize_text_field
AH_Validator::sanitize_textarea($val)   // sanitize_textarea_field
AH_Validator::sanitize_html($val)       // wp_kses_post
AH_Validator::sanitize_url($val)        // esc_url_raw
AH_Validator::sanitize_int($val)        // (int)
AH_Validator::sanitize_slug($val)       // sanitize_title
AH_Validator::sanitize_color($val)      // validates hex color, returns '' on invalid
```

### AH_Uploader

```php
// Call in a POST handler that has an uploaded file
$result = AH_Uploader::upload('file');   // 'file' = $_FILES array key

if (is_wp_error($result)) {
    $error = $result->get_error_message();
} else {
    $media_id = $result;   // int ID in wp_ah_media
}
```

- Validates MIME type (jpeg, png, webp, gif, svg, pdf)
- Moves to `uploads/ah-media/YYYY/MM/filename.ext`
- Creates a row in `wp_ah_media` and returns the ID

---

## 8. Admin Portal

### URL routing

WordPress routes admin pages through the `page` GET param:

```
/wp-admin/admin.php?page=ah-services               → services.php
/wp-admin/admin.php?page=ah-services&tab=seo       → services.php, $tab = 'seo'
/wp-admin/admin.php?page=ah-services&action=edit&id=5  → edit mode
```

`AH_Admin_Menus::register()` maps each slug to a callback that calls
`self::load('template-filename')`, which simply `include`s the file from `admin/pages/`.

### Admin page template anatomy

```php
<?php
defined('ABSPATH') || exit;
if (!current_user_can('manage_options')) wp_die('Access denied.');

$model  = new AH_Services_Model();
$notice = '';
$tab    = sanitize_key($_GET['tab'] ?? 'list');

// ---------- Mutations (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && wp_verify_nonce($_POST['ah_svc_nonce'] ?? '', 'ah_save_services')) {

    if (isset($_POST['save_item'])) {
        $id = (int)($_POST['item_id'] ?? 0);
        $data = [
            'title'  => sanitize_text_field($_POST['title'] ?? ''),
            'status' => sanitize_key($_POST['status'] ?? 'active'),
        ];
        $id ? $model->update($id, $data) : $model->create($data);
        $notice = 'Saved.';
    }
}

// ---------- Deletes (GET nonce link) ----------
if (isset($_GET['delete']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'ah_del_service')) {
    $model->delete((int)$_GET['delete']);
    $notice = 'Deleted.';
}

// ---------- Fetch display data ----------
$result = $model->paginate(AH_Pagination::current_page(), 20, [], sanitize_text_field($_GET['s'] ?? ''));
$items  = $result['items'];
$meta   = $result['meta'];
$edit   = isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit'
          ? $model->find((int)$_GET['id']) : null;
?>
<div class="wrap ah-wrap">
  <h1>Services</h1>
  <?php if ($notice): ?><div class="ah-notice ah-notice-success"><?= esc_html($notice) ?></div><?php endif; ?>
  <!-- tabs, table, form -->
</div>
```

### Adding a new admin page (3 steps)

1. Create `admin/pages/my-feature.php`
2. In `admin/menus/class-admin-menus.php` add:
```php
// inside register()
add_submenu_page('ah-dashboard', 'My Feature', 'My Feature', self::$cap, 'ah-my-feature', [self::class, 'page_my_feature']);

// callback method
public static function page_my_feature() { self::load('my-feature'); }
```
3. Done — WordPress handles the rest via the `page` slug.

### Nonce reference

| Operation | Nonce action string | PHP verify |
|---|---|---|
| POST form save | `ah_save_{section}` | `wp_verify_nonce($_POST['ah_{x}_nonce'], 'ah_save_{section}')` |
| GET delete link | `ah_del_{section}` | `wp_verify_nonce($_GET['_wpnonce'], 'ah_del_{section}')` |
| All AJAX | `ah_admin_nonce` | `check_ajax_referer('ah_admin_nonce', 'nonce', false)` |

---

## 9. AJAX System

### JS side — `ahAjax()` helper

```javascript
// Defined in admin-script.js, uses jQuery $.post
ahAjax(
    { action: 'ah_toggle_status', id: 5, table: 'services', toggle_action: 'activate' },
    (data) => { /* success: update badge text */ },
    (msg)  => { alert(msg); }
);
```

`ahAdmin.ajaxUrl` and `ahAdmin.nonce` are localized from `AH_Admin_Bootstrap::enqueue_assets()`.

### PHP side — AH_Ajax_Handlers

Every handler follows the same pattern:

```php
public static function handle_toggle_status(): void {
    self::verify();                          // capability + nonce check

    $id    = (int)($_POST['id']    ?? 0);
    $table = sanitize_key($_POST['table'] ?? '');

    if (!in_array($table, self::ALLOWED_TABLES, true)) {
        wp_send_json_error(['message' => 'Invalid table.']);
    }

    // ... do work ...

    wp_send_json_success(['status' => $new_status]);
}
```

### Registered AJAX actions

**Admin-only** (registered in `init()`, requires logged-in user + `manage_options`):

| Action | Handler | Purpose |
|---|---|---|
| `ah_toggle_status` | `handle_toggle_status` | Flip active/inactive on any allowed table |
| `ah_delete_item` | `handle_delete_item` | Delete row from any allowed model |
| `ah_update_sort_order` | `handle_update_sort_order` | Reorder sortable list |
| `ah_get_media` | `handle_get_media` | Paginated media for picker modal |
| `ah_upload_media` | `handle_upload_media` | File upload → wp_ah_media |
| `ah_delete_media` | `handle_delete_media` | Delete media row + file from disk |
| `ah_mark_submission` | `handle_mark_submission` | Update contact submission status |
| `ah_save_nav_item` | `handle_save_nav_item` | Add / update nav menu item |
| `ah_delete_nav_item` | `handle_delete_nav_item` | Delete nav menu item |
| `ah_flush_rewrites` | `handle_flush_rewrites` | `flush_rewrite_rules(true)` |
| `ah_clear_transients` | `handle_clear_transients` | Delete all `_transient_*` from wp_options |
| `ah_load_demo_data` | `handle_load_demo_data` | Run CSV importer on all 7 sample files |
| `ah_clear_audit_log` | `handle_clear_audit_log` | `TRUNCATE wp_ah_audit_logs` |
| `ah_db_health_check` | `handle_db_health_check` | `SHOW TABLES LIKE` check on all required tables |
| `ah_clear_form_submissions` | `handle_clear_form_submissions` | Delete all form submission rows |
| `ah_save_static_page` | `handle_save_static_page` | Write `static/{slug}.html` + create WP page |

**Public** (registered in `init_public()`, called in `functions.php` outside `is_admin()` — available to all visitors):

| Action | Handler | Purpose |
|---|---|---|
| `ah_contact_submit` | `handle_contact_submit` | Legacy contact page form submission |
| `ah_form_submit` | `handle_form_submit` | Form Builder shortcode form submission |

`init_public()` registers both `wp_ajax_` and `wp_ajax_nopriv_` variants so the action works whether the visitor is logged in or not. It uses `ah_frontend_nonce` (not `ah_admin_nonce`) — localized via `wp_localize_script` on the frontend as `ahTheme.nonce`.

### Adding a new AJAX action

1. Add the action name to `$actions` array in `AH_Ajax_Handlers::init()`:
```php
'ah_my_action',
```

2. Add the handler (the naming convention `ah_` → `handle_` is applied automatically):
```php
public static function handle_my_action(): void {
    self::verify();
    $data = sanitize_text_field($_POST['data'] ?? '');
    // ... process ...
    wp_send_json_success(['result' => $data]);
}
```

3. Call from JS:
```javascript
ahAjax({ action: 'ah_my_action', data: 'hello' }, onSuccess, onError);
```

---

## 10. Import System

### Flow

```
User uploads CSV
    ↓
admin/pages/import.php  validates file extension + UPLOAD_ERR_OK
    ↓
AH_CSV_Importer::parse_file($tmp_path)
    → strips UTF-8 BOM
    → row 1 = headers
    → returns array of associative arrays
    ↓
AH_CSV_Importer::import($type, $rows)
    → dispatches to import_{type}()
    → validates required columns per row
    → sanitizes every field
    → $wpdb->insert() per row
    → logs to audit_logs
    ↓
Returns ['imported' => N, 'skipped' => N, 'errors' => [...]]
```

### Import types and required columns

| Type key | Required columns | Target table |
|---|---|---|
| `services` | `title` | wp_ah_services |
| `reviews` | `reviewer_name`, `review_text`, `star_rating` | wp_ah_reviews |
| `faqs` | `question`, `answer` | wp_ah_faqs |
| `posts` | `title`, `post_type` | wp_ah_posts |
| `team` | `name`, `designation` | wp_ah_team_members |
| `taxonomies` | `name`, `type_slug` | wp_ah_taxonomies |
| `news_bar` | `text` | wp_ah_news_bar_items |

### Sample files

```
admin/import/samples/
    sample-services.csv        5 service rows
    sample-reviews.csv         6 review rows
    sample-posts.csv           4 posts (blog / news / article)
    sample-faqs.csv            7 FAQ rows
    sample-team.csv            6 team member rows
    sample-taxonomies.csv      14 taxonomy terms (categories, tags, subtags)
    sample-news-bar.csv        5 ticker items
```

All sample files are linked on the import admin page and available as direct downloads.

### Adding a new import type

1. Add to `AH_CSV_Importer::get_config()`:
```php
'my_type' => [
    'label'    => 'My Things',
    'sample'   => 'sample-my-type.csv',
    'required' => ['name'],
    'columns'  => [
        'name'   => 'Name of the thing (required)',
        'status' => 'active | inactive',
    ],
],
```

2. Add a `case` to `import()`:
```php
case 'my_type': return self::import_my_type($rows);
```

3. Implement `private static function import_my_type(array $rows): array`
   — follow the existing pattern: loop, validate required, sanitize, insert, `self::result_add()`.

4. Create `admin/import/samples/sample-my-type.csv` with a header row + 3–5 example rows.

---

## 11. Admin CSS Component Library

All components live in `admin/assets/css/admin-style.css`.

### Design tokens

```css
--ah-primary:    #2563eb   /* blue — primary buttons, active tabs, links */
--ah-success:    #16a34a   /* green — success notices, active badges */
--ah-danger:     #dc2626   /* red — delete buttons, danger badges */
--ah-warning:    #d97706   /* amber — warning notices */
--ah-text:       #1e293b   /* default body text */
--ah-muted:      #64748b   /* secondary / helper text */
--ah-border:     #e2e8f0   /* borders, dividers */
--ah-bg-light:   #f8fafc   /* card / table backgrounds */
--ah-radius:     6px
--ah-shadow:     0 1px 3px rgba(0,0,0,.08)
```

### Page wrapper

```html
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-admin-home"></span> Page Title</h1>
  <!-- content -->
</div>
```

### Card

```html
<div class="ah-card">
  <div class="ah-card-header">
    <h2>Card Title</h2>
    <a href="#" class="ah-btn ah-btn-secondary ah-btn-sm">Header Action</a>
  </div>
  <!-- card body — padding is applied by .ah-card -->
</div>
```

### Table

```html
<div class="ah-table-wrap">
  <table class="ah-table ah-sortable-list" data-model="services">
    <thead>
      <tr><th></th><th>Title</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <tr data-id="1">
        <td class="ah-sort-handle">&#9776;</td>
        <td>My Service</td>
        <td><span class="ah-badge ah-badge-active">active</span></td>
        <td class="row-actions">
          <a href="?page=ah-services&action=edit&id=1" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
          <a href="#" class="ah-btn ah-btn-danger ah-btn-sm"
             data-delete-item="1" data-model="services">Delete</a>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

### Buttons

```html
<button class="ah-btn ah-btn-primary">Primary</button>
<button class="ah-btn ah-btn-secondary">Secondary</button>
<button class="ah-btn ah-btn-danger">Danger</button>
<button class="ah-btn ah-btn-primary ah-btn-sm">Small</button>
<button class="ah-btn ah-btn-primary ah-btn-icon">
  <span class="dashicons dashicons-plus"></span>
</button>
```

### Status badges

```html
<span class="ah-badge ah-badge-active">active</span>
<span class="ah-badge ah-badge-inactive">inactive</span>
<span class="ah-badge ah-badge-draft">draft</span>
<span class="ah-badge ah-badge-new">new</span>
<span class="ah-badge ah-badge-spam">spam</span>
```

### Form rows

```html
<div class="ah-form-row">
  <label>Field Label</label>
  <input type="text" name="field_name" value="<?= esc_attr($val) ?>">
  <p class="description">Helper text.</p>
</div>
```

### Tabs

```html
<div class="ah-tabs">
  <a href="?page=ah-x&tab=one" class="ah-tab active">Tab One</a>
  <a href="?page=ah-x&tab=two" class="ah-tab">Tab Two</a>
</div>
```

### Image picker

```html
<div class="ah-image-picker">
  <img src="<?= esc_url($url) ?>" class="ah-image-preview <?= $url ? 'visible' : '' ?>"
       alt="" style="width:100%;height:120px;object-fit:cover;">
  <div class="ah-image-picker-btns">
    <input type="hidden" class="ah-image-id" name="image_id" value="<?= (int)$id ?>">
    <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Image</button>
    <button type="button" class="ah-btn ah-btn-sm ah-remove-image"
            style="color:var(--ah-danger);">Remove</button>
  </div>
</div>
```

JS wires `.ah-pick-image` to the WP Media Library frame automatically.
`.ah-image-preview.visible` shows the image; without `.visible` it is hidden.

### Notices

```html
<div class="ah-notice ah-notice-success">Operation completed.</div>
<div class="ah-notice ah-notice-warning">Something went wrong.</div>
```

### Repeater (dynamic add/remove rows)

```html
<div class="ah-repeater-container">
  <div class="ah-repeater-item" style="display:flex;gap:8px;align-items:center;padding:8px;">
    <span class="ah-sort-handle">&#9776;</span>
    <input type="text" name="items[]" value="First item" style="flex:1;">
    <button type="button" class="ah-repeater-remove">&#10005;</button>
  </div>
</div>
<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-repeater">+ Add Item</button>
```

### Stats grid (dashboard cards)

```html
<div class="ah-stats-grid">
  <div class="ah-stat-card">
    <div class="ah-stat-number">42</div>
    <div class="ah-stat-label">Services</div>
  </div>
</div>
```

### Two-column layout (list + form)

```html
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
  <div><!-- list / table --></div>
  <div class="ah-card"><!-- add/edit form --></div>
</div>
```

---

## 12. Admin JavaScript Patterns

All behaviours are wired automatically by CSS selectors — no manual `init()` calls in templates.

| Selector / attribute | What JS does |
|---|---|
| `table.ah-sortable-list[data-model]` | jQuery UI Sortable → sends `ah_update_sort_order` on drag-end |
| `[data-toggle-status][data-id][data-table]` | Click → AJAX toggle → swap badge + button text |
| `[data-delete-item][data-model]` | Click → `confirm()` → AJAX delete → `fadeOut()` row |
| `.ah-image-picker .ah-pick-image` | Opens WP Media Library, sets `.ah-image-id` value + `.ah-image-preview` src |
| `.ah-image-picker .ah-remove-image` | Clears image ID to `0` and removes preview src |
| `.ah-add-repeater` | Clones last `.ah-repeater-item`, clears input values |
| `.ah-repeater-remove` | Removes closest `.ah-repeater-item` (keeps minimum 1) |
| `.ah-generate-slug-source` | On `blur` → converts value to slug → writes `.ah-slug-field` |
| `.ah-tab[data-tab]` | Client-side tab switch + `history.replaceState` (no page reload) |
| `#ah-bulk-select-all` | Master checkbox that toggles all `.ah-bulk-cb` checkboxes |
| `.wp-color-picker` | Initialized as wp-color-picker on page load |

### Inline status toggle markup

```html
<button class="ah-btn ah-btn-sm"
        data-toggle-status="1"
        data-id="<?= (int)$item->id ?>"
        data-table="services"
        data-action="<?= $item->status === 'active' ? 'deactivate' : 'activate' ?>">
  <?= $item->status === 'active' ? 'Deactivate' : 'Activate' ?>
</button>
```

When clicked: sends `ah_toggle_status` AJAX, receives new status, updates badge class and button text in the DOM without a page reload.

### Inline AJAX delete markup

```html
<a href="#"
   class="ah-btn ah-btn-danger ah-btn-sm"
   data-delete-item="<?= (int)$item->id ?>"
   data-model="services">Delete</a>
```

When clicked: shows `confirm()`, sends `ah_delete_item` AJAX, fades out the `<tr>` on success.

---

## 13. Security Rules

These are non-negotiable for every line of code in this project.

### Input sanitization (before DB / processing)

```php
sanitize_text_field($_POST['title'] ?? '')          // single-line text
sanitize_textarea_field($_POST['bio'] ?? '')         // multi-line plain text
wp_kses_post($_POST['content'] ?? '')               // rich HTML from wp_editor
esc_url_raw($_POST['link_url'] ?? '')               // URLs
(int)($_POST['sort_order'] ?? 0)                    // integers
sanitize_key($_POST['status'] ?? '')                // ENUM-style values
sanitize_title($_POST['slug'] ?? '')                // slugs
sanitize_email($_POST['email'] ?? '')               // emails
```

### Output escaping (before echo)

```php
echo esc_html($row->title);              // plain text in HTML
echo esc_attr($row->slug);              // inside HTML attributes
echo esc_url($row->link_url);           // inside href / src
echo esc_textarea($row->bio);           // inside <textarea>
echo wp_kses_post($row->content);       // trusted HTML content
```

### SQL — always prepared

```php
$row = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM `{$table}` WHERE slug = %s", $slug)
);
```

### Nonces — always verify before mutation

```php
// POST form
if (!wp_verify_nonce($_POST['ah_svc_nonce'] ?? '', 'ah_save_services')) {
    wp_die('Security check failed.');
}

// GET delete link
if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'ah_del_service')) {
    wp_die('Security check failed.');
}
```

### Capability check — first line of every admin page and AJAX handler

```php
if (!current_user_can('manage_options')) wp_die('Access denied.');
```

---

## 14. Naming Conventions

| Thing | Rule | Example |
|---|---|---|
| PHP class | `AH_` prefix + PascalCase | `AH_CSV_Importer` |
| PHP method | snake_case | `import_services()` |
| PHP class file | `class-` + kebab-case | `class-csv-importer.php` |
| DB table | `wp_ah_` + snake_case | `wp_ah_home_why_us_cards` |
| Nonce action | `ah_` + snake_case | `ah_save_services`, `ah_del_service` |
| AJAX action | `ah_` + snake_case | `ah_toggle_status` |
| Admin page slug | `ah-` + kebab | `ah-client-stories` |
| CSS component class | `.ah-` + BEM-lite | `.ah-btn`, `.ah-btn-primary`, `.ah-card-header` |
| JS localized object | camelCase | `ahAdmin.ajaxUrl`, `ahAdmin.nonce` |

---

## 15. Recipes — How to Add New Features

### Recipe A: New content type end-to-end

Example: "Testimonial Videos"

**Step 1 — DB table** (`database/class-db-installer.php`):
```php
$wpdb->query("CREATE TABLE IF NOT EXISTS `{$p}ah_testimonial_videos` (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title     VARCHAR(255) NOT NULL DEFAULT '',
    video_url VARCHAR(500) NOT NULL DEFAULT '',
    sort_order SMALLINT    NOT NULL DEFAULT 0,
    status    ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
```

**Step 2 — Model** (`models/class-testimonial-videos-model.php`):
```php
class AH_Testimonial_Videos_Model extends AH_Model_Base {
    protected string $table_suffix = 'testimonial_videos';
}
```

**Step 3 — Autoloader** (`inc/class-autoloader.php`):
```php
'AH_Testimonial_Videos_Model' => 'models/class-testimonial-videos-model.php',
```

**Step 4 — Admin page** (`admin/pages/testimonial-videos.php`) — follow the anatomy in §8.

**Step 5 — Menu** (`admin/menus/class-admin-menus.php`):
```php
add_submenu_page('ah-dashboard', 'Testimonial Videos', 'Testimonial Videos',
    self::$cap, 'ah-testimonial-videos', [self::class, 'page_testimonial_videos']);

public static function page_testimonial_videos() { self::load('testimonial-videos'); }
```

**Step 6 — AJAX** — add allowed table/model names to `handle_toggle_status()` and
`handle_delete_item()` whitelists in `admin/ajax/class-ajax-handlers.php`.

**Step 7 — Import** — add type to `AH_CSV_Importer::get_config()`, implement the method, add sample CSV.

---

### Recipe B: New site setting

In `database/class-db-installer.php` seeding block:
```php
$wpdb->query("INSERT IGNORE INTO `{$p}ah_site_settings`
    (setting_key, setting_value, field_type, group_name, label, sort_order)
    VALUES ('my_setting', '', 'text', 'general', 'My Setting Label', 99)");
```

Read anywhere:
```php
$val = (new AH_Settings_Model())->get_value('my_setting');
```

---

### Recipe C: New AJAX-powered sortable list

Add `class="ah-sortable-list" data-model="my_things"` to the `<table>`.
Then add `'my_things'` to `$allowed_models` in `handle_update_sort_order()`.
The JS wires itself automatically.

---

### Recipe D: Inline status toggle for a new table

HTML — see §12 for the markup.
PHP — add `'my_things'` to `$allowed_tables` in `handle_toggle_status()`.

---

### Recipe E: Rich text field (wp_editor)

```php
wp_editor(
    wp_kses_post($row->content ?? ''),
    'content_field_id',
    [
        'textarea_name' => 'content',
        'media_buttons' => true,
        'teeny'         => false,
        'textarea_rows' => 12,
    ]
);
```

On save: `$content = wp_kses_post($_POST['content'] ?? '');`

`wp_editor()` echoes immediately — place it exactly where you want it in the form.

---

## 16. Common Gotchas

**1. `enqueue_thickbox()` does not exist**
The correct function is `add_thickbox()`. `enqueue_thickbox()` causes a fatal error.
Fixed in `admin/class-admin-bootstrap.php:36`.

**2. Forgetting the autoloader map**
Adding a class file but not its entry in `inc/class-autoloader.php` triggers a fatal
"Class not found" on first use. Always register class + file together.

**3. Nonce type mismatch — GET vs POST**
GET delete links use `wp_nonce_url()` / `$_GET['_wpnonce']`.
POST forms use `wp_nonce_field()` / `$_POST['ah_nonce']`.
Never mix them.

**4. Foreign key creation order**
All tables must exist before FK constraints are added. Use `SET FOREIGN_KEY_CHECKS = 0`
during CREATE TABLE phase. Never embed FK constraints inside `CREATE TABLE` in this project.

**5. Storing image URLs instead of IDs**
Always store `image_id` (int FK to `wp_ah_media`) and resolve the URL at render time.
Raw URLs break when files are moved or the site domain changes.

**6. `$wpdb->update()` returns `0` on no-change — not an error**
```php
$result = $wpdb->update($table, $data, $where);
if (false === $result) { /* real DB error */ }
// 0 just means nothing changed — this is normal
```

**7. `wp_editor()` echoes, not returns**
Do not try to capture its output with `ob_start()`. Place the call inline in the form.

**8. UTF-8 BOM in CSV files from Excel**
Excel saves UTF-8 CSVs with a 3-byte BOM (`\xEF\xBB\xBF`). The parser strips it.
Do not remove that code from `AH_CSV_Importer::parse_file()`.

**9. `sanitize_key()` vs `sanitize_title()`**
- `sanitize_key()` → lowercase, numbers, underscores, hyphens — use for status values, AJAX actions
- `sanitize_title()` → full slug processing — use for URL slugs

**10. Always guard on `$page_id === 0`**
Several admin pages (services, reviews, client-stories, etc.) require a CMS page record.
All of them check `if (!$page_id) { show warning; return; }` at the top.
Follow this pattern in any new page that depends on `wp_ah_pages`.

---

## 17. Full DB Table List

| Table suffix | Full name | Purpose |
|---|---|---|
| `pages` | wp_ah_pages | CMS page registry |
| `page_sections` | wp_ah_page_sections | Section visibility per page |
| `site_settings` | wp_ah_site_settings | Key-value settings store |
| `admin_roles` | wp_ah_admin_roles | CMS role definitions |
| `admin_users` | wp_ah_admin_users | CMS users (extends WP users) |
| `media` | wp_ah_media | Uploaded file registry |
| `posts` | wp_ah_posts | Blog / news / article content |
| `services` | wp_ah_services | Service entries |
| `reviews` | wp_ah_reviews | Client reviews |
| `faqs` | wp_ah_faqs | FAQ entries |
| `team_members` | wp_ah_team_members | Team member profiles |
| `taxonomies` | wp_ah_taxonomies | Tags / categories |
| `taxonomy_types` | wp_ah_taxonomy_types | Taxonomy type definitions |
| `news_bar_items` | wp_ah_news_bar_items | Ticker / news bar items |
| `contact_submissions` | wp_ah_contact_submissions | Contact form entries |
| `contact_config` | wp_ah_contact_config | Contact page settings |
| `audit_logs` | wp_ah_audit_logs | All create/update/delete events |
| `home_hero` | wp_ah_home_hero | Home hero section |
| `home_highlights` | wp_ah_home_highlights | Home highlights row |
| `home_why_us` | wp_ah_home_why_us | Why Us section header |
| `home_why_us_cards` | wp_ah_home_why_us_cards | Why Us feature cards |
| `home_guide` | wp_ah_home_guide | Guide section header |
| `home_guide_points` | wp_ah_home_guide_points | Guide bullet points |
| `home_stack_items` | wp_ah_home_stack_items | Tech stack / partner logos |
| `home_difference` | wp_ah_home_difference | Difference section header |
| `home_difference_rows` | wp_ah_home_difference_rows | Comparison table rows |
| `home_experience` | wp_ah_home_experience | Experience section header |
| `home_experience_cards` | wp_ah_home_experience_cards | Experience metric cards |
| `home_why_req` | wp_ah_home_why_req | Why Required section header |
| `home_why_req_cards` | wp_ah_home_why_req_cards | Why Required cards |
| `home_featured` | wp_ah_home_featured | Featured section header |
| `home_featured_items` | wp_ah_home_featured_items | Featured items |
| `about_page_header` | wp_ah_about_page_header | About page header |
| `about_story` | wp_ah_about_story | Our Story section |
| `about_story_points` | wp_ah_about_story_points | Story bullet points |
| `about_values` | wp_ah_about_values | Value cards |
| `services_page_header` | wp_ah_services_page_header | Services listing header |
| `service_bullet_points` | wp_ah_service_bullet_points | Per-service bullet points |
| `reviews_page_header` | wp_ah_reviews_page_header | Reviews listing header |
| `faqs_page_header` | wp_ah_faqs_page_header | FAQ listing header |
| `posts_listing_header` | wp_ah_posts_listing_header | Blog listing header |
| `post_links` | wp_ah_post_links | Links attached to a post |
| `post_taxonomies` | wp_ah_post_taxonomies | Post ↔ taxonomy pivot |
| `client_stories_header` | wp_ah_client_stories_header | Client stories page header |
| `client_gallery` | wp_ah_client_gallery | Gallery images |
| `client_video_links` | wp_ah_client_video_links | Video embed links |
| `footer_config` | wp_ah_footer_config | Footer global settings |
| `footer_contact_links` | wp_ah_footer_contact_links | Footer contact items |
| `footer_social_links` | wp_ah_footer_social_links | Social media links |
| `floating_widgets` | wp_ah_floating_widgets | Floating CTA buttons |
| `client_users_journey` | wp_ah_client_users_journey | (reserved / future) |
| `client_story_images` | wp_ah_client_story_images | (reserved / future) |
| `file_links` | wp_ah_file_links | Uploaded file registry for shareable links |
| `forms` | wp_ah_forms | Form Builder — form definitions |
| `form_fields` | wp_ah_form_fields | Form Builder — field definitions (JSON options) |
| `form_submissions` | wp_ah_form_submissions | Form Builder — visitor submissions (JSON data) |

---

## 18. File Links System

**Admin page:** CMS Portal → File Links (`ah-file-links`)

**Purpose:** Upload any file and instantly get a publicly shareable URL you can paste
anywhere — emails, page content, navigation menus, CTAs.

### How it works

```
Admin uploads file
    ↓
POST handler validates UPLOAD_ERR_OK
    ↓
wp_unique_filename() avoids collisions
    ↓
move_uploaded_file() → uploads/ah-files/YYYY/MM/filename.ext
    ↓
Row inserted into wp_ah_file_links
    ↓
Table shows the file URL with copy button
```

### DB table: wp_ah_file_links

Auto-created on the first page visit (`CREATE TABLE IF NOT EXISTS`) — no theme reactivation needed.

| Column | Type | Purpose |
|---|---|---|
| id | INT UNSIGNED | Primary key |
| original_name | VARCHAR(255) | Original uploaded filename (sanitized) |
| stored_name | VARCHAR(255) | Actual filename on disk (unique) |
| file_path | VARCHAR(500) | Path relative to uploads/ah-files/ (e.g. 2025/05/file.pdf) |
| mime_type | VARCHAR(150) | Detected MIME type |
| file_size | BIGINT | File size in bytes |
| uploaded_by | INT UNSIGNED | WP user ID |
| created_at | DATETIME | Upload timestamp |

### File storage location

```
wp-content/uploads/ah-files/
    index.php        ← directory listing block (auto-created)
    2025/
        05/
            my-document.pdf
            presentation.pptx
```

URL pattern: `{site_url}/wp-content/uploads/ah-files/YYYY/MM/stored-name.ext`

### Getting the URL in code

```php
$upload = wp_upload_dir();
$url    = trailingslashit($upload['baseurl']) . 'ah-files/' . $row->file_path;
```

### Accepted file types

All file types accepted — whatever PHP's `upload_max_filesize` and `post_max_size`
allow. MIME type is detected via `wp_check_filetype()`.

### Copy button

Uses `navigator.clipboard.writeText()` with an `execCommand('copy')` fallback for
non-HTTPS / older browsers. The clipboard icon swaps to a checkmark for 2 seconds on success.

### Deleting a file

Delete link uses a GET nonce (`ah_del_file_link`). On confirm:
1. Removes file from disk with `unlink()`
2. Deletes the DB row
3. Logs the action to `wp_ah_audit_logs`

If the file is missing on disk, the row still shows in the table with a ⚠ warning — you
can still delete the orphan record via the delete button.

---

## 19. Dynamic Form Builder System

**Admin page:** CMS Portal → Form Builder (`ah-form-builder`)

**Purpose:** Build reusable contact/inquiry forms with a drag-and-drop field editor,
embed them anywhere via a shortcode, capture visitor submissions to the DB,
and get email notifications on every submission.

### How it works — end-to-end flow

```
Admin builds form in CMS Portal → Form Builder
    ↓
Creates form record + ordered field definitions (stored as rows in wp_ah_form_fields)
    ↓
Admin copies [ah_form id="N"] shortcode
    ↓
Shortcode pasted into any WP page or PHP template via do_shortcode()
    ↓
AH_Form_Builder::render() outputs self-contained HTML (inline CSS + vanilla JS fetch)
    ↓
Visitor fills form + submits → fetch() POST to admin-ajax.php?action=ah_form_submit
    ↓
AH_Ajax_Handlers::handle_form_submit():
    → nonce check (ah_frontend_nonce)
    → honeypot check (ah_hp field)
    → validate required fields
    → AH_Form_Builder::submit() saves JSON to wp_ah_form_submissions
    → wp_mail() sends email to form's notify_email
    → wp_send_json_success(['message' => $form->success_message])
    ↓
JS swaps form with success message
```

### Core class: AH_Form_Builder (`inc/class-form-builder.php`)

All methods are static. No constructor.

```php
AH_Form_Builder::install_tables()          // CREATE TABLE IF NOT EXISTS (idempotent — called on every admin page load)
AH_Form_Builder::create(array $data)       // Insert wp_ah_forms row, return int ID
AH_Form_Builder::get(int $id)             // Fetch single form object
AH_Form_Builder::all()                    // All forms as array
AH_Form_Builder::save_fields(int $form_id, array $fields)  // DELETE all + re-insert (clean slate)
AH_Form_Builder::get_fields(int $form_id) // Ordered field rows for a form
AH_Form_Builder::submit(int $form_id, array $data)         // Insert JSON submission row
AH_Form_Builder::get_submissions(int $form_id)             // All submissions for a form
AH_Form_Builder::render(array $atts)      // Shortcode renderer — returns full HTML string
```

### DB Tables (auto-created — no theme reactivation needed)

```sql
wp_ah_forms
    id              INT UNSIGNED AUTO_INCREMENT
    name            VARCHAR(255)         — admin label for the form
    notify_email    VARCHAR(255)         — where to send submission emails
    success_message TEXT                 — shown to visitor after submit
    status          ENUM('active','inactive')
    created_at      DATETIME

wp_ah_form_fields
    id              INT UNSIGNED AUTO_INCREMENT
    form_id         INT UNSIGNED         — FK to wp_ah_forms.id
    label           VARCHAR(255)         — visible field label
    field_key       VARCHAR(100)         — snake_case key (auto-generated from label)
    field_type      ENUM('text','email','tel','textarea','select','number','date','url')
    placeholder     VARCHAR(255)
    options         JSON                 — array of option strings (select type only)
    is_required     TINYINT(1)
    sort_order      SMALLINT

wp_ah_form_submissions
    id              INT UNSIGNED AUTO_INCREMENT
    form_id         INT UNSIGNED
    data            JSON                 — {field_key: value, ...}
    ip_address      VARCHAR(45)
    created_at      DATETIME
```

### Field key auto-generation

`field_key` is never entered manually. It is auto-generated from `label` at save time:

```php
private static function to_key(string $label): string {
    return str_replace('-', '_', sanitize_title($label));
}
// "Full Name" → "full_name"
// "Email Address" → "email_address"
// "Phone Number" → "phone_number"
```

### Shortcode usage

```php
// In a page template:
echo do_shortcode('[ah_form id="1"]');

// In WP page content (editor):
[ah_form id="1"]
```

The rendered form is fully self-contained — inline `<style>` + `<script>` blocks are
included in the returned HTML string. No extra stylesheet or JS file needs to be enqueued.

### Admin UI (`admin/pages/form-builder.php`)

- **Form selector** dropdown at top — switches active form
- **+ New Form** button — creates blank form record
- **Build Form tab:**
  - Form settings card: name, notify_email, success_message, status
  - Fields table: drag handle (⠿), label, type dropdown, placeholder, options textarea (select only), required checkbox, delete row
  - `+ Add Field` button clones `<template id="fb-row-tpl">`
  - jQuery UI Sortable on `#fb-body` for drag reorder
  - On save: JS serializes all rows to JSON → `input[name="fields_json"]` → PHP `json_decode()` → `save_fields()`
- **Submissions tab:**
  - Table listing all submissions for the active form
  - Each row has a ▶ toggle that expands a detail sub-row showing all field values in a CSS grid

### Shortcode nonce strategy

The shortcode `render()` method calls `wp_nonce_field('ah_frontend_nonce', 'nonce')` inside the form.
The AJAX handler verifies with `check_ajax_referer('ah_frontend_nonce', 'nonce', false)`.
This is separate from the admin nonce (`ah_admin_nonce`) — intentionally, because the
frontend nonce must be accessible to non-logged-in visitors.

### Spam protection

A honeypot field is injected into every form:

```html
<div style="position:absolute;left:-9999px;aria-hidden:true">
  <input type="text" name="ah_hp" value="" tabindex="-1" autocomplete="off">
</div>
```

The handler silently returns success (without saving) if `ah_hp` is non-empty:

```php
if (!empty($_POST['ah_hp'])) {
    wp_send_json_success(['message' => 'Thank you!']); // bot trap
}
```

### Adding a new field type

1. Add the ENUM value to `wp_ah_form_fields.field_type` (or rely on default text handling).
2. In `render()`, add a case to the field-rendering switch:
```php
case 'my_type':
    $html .= '<input type="my_type" name="' . esc_attr($field->field_key) . '" ...>';
    break;
```
3. In the admin UI `<template id="fb-row-tpl">`, add the option to the `<select>` for field type.

### Accessing submission data

```php
$submissions = AH_Form_Builder::get_submissions($form_id);
foreach ($submissions as $sub) {
    $data = json_decode($sub->data, true); // ['full_name' => 'John', 'email' => '...']
    echo $data['full_name'];
}
```

---

## 20. Admin Actions System

**Admin page:** CMS Portal → Admin Actions (`ah-admin-actions`)

**Purpose:** One-click utility operations for maintenance, diagnostics, and testing.
Each action fires via jQuery AJAX and shows an inline result — no page reload.

### Available actions

| Button | AJAX Action | What it does |
|---|---|---|
| Flush Rewrite Rules | `ah_flush_rewrites` | `flush_rewrite_rules(true)` — regenerates WP permalink rules |
| Clear Transients | `ah_clear_transients` | DELETEs all `_transient_*` and `_site_transient_*` rows from `wp_options` |
| Load Demo Data | `ah_load_demo_data` | Runs `AH_CSV_Importer` on all 7 sample CSV files; reports imported/skipped per type |
| DB Health Check | `ah_db_health_check` | `SHOW TABLES LIKE` on every required `wp_ah_*` table; reports missing ones |
| Clear Audit Log | `ah_clear_audit_log` | `TRUNCATE wp_ah_audit_logs` — irreversible, protected by `window.confirm()` |
| Clear Form Submissions | `ah_clear_form_submissions` | `DELETE FROM wp_ah_form_submissions` — irreversible, confirm-gated |

### Inline result pattern

```html
<button class="ah-btn ah-btn-primary ah-action-btn" data-action="ah_flush_rewrites">Run</button>
<div class="ah-action-result"></div>  <!-- hidden; shown as .ok or .err on response -->
```

Destructive actions add `data-confirm="Are you sure?"` — JS calls `window.confirm()` before firing.

### Adding a new action card

1. Add `data-action="ah_my_action"` button + `.ah-action-result` div in `admin/pages/admin-actions.php`
2. Add `'ah_my_action'` to the `$actions` array in `AH_Ajax_Handlers::init()`
3. Implement `public static function handle_my_action(): void` following the `self::verify()` → do work → `wp_send_json_success(['message' => '...'])` pattern

---

## 21. Static HTML Pages System

**Admin page:** CMS Portal → Static Pages (`ah-static-pages`)

**Purpose:** Upload raw HTML files and serve them as WordPress pages with complete
style isolation — the active theme's CSS cannot reach inside the static content.

### Architecture

```
Admin pastes HTML in editor → Save button
    ↓
ah_save_static_page AJAX handler
    → validates slug (lowercase, hyphens only)
    → realpath() path-traversal check
    → file_put_contents(static/{slug}.html, $html)
    → get_page_by_path($slug): exists? → set template meta
                              missing? → wp_insert_post() + set template meta
    ↓
WordPress page at /{slug}/ created with Template: Static HTML Page
    ↓
Visitor requests /{slug}/
    ↓
template-static-page.php loads
    → ?raw=1? → readfile(static/{slug}.html) → exit   (bare HTML, no WP wrapper)
    → normal  → get_header() + <iframe src="?raw=1"> + resize JS + get_footer()
```

### CSS isolation mechanism

The `<iframe src="?raw=1">` loads the HTML from the same origin (same domain), so:
- The parent page's CSS cannot reach inside the iframe
- The iframe content has no theme stylesheet applied at all
- JavaScript can still resize the iframe via `iframe.contentDocument.documentElement.scrollHeight`
- `sandbox="allow-scripts allow-same-origin allow-forms allow-popups"` permits normal interaction while blocking cross-origin escalation

### File structure

```
ah_final_theme/
├── static/
│   ├── .gitkeep               directory marker
│   ├── privacy-policy.html    → served at /privacy-policy/
│   ├── terms.html             → served at /terms/
│   └── embedded-tool.html     → served at /embedded-tool/
└── template-static-page.php   WordPress page template
```

File naming rule: slug must be lowercase letters, numbers, hyphens only. The template reads `static/{page-slug}.html`.

### Admin UI (`admin/pages/static-pages.php`)

- **Sidebar** — lists all `.html` files in `static/`; Edit link opens in editor; View link opens the front-end URL
- **Editor** — monospace textarea with full HTML; Save writes the file + creates/updates WP page
- **New Page** — `+ New Page` button shows slug input field; after save, redirects to edit mode for that slug
- **WP page auto-creation** — `wp_insert_post()` is called if no page with that slug exists; template meta is always set to `template-static-page.php`

### Using a static page as an iframe src elsewhere

The `?raw=1` URL serves pure HTML — no WordPress shell at all. Use it as an `src` for an `<iframe>` on any other page:

```html
<iframe
  src="https://yoursite.com/privacy-policy/?raw=1"
  sandbox="allow-scripts allow-same-origin"
  style="width:100%;border:none;"
></iframe>
```

### Adding to the navigation menu

Static pages are standard WordPress pages — add them via **WP Admin → Appearance → Menus** like any other page.

### Security notes

- `ah_save_static_page` handler: slug is stripped to `[a-z0-9-]` only before use as a filename
- `realpath()` check ensures the resolved path is inside the `static/` directory (prevents `../` traversal)
- `manage_options` capability required — only WP admins can write files

---

## 22. Plugin Mode & Deployment

See `deploy.md` in the project root for the full deployment guide.

### Key points

**Two modes — same codebase:**

| Mode | When | Bootstrap |
|---|---|---|
| Plugin + Companion Theme | Recommended for new projects | `ah-cms.php` boots everything; companion theme's `functions.php` calls `AH_Asset_Loader::init()` only |
| Standalone Theme | Backward-compat; theme-only installs | `functions.php` runs full bootstrap |

**Detection:** `functions.php` checks `defined('AH_PLUGIN_DIR')` at the top. If true (plugin active), it skips constants + admin bootstrap and returns early.

**Asset loading:** `AH_Asset_Loader::frontend_assets()` always uses `get_template_directory_uri()` — this resolves to the **active theme**, not the plugin directory. This is the critical line that makes plugin mode work.

**Backward-compat constants:** All 60+ existing files reference `AH_THEME_DIR` and `AH_THEME_URL`. In plugin mode these are aliased to `AH_PLUGIN_DIR` / `AH_PLUGIN_URL`, so nothing breaks.

### Using models from a companion theme

```php
// Read any data in a front-end template
$hero     = (new AH_Home_Model())->get_hero();
$services = (new AH_Services_Model())->get_active();
$reviews  = (new AH_Reviews_Model())->get_active();
$settings = (new AH_Settings_Model())->get_all_by_group('general');

// Resolve image URL from stored media ID
$media_m = new AH_Media_Model();
$url     = $media_m->get_url((int) $hero->image_id);
```

All model classes are autoloaded by the plugin — no `require_once` needed in the theme.
