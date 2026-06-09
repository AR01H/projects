# WordPress Basics - Complete Developer Reference
<!-- Written for the ah_advaithhomes project - covers everything from scratch -->
<!-- Last updated: 2026-05-17 -->

---

## Table of Contents

1. [What Is WordPress?](#1-what-is-wordpress)
2. [What Is a Theme?](#2-what-is-a-theme)
3. [What Is a Plugin?](#3-what-is-a-plugin)
4. [How WordPress Loads a Page](#4-how-wordpress-loads-a-page)
5. [Adding a Page Template](#5-adding-a-page-template)
6. [Adding a Custom Page Header (Hero)](#6-adding-a-custom-page-header-hero)
7. [Adding Styles (CSS)](#7-adding-styles-css)
8. [Adding Counts - DB Rows, Post Counts, Option Checks](#8-adding-counts)
9. [How to Create a Plugin from Scratch](#9-how-to-create-a-plugin-from-scratch)
10. [Plugin Anatomy - File by File](#10-plugin-anatomy)
11. [Adding Admin Pages to a Plugin](#11-adding-admin-pages-to-a-plugin)
12. [Saving & Reading Data in a Plugin](#12-saving--reading-data-in-a-plugin)
13. [Plugin Database Tables](#13-plugin-database-tables)
14. [Plugin AJAX Actions](#14-plugin-ajax-actions)
15. [Plugin Hooks - actions & filters](#15-plugin-hooks)
16. [Security Checklist](#16-security-checklist)
17. [WordPress Functions You Must Know](#17-wordpress-functions-you-must-know)
18. [Common Mistakes & How to Fix Them](#18-common-mistakes)

---

## 1. What Is WordPress?

WordPress is a **PHP application** that:
- Connects to a MySQL database
- Loads your theme templates and fills them with content
- Runs a full admin dashboard for managing content
- Lets you extend everything via plugins

When a visitor hits your URL, PHP runs → WordPress loads → it finds the right template → outputs HTML.

### Core concepts

| Term        | What it means                                                  |
|-------------|----------------------------------------------------------------|
| Theme       | The set of PHP + CSS + JS files that control how the site looks |
| Plugin      | A PHP file (or folder) that adds new features to WordPress     |
| Post        | A piece of content - blog post, page, product, etc.           |
| Option      | A key-value pair stored in `wp_options` (settings, config)    |
| Hook        | A named event where WordPress lets you inject your own code    |
| Nonce       | A security token for forms and AJAX requests                   |
| WP_Query    | The class that fetches posts from the database                 |

---

## 2. What Is a Theme?

A theme is a folder inside `wp-content/themes/` with at least:

```
my-theme/
├── style.css       ← Required. Contains theme metadata in a comment block.
└── index.php       ← Required. The last-resort fallback template.
```

### style.css theme header

The comment block at the top of `style.css` registers the theme with WordPress:

```css
/*
Theme Name: My Theme
Theme URI: https://example.com/my-theme
Author: Your Name
Version: 1.0.0
Description: A custom theme for my project.
Text Domain: my-theme
*/
```

### How a theme controls the page

When a visitor loads a URL, WordPress checks the URL, figures out what type of content
it is, and looks for a template file in your theme:

| URL / content type       | Template file WordPress looks for |
|--------------------------|-----------------------------------|
| Homepage (static page)   | `front-page.php` → `page.php`     |
| Any page (e.g. /about/)  | `page-about.php` → `page.php`     |
| Blog post                | `single.php`                      |
| Blog listing             | `home.php` → `index.php`          |
| Category archive         | `archive.php`                     |
| 404 error                | `404.php`                         |

The template then calls `get_header()` and `get_footer()` to wrap the content
in the site's navigation and footer.

---

## 3. What Is a Plugin?

A plugin is a PHP file (or folder of files) in `wp-content/plugins/`.
WordPress finds it by reading the comment block at the top of the main file.

**A plugin can:**
- Add new database tables
- Add new admin menu pages
- Intercept WordPress events (hooks) to run your code
- Add AJAX endpoints
- Add shortcodes, widgets, REST routes
- Modify how WordPress behaves (filters)

**When to use a plugin vs a theme:**
- Theme → controls how the site **looks**
- Plugin → adds **functionality** that works regardless of the active theme

### Plugin vs theme - real example

In this project:
- `ah_advaithhomes` (theme) - templates, CSS, blog layout, page templates
- `ah_cms_plugin` (plugin) - database tables, admin CMS portal, AJAX handlers, data models

If you swap the theme, the plugin still works. The plugin's data still exists.

---

## 4. How WordPress Loads a Page

Understanding this order stops 90% of "where does this even run?" confusion.

```
1. WordPress core loads (wp-settings.php)
2. All active plugins load (their main PHP file is included)
3. The active theme's functions.php loads
4. WordPress parses the URL to decide what content type it is
5. The 'wp' hook fires - all registered callbacks run
6. The 'template_redirect' hook fires - template file is selected
7. The correct template file is included (e.g. page.php)
8. The template calls get_header() → header.php renders
9. The template renders its content
10. The template calls get_footer() → footer.php renders
11. wp_footer() fires - any footer scripts/styles output
12. HTML is sent to the browser
```

**Key rule:** Code in `functions.php` or plugin files runs BEFORE any template.
You register hooks there; the hooks fire later during the correct step.

---

## 5. Adding a Page Template

A page template is a PHP file that WordPress uses for a specific page. There are two ways:

### Method A - slug-based (automatic)

Name the file `page-{slug}.php`. WordPress uses it automatically
for any page whose slug matches.

```
page-blog.php      → used for /blog/ automatically
page-contact.php   → used for /contact/ automatically
page-services.php  → used for /services/ automatically
```

No configuration needed in WordPress admin.

### Method B - Template Name comment (selectable)

Add this comment at the top of any PHP file:

```php
<?php
/**
 * Template Name: My Custom Layout
 */
get_header();
?>
<!-- your HTML here -->
<?php get_footer(); ?>
```

WordPress reads this and adds "My Custom Layout" to the Template dropdown
in Pages → Edit → Page Attributes.

To use it:
1. Go to **Pages → Add New** (or edit existing)
2. On the right sidebar under **Page Attributes → Template**, select "My Custom Layout"
3. Publish / Update

### Full page template boilerplate

```php
<?php
/**
 * Template Name: Team Page
 */
get_header();                        // outputs <html>, <head>, <nav>, news ticker

$team = ah_get_team();               // get data
?>

<!-- ── Hero ────────────────────────────────────────────────── -->
<section class="page-hero page-hero--sm">
  <div class="container">
    <div class="page-hero__copy text-center" data-aos="fade-up">
      <span class="section__eyebrow">Who We Are</span>
      <h1 class="page-hero__title">Meet the <em>Team</em></h1>
    </div>
  </div>
</section>

<!-- ── Content ──────────────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="grid-4">
      <?php foreach ( $team as $member ) : ?>
      <div class="sidebar-card" data-aos="fade-up">
        <div class="sidebar-card__title"><?php echo esc_html( $member->name ); ?></div>
        <p><?php echo esc_html( $member->role ?? '' ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php get_template_part( 'components/cta-section' ); ?>  <!-- reusable CTA band -->
<?php get_footer(); ?>               // outputs footer, closing tags, wp_footer()
```

---

## 6. Adding a Custom Page Header (Hero)

The "page hero" is the top section of a page - usually contains a title, subtitle, and eyebrow label.

### The CSS classes to use

```php
<section class="page-hero page-hero--sm" aria-label="Section label">
  <div class="container">
    <div class="page-hero__copy text-center" style="max-width:640px;margin-inline:auto" data-aos="fade-up">
      <span class="section__eyebrow">Free Resources</span>
      <h1 class="page-hero__title">The Complete<br><em>Home Buying Library</em></h1>
      <p class="page-hero__desc">
        A longer subtitle description here. Keep it to 1-2 sentences.
      </p>
      <!-- Optional CTA buttons -->
      <div style="display:flex;gap:12px;justify-content:center;margin-top:24px">
        <a href="/contact/" class="btn btn-primary">Book a Free Call →</a>
        <a href="/services/" class="btn btn-outline">Our Services</a>
      </div>
    </div>
  </div>
</section>
```

### Variants

| Class added to `<section>` | Effect                           |
|----------------------------|----------------------------------|
| `page-hero--sm`            | Smaller top/bottom padding       |
| `page-hero--centered`      | Text centered (same as text-center) |
| *(nothing extra)*          | Full-size hero with large padding |

### Hero with left text + right image (2-column)

```php
<section class="page-hero">
  <div class="container">
    <div class="page-hero__inner">
      <div class="page-hero__copy">
        <span class="section__eyebrow">Eyebrow Label</span>
        <h1 class="page-hero__title">Main Title <em>Here</em></h1>
        <p class="page-hero__desc">Subtitle text.</p>
        <a href="/contact/" class="btn btn-primary">Primary CTA →</a>
      </div>
      <div class="page-hero__media">
        <img src="..." alt="..." style="width:100%;border-radius:var(--r-lg)">
      </div>
    </div>
  </div>
</section>
```

### Reusable hero component

For the homepage hero, use the built-in component:

```php
get_template_part( 'components/hero' );          // uses data from ah_get_home_settings()

// Or pass your own args:
get_template_part( 'components/hero', null, [
    'headline'  => 'Custom <em>Heading</em>',
    'subline'   => 'Subtitle here.',
    'cta_label' => 'Book Now',
    'cta_url'   => '/contact/',
] );
```

### Section header (not a full hero - used mid-page)

```php
<div class="section__header text-center">
  <span class="section__eyebrow">Small Label</span>
  <h2 class="section__title">Section Heading</h2>
  <p class="section__desc" style="margin-inline:auto">Description text, keep it short.</p>
</div>
```

---

## 7. Adding Styles (CSS)

### Where styles live

```
assets/css/
├── variables.css   ← Design tokens (colours, spacing, font vars)
├── base.css        ← Reset, typography
├── components.css  ← Named components (nav, cards, buttons…)
├── layout.css      ← Page layout, sidebar, grid offsets
├── forms.css       ← Inputs, labels, validation
└── animations.css  ← AOS, transitions, hover effects
```

They are all enqueued in `functions.php` via `wp_enqueue_style()`.

### Step 1 - Add a CSS variable (design token)

Open `assets/css/variables.css`, find the `:root {}` block, add your token:

```css
:root {
    /* existing... */
    --highlight-bg: #fef3c7;
    --highlight-border: #fde68a;
}
```

Use it anywhere: `background: var(--highlight-bg);`

### Step 2 - Add a new component style

Open `assets/css/components.css`, scroll to the bottom, add:

```css
/* ── Highlight Box ──────────────────────────────────────────── */
.highlight-box {
    background: var(--highlight-bg);
    border: 1.5px solid var(--highlight-border);
    border-radius: var(--r-lg);
    padding: 20px 24px;
    margin: 24px 0;
}
.highlight-box__title {
    font-weight: 700;
    font-size: .95rem;
    margin-bottom: 6px;
    color: var(--accent-dark);
}
.highlight-box p {
    font-size: .875rem;
    color: var(--text-secondary);
    margin: 0;
}
```

Then in your template:

```html
<div class="highlight-box">
  <div class="highlight-box__title">Did you know?</div>
  <p>First-time buyers get stamp duty relief up to £425,000.</p>
</div>
```

### Step 3 - Responsive styles

Always add mobile breakpoints after your base styles:

```css
.my-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

/* Tablet: 2 columns */
@media (max-width: 1024px) {
    .my-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Mobile: 1 column */
@media (max-width: 768px) {
    .my-grid { grid-template-columns: 1fr; }
}
```

### Step 4 - Page-specific styles (inline in template)

For styles used only on one template, add a `<style>` block in that file:

```php
<?php get_header(); ?>
<style>
.my-page-only-thing {
    background: linear-gradient(135deg, var(--slate-900), #1e3a5f);
    color: white;
    padding: 60px 0;
}
</style>
<!-- rest of your template -->
```

### Using CSS custom properties in calculations

```css
.nav-offset-section {
    /* Push down by nav height + extra padding */
    padding-top: calc(var(--nav-h) + 40px);
}
```

---

## 8. Adding Counts

Counts appear in dashboard cards, admin tables, and status checks.
There are four types of "things to count" in this project.

### Type 1 - Count rows in a custom DB table

```php
global $wpdb;
$table = ah_theme_table( 'services' );                       // → wp_ah_services
$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );

echo $count . ' services';   // e.g. "6 services"
```

**With a WHERE condition:**

```php
$count = (int) $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM `{$table}` WHERE status = %s",
        'active'
    )
);
```

### Type 2 - Count WordPress posts

```php
// Count all published posts
$count = wp_count_posts()->publish;

// Count published posts of a custom post type
$count = wp_count_posts( 'product' )->publish;

// Count with a full WP_Query
$query = new WP_Query( [ 'post_type' => 'post', 'post_status' => 'publish' ] );
$count = $query->found_posts;
wp_reset_postdata();
```

### Type 3 - Check if a WP option exists

```php
$value = get_option( 'ah_site_settings' );

if ( $value ) {
    echo 'Option is set - ✓';
} else {
    echo 'Missing - using fallback';
}
```

**For null-safe check** (when value CAN be null but key exists - use `array_key_exists`):

```php
// WRONG - isset() returns false for null values:
if ( isset( $row['count'] ) ) { ... }

// RIGHT - array_key_exists() handles null correctly:
if ( array_key_exists( 'count', $row ) ) { ... }
```

### Type 4 - Count files on disk

```php
$static_dir = trailingslashit( get_template_directory() ) . 'static/';
$files      = glob( $static_dir . '*.html' ) ?: [];
$count      = count( $files );

echo $count . ' static pages';
```

### Displaying a count in a dashboard card

```php
$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
$ok    = $count > 0;
?>
<div class="ah-admin-card ah-admin-card--<?php echo $ok ? 'ok' : 'warn'; ?>">
  <div class="ah-admin-card__label">Services</div>
  <div class="ah-admin-card__value"><?php echo esc_html( $ok ? $count : '-' ); ?></div>
  <div class="ah-admin-card__sub"><?php echo $ok ? 'rows in DB' : 'empty'; ?></div>
</div>
```

### Handling "table might not exist"

Before counting, check the table exists:

```php
global $wpdb;
$table = ah_theme_table( 'services' );

$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;

if ( $exists ) {
    $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
} else {
    $count = null;  // null = table missing (plugin not active)
}
```

---

## 9. How to Create a Plugin from Scratch

### Step 1 - Create the folder and main file

```
wp-content/plugins/
└── my-plugin/
    └── my-plugin.php       ← Main plugin file
```

### Step 2 - The plugin header comment

This is what makes WordPress recognise it as a plugin:

```php
<?php
/*
Plugin Name: My Plugin
Plugin URI: https://example.com/my-plugin
Description: What this plugin does, in one sentence.
Version: 1.0.0
Author: Your Name
Author URI: https://example.com
Text Domain: my-plugin
License: GPL-2.0+
*/

// Safety: stop direct access
defined( 'ABSPATH' ) || exit;
```

After saving this file, go to **WP Admin → Plugins** and you'll see "My Plugin" listed.
Click **Activate**.

### Step 3 - Define constants

```php
define( 'MY_PLUGIN_VERSION', '1.0.0' );
define( 'MY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );   // absolute path with trailing /
define( 'MY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );    // URL with trailing /
```

### Step 4 - Include your other files

```php
require_once MY_PLUGIN_DIR . 'includes/class-my-feature.php';
require_once MY_PLUGIN_DIR . 'admin/admin-page.php';
```

### Step 5 - Hook into WordPress

```php
// Run setup when WordPress is fully loaded
add_action( 'init', 'my_plugin_init' );

function my_plugin_init() {
    // register post types, taxonomies, etc.
}

// Add admin menu
add_action( 'admin_menu', 'my_plugin_admin_menu' );

function my_plugin_admin_menu() {
    add_menu_page(
        'My Plugin',          // Page title (shown in browser tab)
        'My Plugin',          // Menu label
        'manage_options',     // Capability required to see it
        'my-plugin',          // Menu slug (used in URL: ?page=my-plugin)
        'my_plugin_page',     // Callback function to render the page
        'dashicons-admin-site', // Icon (dashicons class or URL)
        30                    // Position in menu (lower = lower in list)
    );
}

function my_plugin_page() {
    echo '<div class="wrap"><h1>My Plugin</h1><p>Hello!</p></div>';
}
```

### Step 6 - Run code on activation

```php
register_activation_hook( __FILE__, 'my_plugin_activate' );

function my_plugin_activate() {
    // Create DB tables, set default options, etc.
    add_option( 'my_plugin_version', MY_PLUGIN_VERSION );
}
```

### Step 7 - Clean up on deactivation / uninstall

```php
register_deactivation_hook( __FILE__, 'my_plugin_deactivate' );

function my_plugin_deactivate() {
    flush_rewrite_rules();   // clear any custom URL rules
}

// uninstall.php (separate file) - runs on "Delete" in Plugins list
// Only drop tables and delete options in uninstall.php, not deactivation
```

---

## 10. Plugin Anatomy

Here is a well-organised plugin folder structure:

```
my-plugin/
├── my-plugin.php              ← Main file (plugin header, constants, requires)
├── uninstall.php              ← Runs on plugin deletion
│
├── includes/
│   ├── class-autoloader.php   ← Optional: auto-load classes by name
│   └── class-my-model.php     ← Business logic, DB queries
│
├── admin/
│   ├── class-admin.php        ← Admin menu registration, asset loading
│   ├── assets/
│   │   ├── admin.css
│   │   └── admin.js
│   └── pages/
│       ├── dashboard.php      ← Admin page template
│       └── settings.php       ← Settings page template
│
└── public/
    ├── assets/
    │   ├── style.css
    │   └── script.js
    └── templates/
        └── my-shortcode.php   ← Shortcode HTML output
```

### Main plugin file - full example

```php
<?php
/*
Plugin Name: My Plugin
Version: 1.0.0
Author: Your Name
Text Domain: my-plugin
*/
defined( 'ABSPATH' ) || exit;

define( 'MY_PLUGIN_VERSION', '1.0.0' );
define( 'MY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load files
require_once MY_PLUGIN_DIR . 'includes/class-my-model.php';
require_once MY_PLUGIN_DIR . 'admin/class-admin.php';

// Hooks
add_action( 'admin_menu',             [ 'My_Plugin_Admin', 'register_menu' ] );
add_action( 'admin_enqueue_scripts',  [ 'My_Plugin_Admin', 'enqueue_assets' ] );

// Activation
register_activation_hook( __FILE__, function() {
    My_Plugin_Model::create_tables();
    add_option( 'my_plugin_version', MY_PLUGIN_VERSION );
} );
```

---

## 11. Adding Admin Pages to a Plugin

### Single top-level page

```php
add_menu_page(
    'My Plugin',          // <title> tag
    'My Plugin',          // sidebar label
    'manage_options',     // who can see it
    'my-plugin',          // slug (?page=my-plugin)
    'my_render_page',     // function to call
    'dashicons-admin-generic',
    25
);
```

### Adding sub-pages

```php
add_submenu_page(
    'my-plugin',          // parent slug
    'Settings',           // page title
    'Settings',           // menu label
    'manage_options',
    'my-plugin-settings', // slug (?page=my-plugin-settings)
    'my_render_settings'
);
```

### Admin page template pattern

Every admin page should follow this pattern:

```php
<?php
// 1. Security - always first
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied.' );
}

// 2. Handle POST (form saves)
$notice = '';
if ( isset( $_POST['save'] ) ) {
    // verify nonce first!
    if ( ! wp_verify_nonce( $_POST['my_nonce'] ?? '', 'my_save_action' ) ) {
        wp_die( 'Security check failed.' );
    }
    $title = sanitize_text_field( $_POST['title'] ?? '' );
    update_option( 'my_plugin_title', $title );
    $notice = 'Saved successfully.';
}

// 3. Get current value
$title = get_option( 'my_plugin_title', '' );
?>

<div class="wrap">
  <h1>My Plugin Settings</h1>

  <?php if ( $notice ) : ?>
  <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
  <?php endif; ?>

  <form method="post">
    <?php wp_nonce_field( 'my_save_action', 'my_nonce' ); ?>
    <table class="form-table">
      <tr>
        <th><label for="title">Title</label></th>
        <td>
          <input type="text" id="title" name="title"
                 value="<?php echo esc_attr( $title ); ?>" class="regular-text">
        </td>
      </tr>
    </table>
    <?php submit_button( 'Save Settings' ); ?>
  </form>
</div>
```

---

## 12. Saving & Reading Data in a Plugin

### WP Options (best for site settings)

```php
// Save
update_option( 'my_plugin_setting', 'value' );

// Save array/object as JSON
update_option( 'my_plugin_config', wp_json_encode( [ 'key' => 'value' ] ) );

// Read
$value = get_option( 'my_plugin_setting', 'default' );

// Read JSON option
$raw    = get_option( 'my_plugin_config', '{}' );
$config = json_decode( $raw, true ) ?: [];

// Delete
delete_option( 'my_plugin_setting' );
```

### Post Meta (per-post data)

```php
// Save
update_post_meta( $post_id, '_my_plugin_field', 'value' );

// Read
$value = get_post_meta( $post_id, '_my_plugin_field', true );

// Delete
delete_post_meta( $post_id, '_my_plugin_field' );
```

### Custom DB table (for structured, queryable data)

See section 13 below.

---

## 13. Plugin Database Tables

Use custom tables when you need to store multiple rows of structured data
(e.g. a list of items, orders, reviews).

### Creating the table (on plugin activation)

```php
function my_plugin_create_tables() {
    global $wpdb;
    $table   = $wpdb->prefix . 'my_items';                // → wp_my_items
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title      VARCHAR(255) NOT NULL,
        status     ENUM('active','inactive') DEFAULT 'active',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB {$charset}";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );   // WordPress-safe table creation / update
}

register_activation_hook( __FILE__, 'my_plugin_create_tables' );
```

**`dbDelta()` vs `$wpdb->query()`:**
- `dbDelta()` - WordPress helper that creates OR updates the table schema safely
- `$wpdb->query("CREATE TABLE IF NOT EXISTS ...")` - simpler, but won't alter existing tables

Use `dbDelta()` for anything that might need schema changes later.

### Inserting a row

```php
global $wpdb;
$table = $wpdb->prefix . 'my_items';

$wpdb->insert( $table, [
    'title'      => sanitize_text_field( $_POST['title'] ),
    'status'     => 'active',
    'sort_order' => 0,
] );

$new_id = $wpdb->insert_id;   // ID of the just-inserted row
```

### Reading rows

```php
global $wpdb;
$table = $wpdb->prefix . 'my_items';

// All active rows
$items = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM `{$table}` WHERE status = %s ORDER BY sort_order ASC",
        'active'
    )
);

// Single row by ID
$item = $wpdb->get_row(
    $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $id )
);

// Single value
$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
```

### Updating a row

```php
$wpdb->update(
    $table,
    [ 'title' => sanitize_text_field( $_POST['title'] ) ],   // new values
    [ 'id' => (int) $_POST['id'] ]                           // WHERE
);
```

### Deleting a row

```php
$wpdb->delete( $table, [ 'id' => (int) $id ] );
```

### Always use `$wpdb->prepare()` for queries with variables

```php
// SAFE - parameterised
$wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $id ) );

// DANGEROUS - never do this (SQL injection risk)
$wpdb->get_row( "SELECT * FROM `{$table}` WHERE id = $id" );
```

`%d` = integer, `%s` = string, `%f` = float.

---

## 14. Plugin AJAX Actions

AJAX lets JavaScript talk to PHP without reloading the page.
WordPress routes all AJAX through `admin-ajax.php`.

### PHP side - register the handler

```php
// In your plugin's main file or a class init:

// Logged-in users only:
add_action( 'wp_ajax_my_action', 'my_plugin_handle_my_action' );

// Also allow non-logged-in users (public AJAX):
add_action( 'wp_ajax_nopriv_my_action', 'my_plugin_handle_my_action' );

function my_plugin_handle_my_action() {
    // 1. Verify nonce
    if ( ! check_ajax_referer( 'my_ajax_nonce', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => 'Security check failed.' ] );
    }

    // 2. Check capability (for admin actions)
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Access denied.' ] );
    }

    // 3. Get and sanitize input
    $data = sanitize_text_field( $_POST['data'] ?? '' );

    // 4. Do the work
    update_option( 'my_result', $data );

    // 5. Send response
    wp_send_json_success( [ 'message' => 'Saved: ' . $data ] );
}
```

### JavaScript side

```javascript
// Pass nonce to JS via wp_localize_script() in PHP:
// wp_localize_script( 'my-script', 'myPlugin', [ 'ajaxUrl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('my_ajax_nonce') ] );

fetch( myPlugin.ajaxUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        action: 'my_action',          // must match wp_ajax_{my_action}
        nonce:  myPlugin.nonce,
        data:   'hello world',
    })
} )
.then( r => r.json() )
.then( res => {
    if ( res.success ) {
        console.log( res.data.message );  // 'Saved: hello world'
    } else {
        console.error( res.data.message );
    }
} );
```

### Enqueue the nonce

```php
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_script( 'my-script', MY_PLUGIN_URL . 'admin/assets/admin.js', [ 'jquery' ], MY_PLUGIN_VERSION, true );

    wp_localize_script( 'my-script', 'myPlugin', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'my_ajax_nonce' ),
    ] );
} );
```

---

## 15. Plugin Hooks

Hooks are the core of WordPress extensibility.
**Actions** let you run code at a specific moment.
**Filters** let you modify a value before WordPress uses it.

### Actions - run your code at the right time

```php
// Syntax:
add_action( 'hook_name', 'your_function', $priority, $num_args );

// Examples:
add_action( 'init',          'my_register_post_types' );       // after WP loads
add_action( 'wp_head',       'my_output_meta_tags' );          // inside <head>
add_action( 'wp_footer',     'my_output_footer_scripts' );     // before </body>
add_action( 'admin_menu',    'my_register_admin_pages' );      // build WP admin menu
add_action( 'save_post',     'my_save_post_meta', 10, 2 );     // when a post saves
add_action( 'wp_enqueue_scripts', 'my_enqueue_frontend_assets' ); // CSS/JS on frontend
add_action( 'admin_enqueue_scripts', 'my_enqueue_admin_assets' ); // CSS/JS in admin

// Priority: lower = earlier (default 10)
add_action( 'init', 'run_early', 5 );
add_action( 'init', 'run_late',  20 );
```

### Filters - modify a value

```php
// Syntax:
add_filter( 'hook_name', 'your_function', $priority, $num_args );

// Examples:
add_filter( 'the_title', 'my_modify_title' );           // change post title in output
add_filter( 'body_class', 'my_add_body_classes' );      // add CSS class to <body>
add_filter( 'excerpt_length', fn() => 20 );              // change excerpt word count
add_filter( 'wp_nav_menu_items', 'my_add_nav_item', 10, 2 ); // add item to nav menu

function my_modify_title( $title ) {
    return $title . ' | Extra';   // must return the (modified) value
}
```

### Creating your own hooks (for other plugins/themes to extend)

```php
// Action hook - let others run code at your moment:
do_action( 'my_plugin_after_save', $item_id );

// Filter hook - let others modify your value:
$title = apply_filters( 'my_plugin_item_title', $raw_title, $item_id );
```

### Most-used WordPress hooks quick reference

| Hook                       | When it fires                              |
|----------------------------|--------------------------------------------|
| `plugins_loaded`           | After all plugins are loaded               |
| `init`                     | Early, after WP is set up                  |
| `wp_loaded`                | After theme and plugins are fully loaded   |
| `template_redirect`        | Just before template is selected           |
| `wp_head`                  | Inside `<head>` tag                        |
| `wp_footer`                | Just before `</body>`                      |
| `admin_menu`               | Building the WP admin sidebar              |
| `admin_init`               | Admin requests - register settings etc.   |
| `save_post`                | When any post/page is saved                |
| `after_switch_theme`       | After a theme is activated                 |
| `register_activation_hook` | When a specific plugin is activated        |

---

## 16. Security Checklist

Apply every one of these - every time, no exceptions.

### Input sanitization (before DB / processing)

```php
// Single-line text (removes HTML, trims)
$title = sanitize_text_field( $_POST['title'] ?? '' );

// Multi-line plain text
$bio = sanitize_textarea_field( $_POST['bio'] ?? '' );

// URL
$url = esc_url_raw( $_POST['link'] ?? '' );

// Integer
$id = (int) ( $_POST['id'] ?? 0 );

// Slug (lowercase, hyphens)
$slug = sanitize_title( $_POST['slug'] ?? '' );

// Email
$email = sanitize_email( $_POST['email'] ?? '' );

// HTML from a rich editor (strips dangerous tags)
$content = wp_kses_post( $_POST['content'] ?? '' );

// A value that must be one of a fixed list
$status = in_array( $_POST['status'] ?? '', [ 'active', 'inactive' ], true )
    ? $_POST['status']
    : 'active';
```

### Output escaping (before echo)

```php
echo esc_html( $title );          // plain text in HTML
echo esc_attr( $slug );           // inside HTML attribute (href, value, class...)
echo esc_url( $link );            // inside href or src
echo esc_textarea( $bio );        // inside <textarea>
echo wp_kses_post( $content );    // trusted HTML (allows safe tags)
```

### Nonces - always verify before saving

```php
// In your form:
<?php wp_nonce_field( 'my_save_action', 'my_nonce' ); ?>

// In your POST handler:
if ( ! wp_verify_nonce( $_POST['my_nonce'] ?? '', 'my_save_action' ) ) {
    wp_die( 'Security check failed.' );
}
```

### Capability check - first line of every admin page and AJAX handler

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied.' );
}
```

### SQL - always prepare

```php
// Safe:
$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $id ) );

// Never:
$row = $wpdb->get_row( "SELECT * FROM `{$table}` WHERE id = $id" );  // SQL injection
```

---

## 17. WordPress Functions You Must Know

### Options

| Function                                    | What it does                             |
|---------------------------------------------|------------------------------------------|
| `get_option( $key, $default )`              | Read a stored option                     |
| `update_option( $key, $value )`             | Save/update an option                    |
| `delete_option( $key )`                     | Remove an option                         |
| `get_transient( $key )`                     | Read a cached/temporary value            |
| `set_transient( $key, $value, $expiry )`    | Store a cached value with TTL            |

### Posts

| Function                              | What it does                                     |
|---------------------------------------|--------------------------------------------------|
| `get_posts( $args )`                  | Get array of post objects                        |
| `new WP_Query( $args )`               | Full query with pagination                       |
| `wp_insert_post( $args )`             | Create a new post/page                           |
| `wp_update_post( $args )`             | Update an existing post                          |
| `wp_delete_post( $id, $force )`       | Delete a post                                    |
| `get_post_meta( $id, $key, $single )` | Read post meta value                             |
| `update_post_meta( $id, $key, $val )` | Save post meta value                             |
| `get_permalink( $id )`                | Get front-end URL of a post                      |
| `get_page_by_path( $slug )`           | Find a page by its slug                          |
| `the_title()`                         | Echo the current post's title (inside loop)      |
| `the_content()`                       | Echo the current post's content (inside loop)    |
| `the_permalink()`                     | Echo the current post's URL (inside loop)        |
| `have_posts()` / `the_post()`         | Loop control                                     |
| `wp_reset_postdata()`                 | Reset after a custom WP_Query loop               |

### URLs & paths

| Function                          | Returns                                          |
|-----------------------------------|--------------------------------------------------|
| `home_url( '/about/' )`           | `https://yoursite.com/about/`                   |
| `admin_url( 'admin-ajax.php' )`   | `https://yoursite.com/wp-admin/admin-ajax.php`  |
| `get_template_directory()`        | Absolute server path to active theme folder      |
| `get_template_directory_uri()`    | URL to active theme folder                       |
| `plugin_dir_path( __FILE__ )`     | Absolute path to the plugin folder               |
| `plugin_dir_url( __FILE__ )`      | URL to the plugin folder                         |
| `trailingslashit( $path )`        | Ensures string ends with `/`                     |

### Output

| Function                    | What it does                                        |
|-----------------------------|-----------------------------------------------------|
| `wp_head()`                 | Outputs `<head>` content (scripts, styles, meta)    |
| `wp_footer()`               | Outputs footer scripts before `</body>`             |
| `get_header()`              | Includes `header.php`                               |
| `get_footer()`              | Includes `footer.php`                               |
| `get_template_part( $slug )`| Includes a component/partial template               |
| `do_shortcode( '[code]' )`  | Runs a shortcode and returns its output             |

### Security

| Function                                    | What it does                            |
|---------------------------------------------|-----------------------------------------|
| `wp_verify_nonce( $nonce, $action )`        | Verify a form nonce (POST forms)        |
| `check_ajax_referer( $action, $key )`       | Verify nonce in AJAX handler            |
| `wp_nonce_field( $action, $name )`          | Output hidden nonce input in form       |
| `wp_nonce_url( $url, $action )`             | Add nonce to a GET link                 |
| `current_user_can( $cap )`                  | Check if logged-in user has capability  |
| `wp_die( $message )`                        | Stop execution with an error message    |
| `wp_send_json_success( $data )`             | Send AJAX success response              |
| `wp_send_json_error( $data )`               | Send AJAX error response                |

---

## 18. Common Mistakes

### Mistake 1 - Forgetting `wp_reset_postdata()` after a custom WP_Query

```php
// WRONG - the global $post is now broken for the rest of the page:
$q = new WP_Query( [...] );
while ( $q->have_posts() ) { $q->the_post(); /* ... */ }

// RIGHT:
$q = new WP_Query( [...] );
while ( $q->have_posts() ) { $q->the_post(); /* ... */ }
wp_reset_postdata();   // ← always add this
```

### Mistake 2 - Using `isset()` when the value can be `null`

```php
$row = [ 'count' => null ];

isset( $row['count'] )              // false - WRONG, misses null
array_key_exists( 'count', $row )   // true  - CORRECT
```

### Mistake 3 - Not checking if a table exists before querying it

```php
// If the plugin creating the table isn't active, this throws a DB error:
$count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );

// Safe:
$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
if ( $exists ) {
    $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
} else {
    $count = null;
}
```

### Mistake 4 - Hardcoding the table prefix

```php
// WRONG - breaks on any non-default install:
$table = 'wp_my_items';

// RIGHT:
global $wpdb;
$table = $wpdb->prefix . 'my_items';   // → wp_my_items, or wpsite2_my_items on multisite
```

### Mistake 5 - Mixing GET and POST nonces

```php
// GET link nonces go in the URL:
$url = wp_nonce_url( admin_url( '?page=my&action=delete&id=5' ), 'my_delete_5' );
// Verify: wp_verify_nonce( $_GET['_wpnonce'], 'my_delete_5' )

// POST form nonces go in a hidden field:
wp_nonce_field( 'my_save', 'my_nonce' );
// Verify: wp_verify_nonce( $_POST['my_nonce'], 'my_save' )

// Never put a POST nonce in a GET URL or vice versa.
```

### Mistake 6 - Adding hooks outside `add_action`

```php
// WRONG - runs immediately at file-include time, too early:
My_Class::register_post_types();

// RIGHT - runs at the correct WordPress moment:
add_action( 'init', [ 'My_Class', 'register_post_types' ] );
```

### Mistake 7 - Outputting before `get_header()` inside a template

```php
// WRONG - outputs HTML before WordPress sends headers:
echo '<h1>Title</h1>';
get_header();

// RIGHT:
get_header();
echo '<h1>Title</h1>';
```

### Mistake 8 - Not escaping output

```php
// WRONG - XSS vulnerability:
echo $_POST['name'];
echo $row->title;

// RIGHT:
echo esc_html( sanitize_text_field( $_POST['name'] ) );
echo esc_html( $row->title );
```

### Mistake 9 - Using `the_post()` outside a loop / forgetting the loop

```php
// The loop must be: while ( have_posts() ) : the_post(); ... endwhile;
// Outside this, template tags like the_title() return nothing / error.

// For a single known page, use:
$post_id = get_queried_object_id();
$title   = get_the_title( $post_id );
$content = get_the_content( null, false, $post_id );
```

### Mistake 10 - Calling `wp_editor()` and trying to capture its output

```php
// WRONG - wp_editor() echoes directly, ob_start won't help in most cases:
$editor = wp_editor( $content, 'my_editor', $settings );

// RIGHT - call it exactly where you want it in the form:
wp_editor( $content, 'my_editor', $settings );   // just let it echo inline
```

---

*End of basics.md - for project-specific details see `learn.md` in the same folder.*
