# Theme Architecture - Master Overview

## Purpose

This theme is built as a **reusable base** for any WordPress project.
Every concern is isolated in its own folder. To start a new project you copy the theme and only edit `includes/core_settings.php`.

---

## The Golden Rules

| # | Rule |
|---|------|
| 1 | `function.php` **only loads files** - zero logic |
| 2 | **All config** lives in `includes/core_settings.php` as arrays |
| 3 | **All constants** live in `static/page-sample.php` with `NPT_` prefix |
| 4 | **Hooks/filters** are registered in loops - never repeated manually |
| 5 | **Models** shape data - they never query or output HTML |
| 6 | **Components** output HTML - they never query or register hooks |
| 7 | **Page templates** fetch → redirect-check → render - nothing else |
| 8 | **Middleware** only inspects the request - no data, no HTML |

---

## Folder Map

```
theme-root/
│
├── function.php            ← Bootstrap loader (entry point)
│
├── static/                 ← Constants (NPT_* defines)
│   └── page-sample.php
│
├── includes/               ← Core PHP modules
│   ├── core_settings.php   ← Config arrays (CPTs, menus, assets …)
│   ├── core_details.php    ← Theme setup (supports, menus, sidebars)
│   ├── core_terms.php      ← CPT + taxonomy registration
│   ├── rules_conditions.php← Filters + conditions
│   └── data_fetcher/       ← WP_Query abstraction layer
│       └── page-sample.php
│
├── apis/                   ← REST API + data models
│   ├── fetch_functions.php ← REST route definitions + callbacks
│   └── models/
│       └── page-sample.php ← Data formatters (post → array)
│
├── middleware/             ← Request lifecycle (auth, rate limit, CORS)
│   └── page-sample.php
│
├── admin/                  ← Admin UI (pages, metaboxes, settings)
│   └── page-sample.php
│
├── components/             ← Re-usable UI partials (no queries)
│   └── cards/
│       └── page-sample.php
│
├── pages/                  ← WordPress page templates
│   └── page-sample.php
│
├── assets/                 ← CSS / JS / images
│   ├── css/
│   ├── js/
│   └── images/
│
├── common/                 ← Pure helper functions (no hooks)
│   └── common_functions.php
│
├── languages/              ← .po / .mo translation files
└── info/                   ← YOU ARE HERE - documentation
```

---

## Request Lifecycle (HTML page)

```
Browser → WordPress → function.php loads all modules
                            │
                     page template (pages/*.php)
                            │
                     npt_fetch_*()          ← data_fetcher/
                            │
                     npt_maybe_redirect()   ← models/ (if redirect set)
                            │
                     npt_component()        ← components/
                            │
                     HTML output to browser
```

## Request Lifecycle (REST API)

```
Browser → /wp-json/npt/v1/posts
                    │
             middleware (auth → rate-limit → CORS)
                    │
             fetch_functions.php callback
                    │
             npt_model_*()           ← models/page-sample.php
                    │
             npt_maybe_redirect()    (returns 301 if set)
                    │
             WP_REST_Response → JSON → Browser
```

---

## Load Order (function.php)

```
1. static/page-sample.php       → NPT_* constants
2. includes/core_settings.php   → $GLOBALS['theme_config'] array
3. common/                      → helper functions
4. includes/                    → setup, CPTs, hooks, filters
5. middleware/                  → REST request lifecycle
6. admin/                       → admin-only UI
7. apis/                        → REST routes + AJAX
8. wp_enqueue_scripts hook      → assets from config map
9. rest_api_init hook           → route registration
```

> **Why this order?**
> Constants must exist before config. Config must exist before modules that read it.
> Helpers must exist before the modules that call them.

---

## File Naming Convention

| Type | Pattern | Example |
|------|---------|---------|
| Config / setup | `core_*.php` | `core_settings.php` |
| Feature modules | `descriptive-name.php` | `rules_conditions.php` |
| Samples / stubs | `page-sample.php` | `admin/page-sample.php` |
| Components | folder + `page-sample.php` | `components/cards/page-sample.php` |
| Page templates | `page-{slug}.php` | `pages/page-home.php` |
| Constants | `NPT_UPPER_SNAKE` | `NPT_META_REDIRECT` |
| Functions | `npt_lower_snake()` | `npt_fetch_posts()` |

---

## Documentation Files in This Folder

| File | Covers |
|------|--------|
| `00-overview.md` | This file - big picture |
| `01-bootstrap.md` | How `function.php` loads the theme |
| `02-config.md` | `core_settings.php` - config array reference |
| `03-constants.md` | `static/page-sample.php` - all `NPT_*` constants |
| `04-setup.md` | Theme supports, menus, sidebars, CPTs, taxonomies |
| `05-data-flow.md` | Fetcher → Model → Component → Template pipeline |
| `06-api.md` | REST routes, alternate slugs, redirect handling |
| `07-middleware.md` | Auth, rate limiting, CORS |
| `08-admin.md` | Admin pages, metaboxes, settings, dashboard widgets |
| `09-components.md` | How to build and use components |
| `10-helpers.md` | All helper functions in `common/` |
