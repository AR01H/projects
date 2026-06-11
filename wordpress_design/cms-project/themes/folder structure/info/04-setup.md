# 04 — Theme Setup: CPTs, Taxonomies, Menus, Sidebars

## Files Involved

| File | What it registers |
|------|------------------|
| `includes/core_details.php` | Theme supports, menus, sidebars, image sizes |
| `includes/core_terms.php` | Custom Post Types, Custom Taxonomies |
| `includes/rules_conditions.php` | Global filters and conditions |

All three read from `$GLOBALS['theme_config']` (the config array).

---

## Theme Supports (`core_details.php`)

Registered on `after_setup_theme` hook:

```php
add_theme_support( 'html5', [ 'search-form', 'comment-form', … ] );
add_theme_support( 'custom-logo' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'title-tag' );
add_theme_support( 'align-wide' );   // Gutenberg
add_theme_support( 'wp-block-styles' );
```

**Why `after_setup_theme`?**
This is the earliest hook where theme functions are reliable.
`init` is too late for some supports (e.g. `title-tag`).

---

## Menus

Defined in config:
```php
'menus' => [
    'primary' => 'Primary Navigation',
    'footer'  => 'Footer Links',
],
```

Registered in `core_details.php`:
```php
add_action( 'init', function () use ( $cfg ): void {
    register_nav_menus( $cfg['menus'] );
} );
```

Used in templates:
```php
wp_nav_menu( [
    'theme_location' => 'primary',
    'container'      => 'nav',
    'container_class' => 'main-nav',
] );
```

**To add a new menu:** add one key-value pair to `config['menus']`.

---

## Sidebars / Widget Areas

Defined in config with full WP args:
```php
'sidebars' => [
    [
        'id'            => 'sidebar-main',
        'name'          => 'Main Sidebar',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ],
],
```

Registered in `core_details.php`:
```php
add_action( 'widgets_init', function () use ( $cfg ): void {
    foreach ( $cfg['sidebars'] as $sidebar ) {
        register_sidebar( $sidebar );
    }
} );
```

Used in templates:
```php
if ( is_active_sidebar( 'sidebar-main' ) ) {
    dynamic_sidebar( 'sidebar-main' );
}
```

---

## Custom Post Types (`core_terms.php`)

### Config
```php
'cpt' => [
    [
        'slug'        => 'portfolio',
        'singular'    => 'Portfolio',
        'plural'      => 'Portfolios',
        'icon'        => 'dashicons-portfolio',
        'supports'    => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'has_archive' => true,
        'public'      => true,
    ],
],
```

### How Labels Are Auto-generated

`core_terms.php` builds the `labels` array programmatically:
```php
$labels = [
    'name'          => $cpt['plural'],
    'singular_name' => $cpt['singular'],
    'add_new_item'  => 'Add New ' . $cpt['singular'],
    'edit_item'     => 'Edit ' . $cpt['singular'],
    // … etc
];
```
You only provide `singular` and `plural` — the rest is derived.

### `show_in_rest: true`
Always enabled. This allows:
- Gutenberg block editor to work with the CPT
- REST API to expose the CPT at `/wp-json/wp/v2/{slug}`
- Decoupled / headless front-ends to consume it

### Adding a New CPT

Add one object to `config['cpt']`:
```php
[
    'slug'        => 'service',
    'singular'    => 'Service',
    'plural'      => 'Services',
    'icon'        => 'dashicons-admin-tools',
    'supports'    => [ 'title', 'editor', 'thumbnail' ],
    'has_archive' => false,
    'public'      => true,
],
```
No other code changes needed.

---

## Custom Taxonomies (`core_terms.php`)

### Config
```php
'taxonomies' => [
    [
        'slug'         => 'portfolio-category',
        'singular'     => 'Portfolio Category',
        'plural'       => 'Portfolio Categories',
        'post_types'   => [ 'portfolio' ],
        'hierarchical' => true,
    ],
],
```

### How It's Registered
```php
foreach ( $cfg['taxonomies'] as $tax ) {
    register_taxonomy( $tax['slug'], $tax['post_types'], [
        'labels'       => $labels,
        'hierarchical' => $tax['hierarchical'],
        'rewrite'      => [ 'slug' => $tax['slug'] ],
        'show_in_rest' => true,
    ] );
}
```

**`hierarchical: true`** = behaves like Categories (parent/child tree)
**`hierarchical: false`** = behaves like Tags (flat list)

---

## Filters & Conditions (`rules_conditions.php`)

All WordPress filters are defined as a tuple array and registered in one loop:

```php
$npt_filters = [
    [ 'the_content',  'npt_filter_content',         10, 1 ],
    [ 'excerpt_length', 'npt_filter_excerpt_length', 10, 1 ],
    [ 'body_class',   'npt_filter_body_class',       10, 1 ],
];

foreach ( $npt_filters as [ $hook, $cb, $prio, $args ] ) {
    add_filter( $hook, $cb, $prio, $args );
}
```

### Why Tuple Arrays for Hooks?

Instead of this (repeated, hard to scan):
```php
add_filter( 'the_content',    'npt_filter_content',         10, 1 );
add_filter( 'excerpt_length', 'npt_filter_excerpt_length',  10, 1 );
add_filter( 'body_class',     'npt_filter_body_class',       10, 1 );
```

You get this (list-driven, easy to add/remove/reorder):
```php
$npt_filters = [
    [ 'the_content',    'npt_filter_content',        10, 1 ],
    [ 'excerpt_length', 'npt_filter_excerpt_length', 10, 1 ],
    [ 'body_class',     'npt_filter_body_class',     10, 1 ],
];
```

To **disable** a filter for a project: comment out or remove one line.
No need to find and delete `add_filter()` calls scattered across files.

---

## Hook Priority Guide

| Priority | When to use |
|----------|-------------|
| `0–4` | Must run before WP core (rare) |
| `5–9` | Before most plugins |
| `10` | Default — run alongside plugins |
| `15–20` | After most plugins |
| `99–999` | Must run last (e.g. cleanup, overrides) |

CPTs are registered at priority `0` so they're available before any plugin tries to use them.
