# 01 — Bootstrap (`function.php`)

## What It Does

`function.php` is the **only entry point** WordPress calls automatically.
Its entire job is to load other files in the right order — nothing else.

---

## Why No Logic in `function.php`?

If you put a hook registration, a query, or any real code here it becomes impossible to:
- Test that feature in isolation
- Disable it without commenting out code
- Find it quickly when debugging

**Rule:** If it's not a `require_once` or a `theme_load_dir()` call, it does not belong in `function.php`.

---

## The Loader Helper

```php
function theme_load_dir( string $dir ): void {
    foreach ( glob( $dir . '/*.php' ) ?: [] as $file ) {
        require_once $file;
    }
}
```

This lets you add a new feature by **dropping a file** into the right folder.
No need to touch `function.php` at all.

---

## Load Order Explained

### Step 0 — Constants (`static/`)
```php
require_once __DIR__ . '/static/page-sample.php';
```
Must be **first**. Every other file uses `NPT_*` constants.
If this loads after config, PHP throws undefined constant errors.

### Step 1 — Config (`includes/core_settings.php`)
```php
$GLOBALS['theme_config'] = require __DIR__ . '/includes/core_settings.php';
```
Returns a plain PHP array. Stored in `$GLOBALS` so every module can read it
with `$cfg = $GLOBALS['theme_config']` — no global keyword needed.

> **Why not a class or singleton?**
> A plain array is simpler, faster, and easier to debug.
> `var_dump( $GLOBALS['theme_config'] )` gives you the whole picture instantly.

### Step 2 — Helpers (`common/`)
```php
theme_load_dir( __DIR__ . '/common' );
```
Functions like `npt_component()`, `npt_fetch_posts()`, `npt_truncate()`.
These must exist before includes/ modules call them.

### Step 3 — Core Modules (`includes/`)
```php
theme_load_dir( __DIR__ . '/includes' );
```
Loads `core_details.php`, `core_terms.php`, `rules_conditions.php`.
Each file reads `$GLOBALS['theme_config']` and registers hooks.

### Step 4 — Middleware (`middleware/`)
```php
theme_load_dir( __DIR__ . '/middleware' );
```
Attaches REST lifecycle filters (auth, rate-limit, CORS).
Must run before REST routes are registered.

### Step 5 — Admin (`admin/`)
```php
theme_load_dir( __DIR__ . '/admin' );
```
Admin pages, metaboxes, settings panels.
WordPress guards admin-only hooks internally — safe to load always.

### Step 6 — APIs (`apis/`)
```php
theme_load_dir( __DIR__ . '/apis' );
```
Defines REST routes and AJAX handlers.
Routes are actually _registered_ on `rest_api_init` (inside each file),
so loading the file here just defines the PHP functions.

### Step 7 — Asset Enqueue (hook)
```php
add_action( 'wp_enqueue_scripts', 'theme_enqueue_assets' );
```
Reads `$GLOBALS['theme_config']['assets']` and enqueues in a loop.
Version comes from `NPT_VERSION` constant.

---

## Adding a New Module

1. Create a file in the right folder (e.g. `includes/my-new-feature.php`)
2. Write your hook registrations inside it
3. Done — `theme_load_dir()` picks it up automatically

You **never** need to edit `function.php` to add a new feature.

---

## Diagram

```
WordPress boots
      │
      ▼
function.php
      │
      ├─ static/page-sample.php     (NPT_* constants)
      ├─ includes/core_settings.php (config array → $GLOBALS)
      ├─ common/*.php                (helpers)
      ├─ includes/*.php              (setup, CPTs, filters)
      ├─ middleware/*.php            (REST lifecycle)
      ├─ admin/*.php                 (admin UI)
      └─ apis/*.php                  (REST routes, AJAX)
            │
            └─ Everything is now loaded.
               WordPress continues → template file runs.
```
