# /includes - your site code

Put the site's own PHP here: data services, shortcodes, SEO helpers,
model classes, etc. Then list each file in `config/files.php`:

```php
'always' => array( 'includes/data-services.php' ),  // every request
'admin'  => array( 'includes/admin-columns.php' ),  // wp-admin only
'front'  => array( 'includes/seo.php' ),            // front end only
```

The bootstrap loops that list - no `require_once` chains to maintain.

## Where the old core_* files went

| Old file (advaithhomes_new) | Now lives in |
|---|---|
| `includes/core_info.php` (company info) | `config/theme.php` -> `NT_BRAND_*` constants |
| `includes/core_terms.php` (theme + term constants) | `config/theme.php` -> `NT_THEME_*`, `NT_TERM_PARENT`, `NT_TERM_SECTION`, `NT_TERM_CONTENT` |
| `includes/core_settings.php` (COMING_SOON) | `config/theme.php` -> `NT_COMING_SOON`, `NT_COMING_SOON_SLUG` |
| `includes/rules_conditions.php` (routing) | `config/pages.php` + `config/routes.php` + `core/router.php` |
| term helper functions | `admin/includes/terms.php` (`nt_term_label()`, `nt_terms_tree()`) |
| JSON fallback data | `admin/data/*.json` read via `nt_data( 'name' )` |
