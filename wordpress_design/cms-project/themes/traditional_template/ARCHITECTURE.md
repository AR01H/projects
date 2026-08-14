# The Cane House - Architecture

A WordPress theme where **everything is a registry array executed by a
generic loop** (the Drupal "info array + hook" style). You describe pages,
AJAX calls, REST routes, redirects and admin screens as data; the engines do
the wiring. You never write routing, enqueue, nonce or menu code per feature.

> Built on the `new_theme_template` starter. It still uses that starter's
> `nt_` function prefix and `NT_` constants throughout - see §6 if you ever
> want to rename them.

```
DATA (you edit)                    ENGINES (you don't edit)
config/*.php  ── arrays ──────▶    core/*.php  ── loops + security
admin/data/*.json ── content ─▶    nt_data() / NT_Data_Provider
```

---

## 0. "I just want to change some text"

You almost never need to touch PHP. All user-facing copy lives in
`admin/data/*.json`, read with `nt_data('<file>')` or
`NT_Data_Provider::get('<file>')`. Edit the JSON, reload the page.

| I want to change... | Edit this file |
|---|---|
| Header nav links, dropdowns | `admin/data/nav.json` → `header` |
| Header button ("BOOK US / YOUR EVENT") | `admin/data/nav.json` → `cta` |
| Footer links, products, socials, brand tagline | `admin/data/footer.json` |
| Footer column headings, "Connect:", copyright | `admin/data/footer.json` → `labels` |
| **Every shared button/aria label** (OK, Cancel, Read more, Copied…) | `admin/data/ui.json` → `labels` / `aria` |
| Floating toolbar buttons | `admin/data/ui.json` → `floating_toolbar` |
| "Our Story" section (heading, body, stamp, button) | `admin/data/about.json` |
| Which sections appear on a page, and in what order | `admin/data/page_sections.json` |
| Whether a section shows at all | `admin/data/sections.json` |
| **A message reused in several places** ("Read the blog…") | `admin/data/blocks.json` |
| **Popups / dialogs** | `admin/data/dialogs.json` |
| **The announcement strip** | `admin/data/site_notices.json` |
| **Cookie banner text, categories, re-ask versions** | `admin/data/cookies.json` |
| Breadcrumb wording and nesting | `admin/data/breadcrumbs.json` |
| Policy pages (privacy / cookies / terms) | `admin/data/legal_*.json` + `legal.json` |
| Drifting leaves and the ground artwork | `admin/data/decor.json` → `leaves` |
| Brand name / phone / email | `config/theme.php` (`NT_BRAND_*`) |

Everything else follows the same rule: a component named
`components/foo.php` reads `admin/data/foo.json`. `COMPONENTS.md` lists every
component and the JSON file it reads.

**Rule for new work:** never hardcode a user-facing string in a component.
Read it from JSON with a sensible `?? 'fallback'` default, so the theme still
renders if the key is missing.

---

## 1. Directory map

| Path | What lives here | Do you edit it? |
|---|---|---|
| `functions.php` | 3 lines: loads `core/bootstrap.php` | never |
| `style.css` | WP theme header only | rebrand once |
| `config/` | ALL registries (arrays). The "site map". | **always** |
| `core/` | Generic engines that loop the registries | never |
| `pages/` | Page templates (`page-*.php`) | per page |
| `components/` | Reusable markup: `parts/`, `cards/` | per component |
| `handlers/` | `ajax/` + `rest/` callback functions | per endpoint |
| `admin/` | Admin panel: `tabs/` views, `assets/`, `includes/`, `data/` JSON | per screen |
| `assets/` | Front CSS/JS. Page files under `css/pages/`, `js/pages/` | per page |
| root `*.php` | WP fallbacks: `index`, `page`, `single`, `search`, `404`, `header`, `footer` | rarely |

### The registries (one file = one concern)

| File | Registers | Engine |
|---|---|---|
| `config/theme.php` | Global constants: brand, term levels, flags (old `core_info` + `core_terms` + `core_settings`) | loaded first |
| `config/setup.php` | Theme supports, menus, image sizes | `core/bootstrap.php` |
| `config/files.php` | Extra includes (`always` / `admin` / `front`) | `core/bootstrap.php` |
| `config/pages.php` | **Pages**: slug → template + css + js + aliases + create/front | `core/router.php`, `core/assets.php` |
| `config/routes.php` | **Dynamic URLs** (DB-driven slugs) | `core/router.php` |
| `config/redirects.php` | Redirect rules (path → destination + status) | `core/redirects.php` |
| `config/ajax.php` | AJAX actions (callback, file, public, capability) | `core/ajax.php` |
| `config/rest.php` | REST routes under one namespace | `core/rest.php` |
| `config/assets.php` | Global CSS/JS + admin assets | `core/assets.php` |
| `config/admin.php` | Admin menu, submenus/subtabs, option groups, tools | `core/admin.php` |
| `config/database.php` | Custom DB tables (schema per key) | `core/database.php` |

Every registry passes through a filter (`nt_config_pages`, `nt_config_ajax`, ...)
so a child theme or plugin can alter it without touching these files.

---

## 2. Request lifecycle

```
Request
  │
  ├─ core/redirects.php   rule table + coming-soon gate (template_redirect)
  │
  ├─ core/router.php      static pages loop (config/pages.php)
  │     real WP page ──────────────▶ its template
  │     404 but slug registered ───▶ virtual page, HTTP 200, same template
  │
  ├─ core/router.php      dynamic routes loop (config/routes.php)
  │     matcher returns vars ──────▶ set_query_var() + template
  │
  └─ core/assets.php      global assets + THIS page's css/js + window.ntSite
```

`window.ntSite` (injected on every page) carries `ajaxUrl`, `restUrl`,
`restNonce` and one **nonce per registered AJAX action** - which is why the
JS side never builds security fields by hand.

---

## 3. Recipes (the whole point of this template)

### Add a page
1. `config/pages.php` - add the entry:
   ```php
   'about' => array(
       'title'    => 'About Us',
       'template' => 'pages/page-about.php',
       'css'      => array( 'assets/css/pages/about.css' ),
       'js'       => array( 'assets/js/pages/about.js' ),
   ),
   ```
2. Create `pages/page-about.php` (copy `pages/page-testing.php`'s `get_header()` /
   `get_footer()` shell, minus its dev-only demo blocks) - every real page template
   is the SAME 4 lines: `get_header()` → `<main>` → `app_render_sections('about')`
   → `get_footer()`. It lists no components; the sections live in JSON (next recipe).
3. Done. `/about/` works immediately (virtual routing). Run **Theme →
   Admin Tools → Pages → Sync Now** to also create the real WP page row.

### Compose a page's sections (JSON-driven — the standard way)
Pages are built from an ordered list of section components declared in
`admin/data/page_sections.json` and rendered by `NT_Section_Renderer`
(`src/Sections/`). **Adding, removing, re-ordering or toggling a section is a
one-line JSON edit — no PHP.**

```jsonc
"gallery": [
  { "component": "gallery-grid", "key": "gallery" },        // components/gallery-grid.php
  { "component": "reviews",      "key": "gallery_reviews" }, // hide via sections.json
  { "component": "faqs",         "key": "gallery_faqs" }
]
```
Section keys: `component` (required, `components/<component>.php`), `key`
(optional show/hide via `sections.json`), `header` (optional — merges
`page_headers.json[<name>]`, used with `"component":"banners/page_header"`),
`args` (optional extra context). Rendering goes through `nt_component()` which
realpath-guards the path and extracts context, so `banners/page_header` receives
`$title`/`$subtitle` while a plain section component just reads its own JSON.

A section **component** (`components/*.php`) is self-contained: it reads its own
`admin/data/*.json`, escapes output, and `return`s early when it has no data.
That's the whole contract — see `components/reviews.php` as the reference.

### Add an AJAX call
1. `config/ajax.php`:
   ```php
   'save_vote' => array(
       'callback' => 'nt_ajax_save_vote',
       'file'     => 'handlers/ajax/votes.php',
       'public'   => true,
   ),
   ```
2. `handlers/ajax/votes.php` - write `nt_ajax_save_vote()`. Sanitize input,
   reply with `wp_send_json_success()` / `wp_send_json_error()`.
   **Do not** write nonce/capability code - the dispatcher already ran it.
3. JS: `NT.ajax( 'save_vote', { post_id: 5 } ).then( ... )`.

### Add a REST route
1. `config/rest.php` - add under `routes` with `methods`, `callback`,
   `file`, optional `args` schema and `capability`.
2. Write the callback in `handlers/rest/`.
3. JS: `NT.rest( 'my-route', { page: 2 } )`.

### Add a redirect
One line in `config/redirects.php`:
```php
'old-page' => array( 'to' => '/new-page/', 'status' => 301 ),
```
External destinations need the host whitelisted once:
```php
add_filter( 'allowed_redirect_hosts', fn( $hosts ) => array_merge( $hosts, array( 'partner.example.com' ) ) );
```

### Add a dynamic (DB-driven) URL
Add a matcher in `config/routes.php` (see the commented `guide_category`
example there). The `match` callback receives the slug and returns query vars
or `false`. Use `$wpdb->prepare()` inside the matcher - always.

### Add an admin screen
`config/admin.php` maps four levels into the WP admin:

```
Theme                        <- 'menu'      (sidebar top-level)
+-- Admin Dashboard Tools    <- 'submenus'  (sidebar submenu items)
|     [Dashboard] [Site Settings] [Admin Tools]   <- 'tabs'    (pill buttons)
|         [General] [Social Links]                <- 'subtabs' (small pills)
+-- Contact Submissions
```

Each tab/subtab takes `label`, `icon` (emoji shown in the pill) and `view`.

1. `config/admin.php` - add a submenu, tab or subtab entry, plus a group
   under `options` with `field => type` pairs (types drive the sanitizer).
2. Create the view - it is only 3 calls:
   ```php
   nt_admin_form_open( 'booking' );
   nt_admin_fields( 'booking', array(
       'max_guests' => array( 'label' => 'Max Guests' ),
   ) );
   nt_admin_form_close();
   ```
3. Read anywhere: `nt_option( 'booking', 'max_guests', 4 )`.

### Add an admin tool (one-click maintenance action)
1. `config/admin.php` - add an entry under `tools`:
   ```php
   'reindex_search' => array(
       'title'    => 'Reindex Search',
       'desc'     => 'Rebuild the search index table.',
       'button'   => 'Reindex',
       'callback' => 'nt_tool_reindex_search',
       'group'    => 'maintenance',   // which Admin Tools subtab shows it
   ),
   ```
2. Write `nt_tool_reindex_search()` in `admin/includes/tools.php` - do the
   work, `return 'message to show';`. Capability + nonce are already checked
   by the engine; the button/form is rendered by the loop.
   Built-in tools: clear object cache, clear transients, flush rewrites,
   sync pages, export/import settings JSON (Theme → Admin Tools).

### Add a custom DB table
1. `config/database.php` - add a key with `table`, `desc` and a dbDelta
   `schema` (use the `{table}` / `{charset}` placeholders).
2. Done. It installs on theme activation, shows under **Theme → Admin Tools
   → Database** with a per-table Install / Repair button, and you query it
   with `nt_db_table( 'key' )` + `$wpdb->prepare()`.
   The built-in `submissions` table stores contact form entries and powers
   the **Theme → Contact Submissions** inbox (filter, mark read/new, delete).

### Add JSON fallback data
Drop `admin/data/my-thing.json`, read it with `nt_data( 'my-thing' )`.
Use it so pages render complete before any DB/admin content exists; override
later via the `nt_data_my-thing` filter when a real source arrives.

### Add a component
Create `components/cards/team_card.php`, render with
`nt_component( 'cards/team_card', array( 'member' => $row ) )` - context keys
become local variables. Style it in `assets/css/sections.css`.

### Add a dialog
1. `admin/data/dialogs.json` - one entry (title, tone, body, optional `form`
   naming any `admin/data/form_*.json`, optional `actions`).
2. Open it from anywhere:
   ```php
   <button <?php nt_dialog_trigger( 'brochure' ); ?>>Get the brochure</button>
   ```

There is **no step three, and no markup to write**. The trigger queues the
dialog, PHP ships its data as `window.ntUi.dialogs`, and `assets/js/ui-kit.js`
builds it the first time it is opened - so a page that never mentions it
carries none of its weight. The same object shape works from JS:
`NT.dialog.show({ … })`, `NT.alert()`, `NT.confirm()`, `NT.toast()`.

### Reuse one message in several places
Put it in `admin/data/blocks.json` under a key, then name that key wherever it
is wanted (`promo-block`, `quick-links`, the blog sidebar). Editing the entry
updates every placement at once. See `COMPONENTS.md` §2b.

---

## 3b. Where things are rendered

| Kind | Rendered by | Why |
|---|---|---|
| Page sections, articles, inline alerts | **PHP** | It is content: it has to exist for a crawler and without JS |
| Dialogs, notice strip, cookie banner, toasts | **the browser**, from data PHP ships | Overlay chrome cannot work without JS anyway, and a dialog nobody opens should cost nothing |

The dividing line: **overlay chrome is built in the browser, page content is
rendered on the server.** `src/README.md` gives the reasoning; the PHP classes
and the JS classes mirror each other, so there is one mental model rather than
two renderers to keep in step by hand.

---

## 4. Security model (enforced by engines, not by discipline)

| Concern | Where it is enforced |
|---|---|
| AJAX nonce | `core/ajax.php` dispatcher - before every callback |
| AJAX capability | `core/ajax.php` dispatcher (`capability` key) |
| REST permissions | `core/rest.php` (`capability` key → `current_user_can`) |
| Admin save nonce + cap | `core/admin.php` generic save handler |
| Field sanitization | declared `field => type` map in `config/admin.php` |
| File inclusion | `realpath()` containment in `nt_component()`, `nt_require_theme_file()`, router template resolution, admin view whitelist |
| SQL in matchers/handlers | your job: always `$wpdb->prepare()` |
| Output escaping | your job in templates: `esc_html` / `esc_url` / `esc_attr` |

## 5. Conventions

- Function prefix `nt_`, constants `NT_`, CSS classes/handles `nt-`,
  wp actions `nt_{action}`, options `nt_{group}`.
- **Colors**: edit ONLY the "Client palette" block in
  `assets/css/variables.css` (`--client-color1`, `--client-color2`,
  `--client-light-color`, ...). The semantic `--nt-*` tokens map to it and
  are what component CSS uses - never hard-code a hex outside variables.css.
- Site PHP goes in `/includes` and is listed in `config/files.php`
  (see `includes/README.md` for the old core_* file mapping).
- Page assets live at `assets/css/pages/{slug}.css` + `assets/js/pages/{slug}.js`.
- Asset versions are `filemtime()` - saving a file busts the cache instantly.
- JSON/data files: UTF-8 **without BOM** (deployment symlink serves files as-is).
- Templates escape ALL dynamic output; handlers sanitize ALL input.

## 6. Renaming the prefix for a new site

1. Find/replace across the theme: `nt_` → `xx_`, `NT_` → `XX_`,
   `nt-` → `xx-` (CSS handles/classes), `ntSite` → `xxSite`,
   `window.NT` → `window.XX`.
2. Update `style.css` header + `config/theme.php` brand values.
3. Re-activate the theme (creates pages + flushes rewrites).
