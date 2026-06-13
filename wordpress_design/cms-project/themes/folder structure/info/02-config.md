# 02 - Config (`includes/core_settings.php`)

## What It Is

A single PHP file that **returns an array**.
It is the **only place** you need to edit when starting a new project.

```php
$GLOBALS['theme_config'] = require __DIR__ . '/includes/core_settings.php';
```

---

## Why an Array and Not a Class?

| Approach | Pro | Con |
|----------|-----|-----|
| Plain array | Simple, dumpable, no dependencies | No type safety |
| Class/singleton | Type safe, IDE autocomplete | More boilerplate |
| Constants | Fast | Can't loop over them |

**Decision:** Plain array wins for theme config because:
- You loop over CPTs, menus, sidebars to register them - arrays are loop-friendly
- `var_dump( $GLOBALS['theme_config'] )` shows everything at once
- No class to instantiate - just `require` and use

---

## Full Config Reference

### `name` / `version` / `textdomain`
```php
'name'       => 'New Project Theme',
'version'    => '1.0.0',   // also stored in NPT_VERSION constant
'textdomain' => 'npt',     // also stored in NPT_TEXTDOMAIN constant
```
> Note: `version` in the config is the *human-readable* source of truth.
> The constant `NPT_VERSION` in `static/` is what gets passed to `wp_enqueue_*()`.

---

### `assets` - Front-end files
```php
'assets' => [
    'styles' => [
        'npt-main'  => '/assets/css/main.css',
        'npt-icons' => '/assets/css/icons.css',
    ],
    'scripts' => [
        'npt-main'   => '/assets/js/main.js',
        'npt-vendor' => '/assets/js/vendor.js',
    ],
],
```
**Key** = the `wp_enqueue_*()` handle.
**Value** = path relative to theme root.

`function.php` loops over both arrays to enqueue everything:
```php
foreach ( $cfg['assets']['styles'] as $handle => $path ) {
    wp_enqueue_style( $handle, $uri . $path, [], NPT_VERSION );
}
```

To add a new stylesheet: add one line to the `styles` array. Done.

---

### `menus` - Navigation locations
```php
'menus' => [
    'primary' => 'Primary Navigation',
    'footer'  => 'Footer Links',
],
```
`core_details.php` passes this directly to `register_nav_menus()`.
Add a new menu location here → it appears in WP Admin → Appearance → Menus.

---

### `sidebars` - Widget areas
```php
'sidebars' => [
    [
        'id'          => 'sidebar-main',
        'name'        => 'Main Sidebar',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ],
],
```
`core_details.php` loops over this and calls `register_sidebar()` for each one.

---

### `cpt` - Custom Post Types
```php
'cpt' => [
    [
        'slug'        => 'portfolio',
        'singular'    => 'Portfolio',
        'plural'      => 'Portfolios',
        'icon'        => 'dashicons-portfolio',
        'supports'    => [ 'title', 'editor', 'thumbnail' ],
        'has_archive' => true,
        'public'      => true,
    ],
],
```
`core_terms.php` loops over this and calls `register_post_type()` for each one.
Labels (Add New, Edit, View…) are auto-generated from `singular` / `plural`.

**To add a new CPT:** add one array item. Zero other code changes needed.

---

### `taxonomies` - Custom taxonomies
```php
'taxonomies' => [
    [
        'slug'        => 'portfolio-category',
        'singular'    => 'Portfolio Category',
        'plural'      => 'Portfolio Categories',
        'post_types'  => [ 'portfolio' ],
        'hierarchical' => true,
    ],
],
```
Same loop pattern - `core_terms.php` calls `register_taxonomy()` for each one.

---

### `api_namespace`
```php
'api_namespace' => 'npt/v1',
```
Used as the namespace for all REST routes.
Also stored as `NPT_API_NS` constant in `static/`.

---

### `ajax_actions`
```php
'ajax_actions' => [
    'npt_load_more'   => 'npt_ajax_load_more',
    'npt_submit_form' => 'npt_ajax_submit_form',
],
```
Map of `action_name => handler_function`.
A loader in `apis/` loops over this and registers `wp_ajax_*` hooks.

---

## How Modules Read the Config

Every module uses this pattern:
```php
$cfg = $GLOBALS['theme_config'];
```

Or in a closure:
```php
add_action( 'init', function () use ( $cfg ): void {
    register_nav_menus( $cfg['menus'] );
} );
```

---

## What Changes When Starting a New Project?

1. Update `name`, `version`, `textdomain`
2. Replace the `cpt` array with the new project's post types
3. Replace the `taxonomies` array
4. Update `menus` and `sidebars` as needed
5. Add/remove entries in `assets`

**Everything else stays the same.** The loaders, hooks, and module files are all reusable.
