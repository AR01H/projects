# CMS Plugin — Admin UI Standard

> Single-source reference for every admin page. Every new or modified page MUST follow this standard.

---

## 1. Folder Structure & OOP Architecture

```
plugins/cms-plugin/
├── src/
│   ├── Admin/
│   │   └── Components/
│   │       ├── AdminComponents.php      # Page chrome: headers, notices, cards, forms, tables, tabs, pagination
│   │       ├── BuilderComponents.php    # Drag-drop builder UI: items, grids, submenus, add buttons
│   │       ├── FormBuilder.php          # Fluent form builder API
│   │       ├── TableBuilder.php         # Custom table layouts
│   │       └── PageLayout.php           # Page layout wrappers
│   ├── Bootstrap/
│   │   ├── PluginBootstrap.php          # Plugin entry point, service registration
│   │   └── HookRegistrar.php           # ALL add_action/add_filter calls centralized here
│   ├── Config/
│   │   └── Capabilities.php            # Permission capability definitions
│   ├── Cache/
│   │   └── CacheManager.php            # Cache abstraction
│   ├── Database/
│   │   └── Connection.php              # DB connection wrapper
│   ├── Http/
│   │   ├── Rest/RestController.php     # REST API base class
│   │   └── Ajax/AjaxDispatcher.php     # AJAX handler dispatcher
│   ├── Repository/
│   │   └── AbstractRepository.php      # Base repository with CRUD
│   ├── Support/
│   │   ├── Logger.php                  # Logging utility
│   │   ├── ErrorHandler.php            # Error handling
│   │   └── PermissionService.php       # Granular permission checks
│   ├── Exception/
│   │   ├── ValidationException.php
│   │   ├── UnauthorizedException.php
│   │   ├── NotFoundException.php
│   │   ├── DatabaseException.php
│   │   └── PluginException.php
│   └── Feature/
│       ├── Analytics/
│       │   ├── AnalyticsModule.php
│       │   ├── Controller/
│       │   ├── Repository/
│       │   ├── Model/
│       │   └── Rest/
│       ├── Audit/
│       │   ├── AuditModule.php
│       │   ├── Repository/
│       │   └── Model/
│       ├── CustomCode/
│       │   ├── CustomCodeModule.php
│       │   ├── Service/
│       │   └── Repository/
│       ├── Media/
│       │   ├── MediaModule.php
│       │   ├── Repository/
│       │   └── Model/
│       ├── Navigation/
│       │   └── Controller/
│       │       └── NavigationAdminController.php
│       ├── Newsletter/
│       │   ├── NewsletterModule.php
│       │   ├── Controller/
│       │   ├── Repository/
│       │   └── Model/
│       ├── NewsBar/
│       │   ├── NewsBarModule.php
│       │   ├── Repository/
│       │   └── Model/
│       ├── Pages/
│       │   ├── PagesModule.php
│       │   ├── Controller/
│       │   ├── Repository/
│       │   ├── Model/
│       │   ├── Renderer/
│       │   └── Shortcode/
│       ├── Posts/
│       │   ├── PostsModule.php
│       │   ├── Controller/
│       │   ├── Repository/
│       │   └── Model/
│       ├── Redirect/
│       │   ├── RedirectModule.php
│       │   ├── Service/
│       │   └── Repository/
│       ├── Resources/
│       │   ├── ResourcesModule.php
│       │   ├── Repository/
│       │   ├── Model/
│       │   └── Shortcode/
│       ├── SiteNotices/
│       │   ├── SiteNoticesModule.php
│       │   ├── Repository/
│       │   └── Model/
│       ├── Spotlights/
│       │   ├── SpotlightsModule.php
│       │   ├── Controller/
│       │   ├── Repository/
│       │   └── Model/
│       ├── Taxonomy/
│       │   ├── TaxonomyModule.php
│       │   ├── TermManager.php
│       │   ├── Repository/
│       │   └── Model/
│       ├── Visitors/
│       │   ├── VisitorsModule.php
│       │   ├── Controller/
│       │   ├── Repository/
│       │   └── Model/
│       └── Workflow/
│           ├── WorkflowModule.php
│           ├── Controller/
│           ├── Cron/
│           ├── Service/
│           └── Repository/
├── admin/
│   ├── menus/AdminMenus.php            # Menu registration & page routing
│   ├── AdminBootstrap.php              # Admin enqueue, AJAX handlers
│   ├── assets/css/admin-style.css      # All admin CSS (design tokens, components)
│   ├── assets/js/admin-script.js       # Admin JS (sortable, media picker, etc.)
│   ├── pages/                          # Admin page templates (one per menu item)
│   │   ├── navigation.php              # Uses BuilderComponents
│   │   ├── spotlights.php
│   │   ├── faqs.php
│   │   ├── settings.php
│   │   ├── posts.php
│   │   ├── resources.php
│   │   ├── pages.php
│   │   ├── media.php
│   │   ├── banners.php
│   │   ├── notices.php
│   │   ├── reviews.php
│   │   ├── taxonomy.php
│   │   ├── newsletter.php
│   │   ├── analytics.php
│   │   ├── visitors.php
│   │   ├── help.php
│   │   ├── events.php
│   │   └── import.php
│   ├── FeaturedIn.php                  # Admin root pages (some still need refactoring)
│   ├── NewsBar.php
│   ├── ClientStories.php
│   ├── FileLinks.php
│   ├── StaticPages.php
│   ├── FormBuilder.php
│   ├── WorkflowManager.php
│   ├── RedirectRules.php
│   ├── CustomCode.php
│   ├── AdminActions.php
│   ├── GlobalSettings.php
│   ├── ReferenceNotes.php
│   ├── AuditLog.php
│   └── PageBuilder.php
├── models/                             # Legacy model classes (being migrated to src/Feature/*/Model/)
├── helper/                             # Helper classes (Pagination, Slug, Validator, etc.)
├── database/                           # DB installer, schema, migrations
├── inc/                                # Core includes (Autoloader, Cache, etc.)
├── functions.php                       # Theme-level bootstrap (loads plugin autoloader)
└── ah-cms.php                          # Plugin entry point
```

### OOP Pattern Per Feature

Each feature follows this structure:

```
src/Feature/{FeatureName}/
├── {FeatureName}Module.php            # Module registration (optional)
├── Controller/
│   └── {Feature}AdminController.php   # Admin save/load handlers
├── Repository/
│   └── {Feature}Repository.php        # Database queries
├── Model/
│   └── {Feature}.php                  # Data model
├── Service/
│   └── {Feature}Service.php           # Business logic
└── Rest/
    └── {Feature}RestController.php    # REST API endpoints
```

### Autoloading

- **PSR-4**: `Ah\Cms\*` → `src/*` (namespace maps to directory)
- **Legacy classes**: Explicit map in `inc/Autoloader.php` (AH_*_Model → models/*.php)
- **Admin components**: `Ah\Cms\Admin\Components\*`
- **Feature controllers**: `Ah\Cms\Feature\*\Controller\*`

---

## 2. Component Classes

All reusable UI lives in `src/Admin/Components/`. Four classes, zero overlap:

| Class | Purpose | When to use |
|---|---|---|
| `AdminComponents` | Page chrome: headers, notices, cards, forms, tables, tabs, pagination, badges | Every page — this is the foundation |
| `BuilderComponents` | Drag-drop builder UI: items, grids, submenus, add buttons | Navigation editor, any drag-drop builder |
| `FormBuilder` | Fluent form builder API | Complex multi-step forms |
| `TableBuilder` | Custom table layouts | Tables that need special column rendering |

**Import once at the top of every page file:**

```php
use Ah\Cms\Admin\Components\AdminComponents;
use Ah\Cms\Admin\Components\BuilderComponents; // only if page has builder UI
```

---

## 2. Page Structure Template

Every admin page MUST follow this skeleton:

```php
<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

// ... model imports, state, POST handlers ...

$notice = '';
$n_type = 'success';
// ... handle POST, set $notice ...

?>
<div class="wrap ah-wrap">
  <?php AdminComponents::pageHeader( 'dashicons-icon-name', 'Page Title', 'Optional description text.' ); ?>
  <?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, $n_type ); ?><?php endif; ?>

  <?php if ( $action === 'list' ) : ?>
    <?php AdminComponents::filterBar( array( /* ... */ ) ); ?>
    <?php AdminComponents::dataTable( array( /* ... */ ) ); ?>
    <?php AdminComponents::pagination( $meta ); ?>

  <?php elseif ( $action === 'add' || $action === 'edit' ) : ?>
    <?php AdminComponents::backLink( $back_url ); ?>
    <?php ob_start(); ?>
      <form method="post">
        <?php wp_nonce_field( 'nonce_action', 'nonce_field' ); ?>
        <?php AdminComponents::formRow( 'Label', '<input ...>' ); ?>
        <?php AdminComponents::formGrid( array( /* ... */ ) ); ?>
        <button type="submit" class="ah-btn ah-btn-primary">Save</button>
      </form>
    <?php AdminComponents::card( 'Form Title', ob_get_clean() ); ?>
  <?php endif; ?>
</div>
```

---

## 3. Available Components (Quick Reference)

### 3.1 Page Chrome

```php
// Page header with dashicon and optional description
AdminComponents::pageHeader( 'icon-name', 'Page Title' );
AdminComponents::pageHeader( 'icon-name', 'Page Title', 'Optional description shown below the title.' );

// Success / error / warning notice
AdminComponents::notice( 'Message text', 'success' );  // or 'error', 'warning'

// Back link
AdminComponents::backLink( $url, '← Back' );
```

### 3.2 Cards

```php
// Card with title and content (content as string)
AdminComponents::card( 'Card Title', $html_content );

// Card with ob_start pattern (recommended)
ob_start();
// ... form HTML ...
AdminComponents::card( 'Title', ob_get_clean() );
```

### 3.3 Forms

```php
// Single form row
AdminComponents::formRow( 'Label', '<input type="text" name="field" value="...">' );
AdminComponents::formRow( 'Label', $input_html, 'Help text', 'optional-id' );

// 2-column form grid
AdminComponents::formGrid( array(
  array( 'Field 1', '<input ...>' ),
  array( 'Field 2', '<input ...>' ),
  array( 'Field 3', '<input ...>', 'Optional help text' ),
) );

// Generic field by type
AdminComponents::field( 'text', 'name', 'Label', $value, array( 'placeholder' => '...' ) );
AdminComponents::field( 'textarea', 'name', 'Label', $value, array( 'rows' => 4 ) );
AdminComponents::field( 'select', 'name', 'Label', $value, array( 'options' => array( 'a' => 'A', 'b' => 'B' ) ) );
AdminComponents::field( 'checkbox', 'name', 'Label', $bool_value );
AdminComponents::field( 'number', 'name', 'Label', $value, array( 'min' => 0, 'max' => 100 ) );
AdminComponents::field( 'image', 'name', 'Label', $value );  // Image-only media picker
AdminComponents::field( 'video', 'name', 'Label', $value );  // Video-only media picker
AdminComponents::field( 'media', 'name', 'Label', $value );  // Any media (image/GIF/video)

// Media field with preview (supports images, GIFs, videos)
AdminComponents::mediaField( 'image_id', 'Label', $value );  // Default: image-only
AdminComponents::mediaField( 'video_id', 'Label', $value, array( 'type' => 'video' ) );  // Video-only
AdminComponents::mediaField( 'media_id', 'Label', $value, array( 'type' => 'media' ) );  // Any media
```

### 3.4 Tables

```php
AdminComponents::dataTable( array(
  'columns' => array(
    array( 'label' => 'Name', 'render' => function( $item ) {
      return '<strong>' . esc_html( $item->name ) . '</strong>';
    } ),
    array( 'label' => 'Status', 'render' => function( $item ) {
      return AdminComponents::statusBadge( $item->status );
    } ),
    array( 'label' => 'Date', 'key' => 'created_at' ),  // simple key access
  ),
  'items'         => $items_array,
  'sortable'      => true,                  // optional drag handle column
  'model'         => 'model_name',          // for sortable JS reordering
  'empty_message' => 'No items yet.',
  'actions'       => function( $item ) {
    return '<a href="..." class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
  },
) );
```

### 3.5 Filter Bar

```php
AdminComponents::filterBar( array(
  'page_slug'          => 'ah-slug',
  'search_placeholder' => 'Search…',
  'search_value'       => $search,
  'search_name'        => 's',                    // optional, default 's'
  'hidden_inputs'      => array( 'tab' => 'tab1' ), // optional
  'filters'            => array(
    array(
      'name'     => 'status',
      'options'  => array( '' => 'All', 'active' => 'Active' ),
      'selected' => $current_status,
      'show_if'  => ! empty( $options ),          // optional visibility
    ),
  ),
  'extra_fields'  => '<input type="text" name="label" placeholder="Filter by label…">',  // optional extra inputs
  'active_values' => array( $label_search ),      // values to check for Reset button
  'show_reset'    => true,                         // show Reset when filters active (default true)
  'add_url'       => admin_url( 'admin.php?page=ah-slug&action=add' ),
  'add_label'     => '+ Add New',
) );
```

**Filter Bar outputs:**
- Search input (if `search_placeholder` provided)
- Dropdown filters (from `filters` array)
- Extra fields (raw HTML from `extra_fields`)
- Filter button
- Reset button (red, only shows when a filter is active)
- Add button (blue, right-aligned)

### 3.6 Tabs

```php
// Server-side tabs (URL reload) — RECOMMENDED for most pages
AdminComponents::tabBarUrl( array(
  'tab-key' => 'Tab Label',
  'other'   => 'Other Tab',
), $active_tab );

// Client-side tabs (JS hash switching) — only for in-page toggling
AdminComponents::tabBar( array(
  'tab-key' => 'Tab Label',
), $active_tab );
```

**Tab bar CSS:**
- `.ah-tabs` — flex container
- `.ah-tab` — individual tab link
- `.ah-tab.ah-tab-active` — active tab (highlighted with primary color)
- Wrap tab content in `<div class="ah-card" style="border-top-left-radius:0;">` to visually connect with tabs

### 3.7 Status & Badges

```php
echo AdminComponents::statusBadge( 'active' );   // <span class="ah-badge ah-badge-active">
echo AdminComponents::statusBadge( 'draft' );    // <span class="ah-badge ah-badge-draft">
echo AdminComponents::statusBadge( 'inactive' ); // <span class="ah-badge ah-badge-inactive">
```

### 3.8 Empty State

```php
AdminComponents::emptyState( 'No items yet.', 'dashicons-icon-name' );
```

### 3.9 Pagination

```php
AdminComponents::pagination( $meta );
// $meta = array( 'total' => N, 'total_pages' => M, 'current_page' => P );
```

### 3.10 Stat Cards (Dashboard)

```php
AdminComponents::statCard( $value, 'Label', 'dashicons-icon' );
```

### 3.11 Sticky Header (Edit Forms)

```php
AdminComponents::stickyHeader( $back_url, 'Form Title', 'Save' );
```

### 3.12 Complete List Page (listPage)

Renders an entire admin listing page in one call — header, notice, filter bar, data table, and pagination:

```php
AdminComponents::listPage( array(
  'icon'        => 'megaphone',
  'title'       => 'News Bar',
  'description' => 'Create scrolling news items displayed in the site news ticker.',
  'notice'      => $notice,
  'notice_type' => 'success',
  'filter_bar'  => array(
    'page_slug'          => 'ah-news-bar',
    'search_placeholder' => 'Search…',
    'search_value'       => $search,
    'filters'            => array(
      array(
        'name'     => 'status',
        'options'  => array( '' => 'All', 'active' => 'Active' ),
        'selected' => $status,
      ),
    ),
    'add_url'   => admin_url( 'admin.php?page=ah-news-bar&action=add' ),
    'add_label' => '+ Add Item',
  ),
  'table' => array(
    'columns' => array(
      array( 'label' => 'Title', 'render' => function( $item ) {
        return '<strong>' . esc_html( $item->title ) . '</strong>';
      } ),
      array( 'label' => 'Status', 'render' => function( $item ) {
        return AdminComponents::statusBadge( $item->status );
      } ),
    ),
    'items'         => $items,
    'sortable'      => true,
    'model'         => 'news_bar_items',
    'empty_message' => 'No items yet.',
    'actions'       => function( $item ) {
      return '<a href="..." class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
    },
  ),
  'pagination' => $meta,  // array( 'total' => N, 'total_pages' => M, 'current_page' => P )
) );
```

**When to use:** Any page that is purely a list view (with optional add/edit branches). For pages with both list and edit views, use `listPage()` for the list branch and a manual wrapper for the edit branch.

**Pattern for list + edit pages:**
```php
<?php if ( $action === 'list' ) : ?>
  <?php AdminComponents::listPage( array( /* ... */ ) ); ?>
<?php else : ?>
  <div class="wrap ah-wrap">
    <?php AdminComponents::pageHeader( 'icon', 'Title', 'Description' ); ?>
    <?php AdminComponents::notice( $notice, $n_type ); ?>
    <?php AdminComponents::backLink( $back_url ); ?>
    <?php ob_start(); ?>
      <form method="post">...</form>
    <?php AdminComponents::card( 'Edit Title', ob_get_clean() ); ?>
  </div>
<?php endif; ?>
```

---

## 4. Button Classes

Always use these CSS classes — never raw WordPress button classes:

| Class | Style |
|---|---|
| `ah-btn ah-btn-primary` | Primary action (Save, Create) |
| `ah-btn ah-btn-secondary` | Secondary action (Cancel, Edit, View) |
| `ah-btn ah-btn-danger` | Destructive action (Delete, Remove) |
| `ah-btn ah-btn-sm` | Small variant (inside tables) |
| `ah-btn ah-btn-icon` | Icon-only button |

---

## 5. Card Pattern with `ob_start()`

Every form should be wrapped in a card using the ob_start pattern:

```php
<?php ob_start(); ?>
  <form method="post">
    <?php wp_nonce_field( 'action_name', 'nonce_field' ); ?>
    <?php AdminComponents::formRow( 'Field', '<input ...>' ); ?>
    <button type="submit" class="ah-btn ah-btn-primary">Save</button>
  </form>
<?php AdminComponents::card( 'Card Title', ob_get_clean() ); ?>
```

---

## 6. Tab Bar with `border-top-left-radius: 0`

When using server-side tabs, the card below must connect visually:

```php
<?php AdminComponents::tabBarUrl( $tabs, $active_tab ); ?>

<div class="ah-card" style="border-top-left-radius:0;">
  <!-- tab content -->
</div>
```

---

## 7. Confirm Modal (Reusable Dialog)

Replace all `onclick="return confirm('...')"` with the plugin's modal system. The modal is output once via `AdminBootstrap` on every CMS admin page.

**Delete link:**
```php
// Simple (uses default message)
AdminComponents::confirmDelete( $delete_url, 'ah_del_nonce' );

// Custom message
echo '<a href="' . esc_url( $url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-confirm="Custom message here.">Delete</a>';
```

**Action button with confirmation:**
```php
AdminComponents::actionButton( 'Delete Rule', $url, 'danger', 'Delete this rule permanently?' );
AdminComponents::actionButton( 'Clear Cache', $url, 'secondary', 'Clear all caches?' );
AdminComponents::actionButton( 'Edit', $edit_url, 'secondary' ); // no confirm needed
```

**How it works:**
- Any element with class `ah-confirm-delete` triggers the modal on click
- `data-confirm="..."` sets the confirmation message
- Modal shows Cancel + Delete buttons
- ESC key or clicking overlay closes the modal
- Delete button navigates to the original `href`

**Never use `onclick="return confirm('...')"` — always use the modal.**

---

## 8. POST Handler Pattern

Every page POST handler should follow this structure:

```php
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
  if ( ! wp_verify_nonce( $_POST['nonce_field'] ?? '', 'nonce_action' ) ) {
    wp_die( 'Security check failed.' );
  }

  // Handle different submit buttons
  if ( isset( $_POST['save_item'] ) ) {
    // ... sanitize, save ...
    $notice = 'Item saved.';
  }

  if ( isset( $_POST['delete_item'] ) ) {
    // ... delete ...
    $notice = 'Item deleted.';
  }
}
```

---

## 8. Delete Pattern

**GET delete** (simple, for list pages):
```php
if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_action' ) ) {
  $model->delete( (int) $_GET['delete_id'] );
  $notice = 'Deleted.';
}
```

**POST delete** (for forms, more secure):
```php
// In form
$html .= '<form method="post" style="display:inline;margin:0;padding:0">';
$html .= wp_nonce_field( 'ah_del_action', '_wpnonce', false );
$html .= '<input type="hidden" name="delete_id" value="' . $id . '">';
$html .= '<button type="submit" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm(\'Delete?\')">Delete</button>';
$html .= '</form>';
```

---

## 9. CSS Variables

Use these CSS custom properties for consistent theming:

| Variable | Usage |
|---|---|
| `var(--ah-primary)` | Primary brand color |
| `var(--ah-text)` | Main text color |
| `var(--ah-muted)` | Muted / secondary text |
| `var(--ah-bg-light)` | Light background |
| `var(--ah-border)` | Border color |
| `var(--ah-radius)` | Border radius |
| `var(--ah-danger)` | Error / destructive color |
| `var(--ah-text-muted)` | Muted text (alias) |

---

## 10. Per-Tab Save Pattern

When a page uses server-side tabs where each tab submits independently:

```php
// Load existing data FIRST
$existing = json_decode( get_option( 'option_name', '{}' ), true ) ?? array();

// Only overwrite if the tab's fields are in POST
if ( isset( $_POST['tab1_field'] ) ) {
  $existing['field1'] = sanitize_text_field( $_POST['tab1_field'] );
}

if ( isset( $_POST['tab2_field'] ) ) {
  $existing['field2'] = sanitize_text_field( $_POST['tab2_field'] );
}

// Save with active_tab for redirect
update_option( 'option_name', wp_json_encode( $existing ) );
```

**Critical**: Always check `isset($_POST[...])` before writing each section. Without this, saving one tab wipes the others.

---

## 11. Admin Slug Naming

All admin slugs use the `ah-` prefix:

```
ah-dashboard, ah-navigation, ah-cms-settings, ah-spotlights, ah-faqs, ...
```

**Never use WP core slug names** (e.g., `ah-settings` conflicts with WP Settings). Always prefix with `ah-cms-` for settings-type pages.

---

## 12. Autoloader & Namespaces

- PSR-4: `Ah\Cms\*` → `src/*`
- Non-namespaced classes: mapped in `inc/Autoloader.php`
- Admin components: `Ah\Cms\Admin\Components\*`
- Feature controllers: `Ah\Cms\Feature\*\Controller\*`

**Always use the full namespace in page files:**

```php
use Ah\Cms\Admin\Components\AdminComponents;
use Ah\Cms\Feature\SomeFeature\Controller\SomeController;
```

---

## 13. Checklist for New/Modified Admin Pages

- [ ] Uses `AdminComponents::pageHeader( 'icon', 'Title', 'Description' )` (not raw `<h1>`, description as 3rd param)
- [ ] Uses `AdminComponents::notice()` (not raw notice divs)
- [ ] Uses `AdminComponents::card()` for form wrappers (with `ob_start()`/`ob_get_clean()` pattern)
- [ ] Uses `AdminComponents::formRow()` or `formGrid()` (not raw `<label><input>`)
- [ ] Uses `AdminComponents::dataTable()` (not raw `<table>`)
- [ ] Uses `AdminComponents::filterBar()` with search, filters, and Reset button (not raw filter HTML)
- [ ] Uses `AdminComponents::tabBarUrl()` for server-side tabs (not raw tab HTML)
- [ ] Uses `ah-btn` CSS classes (not WordPress `button button-primary`)
- [ ] Uses CSS variables (not hardcoded colors)
- [ ] Delete buttons use `ah-confirm-delete` class with `data-title` and `data-confirm` (not `onclick="return confirm()"`)
- [ ] Per-tab save uses `isset($_POST[...])` guards
- [ ] Nonce verification on every POST/GET action
- [ ] `php -l` passes with no errors
- [ ] No raw `<table>` tags (use `dataTable()`)
- [ ] No raw form rows (use `formRow()`)
- [ ] Table wrapper has `overflow-x: auto` for mobile scrolling
- [ ] Form fields returning null use `ob_start()`/`ob_get_clean()` (e.g., `wp_editor()`, `render_picker()`)
- [ ] Media upload fields use `mediaField()` with `type => 'media'` to accept images, GIFs, and videos

---

## 14. Responsive Patterns

### Filter Bar
- `.ah-search-form` — flex with `flex-wrap: wrap`, inputs use `flex: 1 1 160px`
- On mobile (≤900px): inputs go full width, stack vertically
- On small mobile (≤600px): buttons go full width

### Data Tables
- `.ah-table-wrap` — `overflow-x: auto` for horizontal scrolling
- `.ah-table` — `min-width: 600px` to prevent column collapse
- On mobile: swipe left to see action columns

### Confirm Modal
- `.ah-modal-overlay` — fixed overlay with backdrop blur
- `.ah-modal` — centered card with header/body/footer
- `.ah-confirm-delete` — any element triggers the modal
- `data-title` — shows what's being deleted
- `data-confirm` — shows confirmation message
- ESC key, overlay click, Cancel button all close modal

---

## 15. Audit Results (Last Updated: 2026-07-24)

### Fully Compliant (all 14 checks pass)

| File | Location |
|---|---|
| spotlights.php | admin/pages/ |
| faqs.php | admin/pages/ |
| settings.php | admin/pages/ |
| navigation.php | admin/pages/ |
| resources.php | admin/pages/ |
| pages.php | admin/pages/ |
| reviews.php | admin/pages/ |
| notices.php | admin/pages/ |
| banners.php | admin/pages/ |
| taxonomy.php | admin/pages/ |
| newsletter.php | admin/pages/ |
| media.php | admin/pages/ |
| posts.php | admin/pages/ |
| FeaturedIn.php | admin/ |
| NewsBar.php | admin/ |
| ClientStories.php | admin/ |

### Partially Compliant (h1/notice fixed, tables still raw)

| File | Fixed | Still needs |
|---|---|---|
| AuditLog.php | pageHeader, filterBar | Raw `<table>` → dataTable |
| FileLinks.php | pageHeader, filterBar, dataTable, card, backLink, confirmDelete, ah-confirm-delete modal, list+edit pattern | — |
| GlobalSettings.php | pageHeader, notice, formRow | — |
| CustomCode.php | pageHeader, tabBarUrl, filterBar, dataTable, formRow, card, backLink, confirmDelete, ah-confirm-delete modal, list+edit pattern | — |
| help.php | pageHeader | — |
| StaticPages.php | pageHeader, filterBar, dataTable, formRow, card, backLink, confirmDelete, ah-confirm-delete modal, list+edit pattern | — |
| FormBuilder.php | pageHeader, filterBar, dataTable, formRow, card, backLink, confirmDelete, ah-confirm-delete modal, list+edit pattern | — |
| ReferenceNotes.php | pageHeader, filterBar, dataTable, formRow, card, backLink, confirmDelete, ah-confirm-delete modal, list+edit pattern | — |
| AdminActions.php | pageHeader | — |
| visitors.php | button class | Raw `<table>` → dataTable |
| analytics.php | — | Raw `<table>` → dataTable |

### Not Yet Audited (complex pages, need deeper refactoring)

| File | Known issues |
|---|---|
| WorkflowManager.php | Raw h1, multiple raw tables, complex JS |
| PageBuilder.php | Raw table |
| RedirectRules.php | Raw table |
