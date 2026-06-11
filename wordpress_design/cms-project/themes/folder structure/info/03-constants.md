# 03 — Constants (`static/page-sample.php`)

## What It Is

A single file that defines every `NPT_*` constant used across the theme.
It is loaded **before anything else** in `function.php`.

---

## Why Constants Instead of Config Values?

| Scenario | Use Config Array | Use Constant |
|----------|-----------------|-------------|
| Looping to register multiple items | ✅ | ❌ |
| Single fixed string used in 10+ files | ❌ | ✅ |
| Meta key names | ❌ | ✅ (never hardcode strings) |
| Asset version numbers | ❌ | ✅ |
| Taxonomy slugs referenced in many files | ❌ | ✅ |

**Rule:** If you write the same string literal in more than one file → make it a constant.

---

## Why `defined() || define()`?

```php
defined( 'NPT_VERSION' ) || define( 'NPT_VERSION', '1.0.0' );
```

This pattern is called a **guard define**.
If another plugin or a parent theme already defines `NPT_VERSION`,
this line skips the definition instead of throwing a fatal error.
Always use this pattern for theme constants.

---

## Complete Constant Reference

### Theme Identity
| Constant | Value | Used In |
|----------|-------|---------|
| `NPT_VERSION` | `'1.0.0'` | `wp_enqueue_*()` version param |
| `NPT_TEXTDOMAIN` | `'npt'` | `__()`, `_e()`, `esc_html__()` |
| `NPT_THEME_DIR` | `get_template_directory()` | `file_exists()`, `require` paths |
| `NPT_THEME_URI` | `get_template_directory_uri()` | Asset URLs |

### API / REST
| Constant | Value | Used In |
|----------|-------|---------|
| `NPT_API_NS` | `'npt/v1'` | `register_rest_route()` namespace |
| `NPT_API_RATE_LIMIT` | `60` | Rate limit middleware |
| `NPT_API_RATE_WINDOW` | `60` | Rate limit window (seconds) |
| `NPT_POSTS_PER_PAGE` | `12` | All queries + REST params |

### Meta Keys
These are post meta (`_postmeta` table) key names.
**Never hardcode these strings** — always reference the constant.

| Constant | Meta Key | Purpose |
|----------|----------|---------|
| `NPT_META_REDIRECT` | `npt_redirect_url` | Where to 301 redirect this post |
| `NPT_META_ALT_URL` | `npt_alternate_url` | Alias / alternate URL |
| `NPT_META_ALT_TITLE` | `npt_alternate_title` | Alternate display name |
| `NPT_META_ALT_SLUG` | `npt_alternate_slug` | Alternate URL slug for lookup |
| `NPT_META_CANONICAL` | `npt_canonical_url` | SEO canonical URL override |
| `NPT_META_CLIENT` | `npt_client_name` | Portfolio: client name |
| `NPT_META_YEAR` | `npt_project_year` | Portfolio: project year |

**How to use:**
```php
// Save
update_post_meta( $post_id, NPT_META_REDIRECT, $url );

// Read
$redirect = get_post_meta( $post->ID, NPT_META_REDIRECT, true );
```

### Option Keys (`wp_options` table)
| Constant | Option Key | Purpose |
|----------|-----------|---------|
| `NPT_OPT_EXAMPLE` | `npt_example_option` | Example admin setting |

**How to use:**
```php
$value = get_option( NPT_OPT_EXAMPLE, '' );
update_option( NPT_OPT_EXAMPLE, $sanitized_value );
```

### Taxonomy Slugs
| Constant | Value | Used In |
|----------|-------|---------|
| `NPT_TAX_PORTFOLIO` | `'portfolio-category'` | `get_the_terms()`, `WP_Query` tax_query |

### Image Sizes
| Constant | Value | Registered In |
|----------|-------|--------------|
| `NPT_IMG_CARD` | `'npt-card'` | `core_details.php` via `add_image_size()` |
| `NPT_IMG_HERO` | `'npt-hero'` | `core_details.php` via `add_image_size()` |

**How to use:**
```php
get_the_post_thumbnail_url( $post, NPT_IMG_CARD );
```

### AJAX Action Names
| Constant | Action String | Hook registered in |
|----------|--------------|-------------------|
| `NPT_AJAX_LOAD_MORE` | `'npt_load_more'` | `apis/fetch_functions.php` |
| `NPT_AJAX_CONTACT` | `'npt_submit_form'` | `apis/fetch_functions.php` |

**How to use in JS:**
```js
fetch( ajaxurl, {
    method: 'POST',
    body: new URLSearchParams({ action: 'npt_load_more', nonce: npt.nonce })
})
```

### Cache / Transient
| Constant | Value | Purpose |
|----------|-------|---------|
| `NPT_CACHE_PREFIX` | `'npt_cache_'` | Prefix for all transient keys |
| `NPT_CACHE_TTL` | `3600` | Default cache lifetime (1 hour) |

**How to use:**
```php
$key  = NPT_CACHE_PREFIX . 'portfolio_list';
$data = get_transient( $key );

if ( false === $data ) {
    $data = npt_fetch_portfolios();
    set_transient( $key, $data, NPT_CACHE_TTL );
}
```

---

## Adding a New Constant

1. Open `static/page-sample.php`
2. Add: `defined( 'NPT_MY_CONSTANT' ) || define( 'NPT_MY_CONSTANT', 'value' );`
3. Use `NPT_MY_CONSTANT` everywhere — never the raw string

**Naming rule:** `NPT_` + `CATEGORY_` + `NAME` in `UPPER_SNAKE_CASE`
- Meta keys: `NPT_META_*`
- Options: `NPT_OPT_*`
- API: `NPT_API_*`
- Images: `NPT_IMG_*`
- AJAX: `NPT_AJAX_*`
- Cache: `NPT_CACHE_*`
