# WordPress Conventions

## Template hierarchy - no custom routing

This theme uses WordPress's own template hierarchy directly:
`front-page.php` -> `home.php` -> `page.php` -> `single.php` -> `archive.php`
-> `search.php` -> `404.php` -> `index.php` (fallback). There is no custom
router or virtual-page system - WordPress already resolves the right
template; building a parallel mechanism for that would be an unnecessary
abstraction over something WordPress does correctly.

Every root template file follows the same shape:
```php
$data = ( new SomeController() )->prepare();
get_header();
// render $data via components / template-parts
get_footer();
```
Nothing else belongs in a root template file - no queries, no data
shaping, no business rules (see `docs/development.md`).

## Hooks

Hooks are registered from `src/Bootstrap/Theme.php::init()`, pointing at a
Service method - never an anonymous function with logic inline, and never
registered from inside a template or component. This keeps every hook the
theme registers discoverable from one file.

## Centralizing repeated WordPress functionality

If the same WP API call (post meta, image URLs, permalink building) is
needed in more than one place, it goes in `src/Support/` once - see the
existing `PostHelper`, `ImageHelper`, `UrlHelper`. Never re-implement the
same WP query/formatting logic in two different components.

## Security

- Escape on output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses()` -
  every value printed into markup, no exceptions.
- Sanitize on input: never trust `$_GET`/`$_POST`/`$_REQUEST` directly.
- Nonces on every form/AJAX action; capability checks (`current_user_can()`)
  before any privileged action.
- Database access goes through `$wpdb->prepare()` or WP_Query - never raw
  string-concatenated SQL.
- `JsonFileProvider`/`View` both realpath-guard their file access - any new
  file-inclusion helper must do the same (never trust a path built from
  user input without checking it resolves inside the theme directory).

## Assets

All enqueuing goes through `AssetService::enqueue()`, driven by
`config/assets.php` - no `wp_enqueue_style()`/`wp_enqueue_script()` calls
scattered across templates or components. Page-specific assets are
enqueued from that page's own Controller/template, not added to the global
registry.
