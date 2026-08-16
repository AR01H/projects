# Development Guide

## "Where does this code go?"

| You're adding... | It goes in |
|---|---|
| A WordPress registration call (`add_theme_support`, a menu, an image size) | `config/theme-support.php` |
| A stylesheet/script to load everywhere | `config/assets.php` |
| A stylesheet/script for one page only | that page's own entry - do not add to the global registry |
| A business/display label that might get renamed | `config/terminology.json`, read via `TerminologyService` |
| A stable settings value (not content, not WP registration) | `config/settings.json` |
| An actual content record | `data/content/` (or `data/pages/`, `data/sections/`) |
| Logic preparing one template's data | a new `src/Controllers/*Controller.php` |
| A business rule / cross-cutting orchestration | a new `src/Services/*Service.php` |
| Fetching + mapping one content domain, with a swappable source | `src/Repositories/Contracts/*Interface.php` + an implementation |
| Raw fetch/cache with zero domain knowledge | `src/DataProviders/` (rarely - usually the two that exist are enough) |
| A reusable, named cross-cutting helper (post data, images, URLs, formatting) | `src/Support/*.php` |
| A reusable piece of UI | `components/<category>/<name>.php` |
| A reused loop/markup fragment that isn't a standalone UI piece | `template-parts/*.php` |
| A new named page's URL | a key in `config/routes.php`, read via `RouteService` - never a hard-coded path string in a template/component |
| A new named page's presentation glue | `pages/<key>/view.php` (see "Adding a page" below) |

## Adding a page

1. Add a key to `config/routes.php`: `'pricing' => '/pricing'`.
2. Create `pages/pricing/view.php` - the same 4-line shape as any root
   template (build data via a Controller, render it).
3. Create the WordPress Page in wp-admin with the slug `pricing` (matching
   the route's path). `page.php` will find it automatically via
   `RouteService::key_for_current_page()` - no other file changes.
4. If the page needs real data beyond its own WordPress content, give it
   its own Controller (`src/Controllers/PricingController.php`) instead of
   reusing the generic `PageController` - swap one line in
   `pages/pricing/view.php`.
5. Link to it from anywhere with `RouteService::url('pricing')`, never a
   hard-coded `/pricing/` string.
6. Every page automatically gets a `page-<key>` class on `<body>` (see
   `RouteService::add_body_class()`) - use it to scope a CSS rule to one
   page (`body.page-pricing .something`) if a rule genuinely can't be a
   general-purpose component.
7. If the page needs its own stylesheet/script, create
   `assets/css/pages/pricing.css` and/or `assets/js/pages/pricing.js` -
   `AssetService::enqueue_page_assets()` loads them automatically for that
   page only, with zero registration needed anywhere (unlike
   `config/assets.php`'s global registry). Don't add page-specific assets
   there. If a page needs something extra beyond that one file (a one-off
   library, a second script), list its path in that route's `styles`/
   `scripts` array in `config/routes.php` instead of a second convention
   file - still loaded automatically, still nowhere else to register it.

If a value or piece of logic doesn't fit any row above, it's a sign the
responsibility needs a small amount of thought before a file gets created -
see `ARCHITECTURE.md`'s folder-ownership table, or ask in review rather
than defaulting to `functions.php` or a Support class.

## Naming

- Internal file/class names describe **technical responsibility**
  (`TestimonialRepository`, `ProfileCard`, `ContentSection`), never a
  current business label (`ReviewsBlock`, `ClientStoriesWidget`).
- Display text a client might want renamed goes through
  `TerminologyService`/`config/terminology.json`, not a hard-coded string.
- One class per file; filename matches the class name exactly.

## Adding a component

1. Decide its category folder (`components/<category>/`), creating the
   folder only now, not in advance.
2. The component file receives data as **bare variables** (extracted from
   the array passed to `View::component()`) - it never fetches its own
   data, never calls a Service or Repository directly.
3. Its CSS lives in `assets/css/components/<name>.css` - base, every size/
   variant, and every interaction state (hover/focus/active/disabled) in
   the same file. No separate "hover styles" file.
4. Its JS (if genuinely interactive) lives in `assets/js/components/<name>.js`,
   registered via `VintageSoul.app.register()` (see `assets/js/core/app.js`).
   Not every component needs a JS file - most won't.
5. Register the new CSS/JS handles in `config/assets.php` only if the
   component is used on every page; otherwise enqueue it from the specific
   page/Controller that needs it.

## Git

- Commit messages describe what changed and why, not "wip"/"fix".
- `.gitkeep` is added only to a directory that is genuinely empty *and*
  structurally required right now - never added automatically to every
  new folder.
- Branch before large structural changes; this base setup itself should be
  tagged/branched before a real project starts customizing it, so the
  generic foundation stays available to copy for the *next* project too.
